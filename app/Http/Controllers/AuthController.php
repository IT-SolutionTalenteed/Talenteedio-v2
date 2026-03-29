<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeTalentMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:talent'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        Mail::to($user->email)->send(new WelcomeTalentMail($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'             => 'required|email',
            'password'          => 'required',
            'recaptcha_token'   => 'required|string',
        ]);

        // Vérification reCAPTCHA v2
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->recaptcha_token,
        ]);

        if (!$recaptchaResponse->successful() || !$recaptchaResponse->json('success')) {
            throw ValidationException::withMessages([
                'recaptcha' => ['Vérification reCAPTCHA échouée. Veuillez réessayer.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'role'       => $user->role,
            ],
        ]);
    }

    public function mobileLogin(Request $request)
    {
        // Endpoint réservé à l'app Flutter — pas de reCAPTCHA
        // Sécurisé par le header X-App-Platform: flutter
        if ($request->header('X-App-Platform') !== 'flutter') {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        $token = $user->createToken('mobile_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email,
            'role'       => $user->role,
            'telephone'  => $user->telephone,
            'ville'      => $user->ville,
            'pays'       => $user->pays,
            'titre_poste'=> $user->titre_poste,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name'            => 'sometimes|nullable|string|max:100',
            'last_name'             => 'sometimes|nullable|string|max:100',
            'email'                 => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'telephone'             => 'sometimes|nullable|string|max:30',
            'ville'                 => 'sometimes|nullable|string|max:100',
            'pays'                  => 'sometimes|nullable|string|max:100',
            'titre_poste'           => 'sometimes|nullable|string|max:255',
            'current_password'      => 'required_with:password|string',
            'password'              => 'sometimes|string|min:8|confirmed',
        ]);

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Mot de passe actuel incorrect.'],
                ]);
            }
            $validated['password'] = Hash::make($validated['password']);
        }

        unset($validated['current_password']);

        // Sync name from first+last
        if (isset($validated['first_name']) || isset($validated['last_name'])) {
            $first = $validated['first_name'] ?? $user->first_name;
            $last  = $validated['last_name']  ?? $user->last_name;
            $validated['name'] = trim("$first $last") ?: $user->name;
        }

        $user->update($validated);

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email,
            'role'       => $user->role,
            'telephone'  => $user->telephone,
            'ville'      => $user->ville,
            'pays'       => $user->pays,
            'titre_poste'=> $user->titre_poste,
        ]);
    }
}