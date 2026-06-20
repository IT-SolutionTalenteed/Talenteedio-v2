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
        // Paramètre optionnel pour le mois/année (format: YYYY-MM)
        $monthYear = $request->input('month', Carbon::now()->format('Y-m'));
        $selectedDate = Carbon::createFromFormat('Y-m', $monthYear);
        
        // Début et fin du mois sélectionné
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();
        
        // Période pour les sparklines (7 derniers jours du mois sélectionné)
        $sevenDaysAgo = $endOfMonth->copy()->subDays(6);

        return response()->json([
            // ═══════════════════════════════════════════════════════
            // SPARKLINES (7 derniers jours du mois)
            // ═══════════════════════════════════════════════════════
            'sparklines' => [
                'talents' => $this->getSparklineDataForPeriod(
                    User::where('role', 'talent'), 
                    $sevenDaysAgo, 
                    $endOfMonth
                ),
                'entreprises' => $this->getSparklineDataForPeriod(
                    Entreprise::query(), 
                    $sevenDaysAgo, 
                    $endOfMonth
                ),
                'offres' => $this->getSparklineDataForPeriod(
                    Offre::query(), 
                    $sevenDaysAgo, 
                    $endOfMonth
                ),
                'evenements' => $this->getSparklineDataForPeriod(
                    Evenement::query(), 
                    $sevenDaysAgo, 
                    $endOfMonth
                ),
            ],

            // ═══════════════════════════════════════════════════════
            // ÉVOLUTION DES INSCRIPTIONS (pour le mois sélectionné)
            // ═══════════════════════════════════════════════════════
            'evolution' => $this->getMonthlyEvolutionData($monthYear),

            // ═══════════════════════════════════════════════════════
            // SECTEURS D'ACTIVITÉ (Top 6 pour le mois sélectionné)
            // ═══════════════════════════════════════════════════════
            'secteurs' => ActivitySector::select('activity_sectors.id', 'activity_sectors.name')
                ->leftJoin('entreprises', 'entreprises.activity_sector_id', '=', 'activity_sectors.id')
                ->whereBetween('entreprises.created_at', [$startOfMonth, $endOfMonth])
                ->selectRaw('COUNT(entreprises.id) as entreprises_count')
                ->groupBy('activity_sectors.id', 'activity_sectors.name')
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
            // OFFRES PAR TYPE DE CONTRAT (pour le mois sélectionné)
            // ═══════════════════════════════════════════════════════
            'offres_par_contrat' => JobContract::select('job_contracts.id', 'job_contracts.name')
                ->leftJoin('offre_job_contract', 'job_contracts.id', '=', 'offre_job_contract.job_contract_id')
                ->leftJoin('offres', 'offres.id', '=', 'offre_job_contract.offre_id')
                ->whereBetween('offres.created_at', [$startOfMonth, $endOfMonth])
                ->selectRaw('COUNT(DISTINCT offres.id) as offres_count')
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
            // CANDIDATURES PAR STATUT (pour le mois sélectionné)
            // ═══════════════════════════════════════════════════════
            'candidatures_statut' => [
                'acceptees' => Candidature::where('statut', 'acceptee')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
                'en_attente' => Candidature::where('statut', 'en_attente')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
                'refusees' => Candidature::where('statut', 'refusee')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
                'archivees' => Candidature::where('statut', 'archivee')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
            ],

            // ═══════════════════════════════════════════════════════
            // TOTAUX POUR LE MOIS SÉLECTIONNÉ
            // ═══════════════════════════════════════════════════════
            'totals_for_month' => [
                'talents' => User::where('role', 'talent')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
                'entreprises' => Entreprise::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
                'offres' => Offre::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
                'evenements' => Evenement::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
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
     * Obtenir les données pour un sparkline sur une période donnée
     */
    private function getSparklineDataForPeriod($query, $startDate, $endDate)
    {
        $data = [];
        $days = $startDate->diffInDays($endDate);
        
        // Limiter à 7 jours maximum
        $daysToShow = min($days, 6);
        
        for ($i = $daysToShow; $i >= 0; $i--) {
            $date = $endDate->copy()->subDays($i)->format('Y-m-d');
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

    /**
     * Obtenir l'évolution des inscriptions jour par jour pour un mois donné
     */
    private function getMonthlyEvolutionData($monthYear)
    {
        // Parse le mois/année (format: YYYY-MM)
        $date = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
        $daysInMonth = $date->daysInMonth;
        
        $days = [];
        $talents = [];
        $entreprises = [];
        
        // Collecter les données pour chaque jour du mois
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = $date->copy()->day($day);
            
            // Format de la date pour l'axe X (ex: "1 jan", "2 jan", etc.)
            $days[] = $currentDate->format('j') . ' ' . $currentDate->locale('fr')->format('M');
            
            // Compter les inscriptions pour ce jour
            $talents[] = User::where('role', 'talent')
                ->whereDate('created_at', $currentDate->format('Y-m-d'))
                ->count();
                
            $entreprises[] = Entreprise::whereDate('created_at', $currentDate->format('Y-m-d'))
                ->count();
        }
        
        return [
            'month' => $date->locale('fr')->isoFormat('MMMM YYYY'), // ex: "juin 2026"
            'monthYear' => $monthYear, // ex: "2026-06"
            'days' => $days,
            'talents' => $talents,
            'entreprises' => $entreprises,
        ];
    }
}
