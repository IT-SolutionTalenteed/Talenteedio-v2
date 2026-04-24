<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MediaCategory;
use App\Models\Entreprise;
use App\Models\Offre;
use App\Models\Evenement;
use App\Models\Candidature;
use App\Models\ActivitySector;
use App\Models\JobContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord admin
     */
    public function index()
    {
        $user = auth()->user();
        
        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_talents' => User::where('role', 'talent')->count(),
            'total_entreprises' => User::where('role', 'entreprise')->count(),
            'total_media_categories' => MediaCategory::count(),
            'active_media_categories' => MediaCategory::where('is_active', true)->count(),
        ];
        
        return response()->json([
            'message' => 'Tableau de bord administrateur',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'stats' => $stats
        ]);
    }

    /**
     * Statistiques complètes pour le dashboard avec graphiques
     */
    public function stats(Request $request)
    {
        // Période pour les évolutions (6 derniers mois)
        $sixMonthsAgo = Carbon::now()->subMonths(6);
        
        // Période pour les sparklines (7 derniers jours)
        $sevenDaysAgo = Carbon::now()->subDays(7);

        return response()->json([
            // ═══════════════════════════════════════════════════════
            // SPARKLINES (7 derniers jours)
            // ═══════════════════════════════════════════════════════
            'sparklines' => [
                'talents' => $this->getSparklineData(User::where('role', 'talent'), $sevenDaysAgo),
                'entreprises' => $this->getSparklineData(Entreprise::query(), $sevenDaysAgo),
                'offres' => $this->getSparklineData(Offre::query(), $sevenDaysAgo),
                'evenements' => $this->getSparklineData(Evenement::query(), $sevenDaysAgo),
            ],

            // ═══════════════════════════════════════════════════════
            // ÉVOLUTION DES INSCRIPTIONS (6 derniers mois)
            // ═══════════════════════════════════════════════════════
            'evolution' => [
                'months' => $this->getLastSixMonths(),
                'talents' => $this->getMonthlyData(User::where('role', 'talent'), $sixMonthsAgo),
                'entreprises' => $this->getMonthlyData(Entreprise::query(), $sixMonthsAgo),
            ],

            // ═══════════════════════════════════════════════════════
            // SECTEURS D'ACTIVITÉ (Top 6)
            // ═══════════════════════════════════════════════════════
            'secteurs' => ActivitySector::withCount('entreprises')
                ->having('entreprises_count', '>', 0)
                ->orderBy('entreprises_count', 'desc')
                ->limit(6)
                ->get()
                ->map(function ($sector) {
                    return [
                        'name' => $sector->name,
                        'count' => $sector->entreprises_count,
                    ];
                }),

            // ═══════════════════════════════════════════════════════
            // OFFRES PAR TYPE DE CONTRAT
            // ═══════════════════════════════════════════════════════
            'offres_par_contrat' => JobContract::select('job_contracts.id', 'job_contracts.name')
                ->leftJoin('offre_job_contract', 'job_contracts.id', '=', 'offre_job_contract.job_contract_id')
                ->selectRaw('COUNT(offre_job_contract.offre_id) as offres_count')
                ->groupBy('job_contracts.id', 'job_contracts.name')
                ->orderBy('offres_count', 'desc')
                ->get()
                ->map(function ($contract) {
                    return [
                        'name' => $contract->name,
                        'count' => $contract->offres_count,
                    ];
                }),

            // ═══════════════════════════════════════════════════════
            // CANDIDATURES PAR STATUT
            // ═══════════════════════════════════════════════════════
            'candidatures_statut' => [
                'acceptees' => Candidature::where('statut', 'acceptee')->count(),
                'en_attente' => Candidature::where('statut', 'en_attente')->count(),
                'refusees' => Candidature::where('statut', 'refusee')->count(),
                'archivees' => Candidature::where('statut', 'archivee')->count(),
            ],
        ]);
    }

    /**
     * Obtenir les données pour un sparkline (7 derniers jours)
     */
    private function getSparklineData($query, $startDate)
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = (clone $query)->whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        
        return $data;
    }

    /**
     * Obtenir les données mensuelles (6 derniers mois)
     */
    private function getMonthlyData($query, $startDate)
    {
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = (clone $query)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $data[] = $count;
        }
        
        return $data;
    }

    /**
     * Obtenir les noms des 6 derniers mois
     */
    private function getLastSixMonths()
    {
        $months = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->locale('fr')->format('M'); // Jan, Fév, Mar, etc.
        }
        
        return $months;
    }
}
