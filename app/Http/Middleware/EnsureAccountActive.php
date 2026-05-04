<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAccountActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Les admins et consultants ne sont jamais bloqués
        if (in_array($user->role, ['admin', 'consultant_externe'])) {
            return $next($request);
        }

        // Vérifier le statut sur l'user directement
        if (isset($user->status) && $user->status === 'pending') {
            return response()->json([
                'message' => 'Votre compte est en cours de vérification. Talenteed.io vous contactera dans les plus brefs délais.',
                'status'  => 'pending',
            ], 403);
        }

        // Pour les entreprises, vérifier aussi sur le modèle Entreprise
        if ($user->role === 'entreprise') {
            $entreprise = $user->entreprise;
            if ($entreprise && $entreprise->status === 'pending') {
                return response()->json([
                    'message' => 'Votre compte est en cours de vérification. Talenteed.io vous contactera dans les plus brefs délais.',
                    'status'  => 'pending',
                ], 403);
            }
        }

        return $next($request);
    }
}
