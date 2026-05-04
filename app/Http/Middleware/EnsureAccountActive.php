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

        // Le check pending ne concerne QUE les entreprises
        if ($user->role !== 'entreprise') {
            return $next($request);
        }

        // Pour les entreprises, vérifier le statut sur le modèle Entreprise
        $entreprise = $user->entreprise;
        if ($entreprise && $entreprise->status === 'pending') {
            return response()->json([
                'message' => 'Votre compte est en cours de vérification. Talenteed.io vous contactera dans les plus brefs délais.',
                'status'  => 'pending',
            ], 403);
        }

        return $next($request);
    }
}
