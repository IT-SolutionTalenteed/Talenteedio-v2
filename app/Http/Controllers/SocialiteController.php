<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeTalentMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirige l'utilisateur vers Google OAuth.
     * Le paramètre 'type' permet de différencier register et login
     */
    public function redirectToGoogle()
    {
        $type = request()->query('type', 'login'); // 'register' ou 'login'
        
        return Socialite::driver('google')
            ->stateless()
            ->with([
                'state' => base64_encode(json_encode(['type' => $type])),
                'prompt' => 'select_account consent', // Force le choix du compte et la confirmation
                'access_type' => 'offline', // Optionnel : pour obtenir un refresh token
            ])
            ->redirect();
    }

    /**
     * Callback Google avec gestion sécurisée des emails existants
     */
    public function handleGoogleCallback()
    {
        $frontendBase = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');

        try {
            // Récupérer le type (register ou login) depuis le state
            $state = json_decode(base64_decode(request()->query('state')), true);
            $type = $state['type'] ?? 'login';
            
            // Récupérer les informations de l'utilisateur depuis Google
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $email = $googleUser->getEmail();
            if (!$email) {
                return redirect("{$frontendBase}/auth/google/callback?type={$type}&error=" . 
                    urlencode('Email non fourni par Google'));
            }
            
            $googleId = $googleUser->getId();
            $name = $googleUser->getName() ?? $email;
            $avatar = $googleUser->getAvatar();
            
            // Vérifier si l'email existe déjà
            $existingUser = User::where('email', $email)->first();
            
            if ($type === 'register') {
                return $this->handleRegister($existingUser, $email, $googleId, $name, $avatar, $frontendBase);
            } else {
                return $this->handleLogin($existingUser, $email, $googleId, $frontendBase);
            }
            
        } catch (\Exception $e) {
            \Log::error('Google Auth Error: ' . $e->getMessage());
            
            return redirect("{$frontendBase}/auth/google/callback?error=" . 
                urlencode('Erreur lors de l\'authentification Google'));
        }
    }

    /**
     * Gère l'inscription via Google
     */
    private function handleRegister($existingUser, $email, $googleId, $name, $avatar, $frontendBase)
    {
        $frontendBase = rtrim($frontendBase, '/');
        // Si l'email existe déjà (compte local ou Google)
        if ($existingUser) {
            $errorMsg = $existingUser->auth_provider === 'google'
                ? 'Ce compte Google est déjà enregistré. Veuillez vous connecter.'
                : 'Cet email est déjà enregistré avec un mot de passe. Veuillez vous connecter normalement.';
            
            return redirect("{$frontendBase}/auth/google/callback?type=register&error=" . 
                urlencode($errorMsg));
        }
        
        // Créer un nouveau compte
        $user = User::create([
            'name'           => $name,
            'email'          => $email,
            'google_id'      => $googleId,
            'auth_provider'  => 'google',
            'avatar_google'  => $avatar,
            'password'       => null,
            'email_verified_at' => now(), // Email vérifié par Google
            'role'           => 'talent', // Rôle par défaut
        ]);
        
        // Envoyer l'email de bienvenue
        Mail::to($user->email)->send(new WelcomeTalentMail($user));
        
        // Générer un token
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return redirect("{$frontendBase}/auth/google/callback?" . http_build_query([
            'token' => $token,
            'role' => $user->role,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => 'register'
        ]));
    }

    /**
     * Gère la connexion via Google
     */
    private function handleLogin($existingUser, $email, $googleId, $frontendBase)
    {
        $frontendBase = rtrim($frontendBase, '/');
        // Si l'utilisateur n'existe pas
        if (!$existingUser) {
            return redirect("{$frontendBase}/auth/google/callback?type=login&error=" . 
                urlencode('Aucun compte trouvé avec cet email. Veuillez vous inscrire d\'abord.'));
        }
        
        // Si l'utilisateur existe mais n'a pas de google_id (compte local)
        if ($existingUser->auth_provider === 'local' || !$existingUser->google_id) {
            return redirect("{$frontendBase}/auth/google/callback?type=login&error=" . 
                urlencode('Ce compte utilise un mot de passe. Veuillez vous connecter avec votre email et mot de passe.'));
        }
        
        // Vérifier que le google_id correspond
        if ($existingUser->google_id !== $googleId) {
            return redirect("{$frontendBase}/auth/google/callback?type=login&error=" . 
                urlencode('Erreur d\'authentification. Veuillez réessayer.'));
        }
        
        // Connexion réussie
        $token = $existingUser->createToken('auth_token')->plainTextToken;
        
        return redirect("{$frontendBase}/auth/google/callback?" . http_build_query([
            'token' => $token,
            'role' => $existingUser->role,
            'user_id' => $existingUser->id,
            'user_name' => $existingUser->name,
            'type' => 'login'
        ]));
    }
}
