<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirige l'utilisateur vers Google OAuth.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    /**
     * Callback Google — crée ou connecte le compte, retourne un token Sanctum.
     * Redirige vers le frontend avec le token en query string.
     */
    public function handleGoogleCallback()
    {
        $frontendBase = env('FRONTEND_URL', 'http://localhost:5173');

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return redirect("{$frontendBase}/login?error=google_auth_failed");
        }

        $email = $googleUser->getEmail();
        if (!$email) {
            return redirect("{$frontendBase}/login?error=no_email");
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            // Créer un compte talent si c'est une première connexion
            $user = User::create([
                'name'          => $googleUser->getName() ?? $email,
                'email'         => $email,
                'password'      => Hash::make(Str::random(32)),
                'role'          => 'talent',
                'google_id'     => $googleUser->getId(),
                'avatar_google' => $googleUser->getAvatar(),
            ]);
        } else {
            // Mettre à jour le google_id si pas encore enregistré
            if (!$user->google_id) {
                $user->update([
                    'google_id'     => $googleUser->getId(),
                    'avatar_google' => $googleUser->getAvatar(),
                ]);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect("{$frontendBase}/auth/google/callback?token={$token}&role={$user->role}");
    }
}
