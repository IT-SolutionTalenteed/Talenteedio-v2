<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Mot de passe incorrect.'], 422);
        }

        // Supprimer le CV stocké
        if ($user->cv_path) {
            Storage::disk('public')->delete($user->cv_path);
        }

        // Révoquer tous les tokens Sanctum
        $user->tokens()->delete();

        // Supprimer le compte (cascade via FK ou suppression manuelle)
        $user->delete();

        return response()->json(['message' => 'Compte supprimé avec succès.'], 200);
    }
}
