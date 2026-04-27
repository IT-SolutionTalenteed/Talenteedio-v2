<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord de l'entreprise
     */
    public function index()
    {
        $user = auth()->user();
        $entreprise = Entreprise::where('user_id', $user->id)->first();
        
        $stats = [
            'total_offres' => 0,
            'active_offres' => 0,
            'total_candidatures' => 0,
            'total_entretiens' => 0,
        ];

        if ($entreprise) {
            $stats['total_offres'] = $entreprise->offres()->count();
            $stats['active_offres'] = $entreprise->offres()
                ->where(function($query) {
                    $query->where('date_limite', '>=', now())
                          ->orWhereNull('date_limite');
                })
                ->count();
            $stats['total_candidatures'] = \App\Models\Candidature::whereHas('offre', function($q) use ($entreprise) {
                $q->where('entreprise_id', $entreprise->id);
            })->count();
            $stats['total_entretiens'] = \App\Models\Entretien::where('entreprise_id', $entreprise->id)->count();
        }
        
        return response()->json([
            'message' => 'Tableau de bord entreprise',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'stats' => $stats,
            'entreprise' => $entreprise ? [
                'id' => $entreprise->id,
                'nom' => $entreprise->nom,
                'logo_url' => $entreprise->logo_url,
            ] : null,
        ]);
    }

    /**
     * Retourner uniquement les statistiques pour le dashboard
     */
    public function stats()
    {
        $user = auth()->user();
        $entreprise = Entreprise::where('user_id', $user->id)->first();
        
        $stats = [
            'totalOffres' => 0,
            'totalCandidatures' => 0,
            'totalEntretiens' => 0,
            'totalArticles' => 0,
        ];

        if ($entreprise) {
            $stats['totalOffres'] = $entreprise->offres()->count();
            $stats['totalCandidatures'] = \App\Models\Candidature::whereHas('offre', function($q) use ($entreprise) {
                $q->where('entreprise_id', $entreprise->id);
            })->count();
            $stats['totalEntretiens'] = \App\Models\Entretien::where('entreprise_id', $entreprise->id)->count();
            $stats['totalArticles'] = $entreprise->articles()->count();
        }
        
        return response()->json($stats);
    }
}
