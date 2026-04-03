<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Article;
use App\Models\Offre;
use App\Models\Entreprise;
use App\Models\CategorieEvenement;
use App\Models\JobContract;
use App\Models\StudyLevel;
use App\Models\ActivitySector;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicController extends Controller
{
    /**
     * Événement mis en avant.
     */
    public function featuredEvent()
    {
        $event = Evenement::with(['entreprises:id,nom,logo', 'categorie:id,titre'])
            ->where('is_featured', true)
            ->latest()
            ->first();

        return response()->json($event);
    }

    /**
     * Offres d'emploi — liste paginée avec filtres.
     */
    public function offres(Request $request)
    {
        $query = Offre::with([
            'entreprise:id,nom,logo',
            'jobContracts:id,name',
            'studyLevels:id,name',
        ]);

        // Recherche par mot-clé (titre ou mission)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('mission', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre localisation
        if ($request->filled('localisation')) {
            $query->where('localisation', 'like', '%' . $request->localisation . '%');
        }

        // Filtre types de contrat
        if ($request->filled('job_contract_ids')) {
            $query->whereHas('jobContracts', function ($q) use ($request) {
                $q->whereIn('job_contracts.id', (array) $request->job_contract_ids);
            });
        }

        // Filtre niveaux d'étude
        if ($request->filled('study_level_ids')) {
            $query->whereHas('studyLevels', function ($q) use ($request) {
                $q->whereIn('study_levels.id', (array) $request->study_level_ids);
            });
        }

        // Filtre date de publication
        if ($request->filled('date_range')) {
            $minutes = match ($request->date_range) {
                'last_hour'   => 60,
                'last_24h'    => 1440,
                'last_7d'     => 10080,
                'last_14d'    => 20160,
                'last_30d'    => 43200,
                default       => null,
            };
            if ($minutes) {
                $query->where('created_at', '>=', now()->subMinutes($minutes));
            }
        }

        $perPage = min((int) ($request->per_page ?? 8), 50);

        return response()->json(
            $query->latest()->paginate($perPage)
        );
    }

    /**
     * Liste des entreprises — avec nb offres et flag participation événement.
     */
    public function entreprises()
    {
        $entreprises = Entreprise::with('activitySector:id,name')
            ->withCount('offres')
            ->get(['id', 'nom', 'logo', 'description', 'ville', 'pays', 'activity_sector_id'])
            ->map(function ($e) {
                $e->participe_evenement = $e->evenements()->exists();
                return $e;
            });

        return response()->json($entreprises);
    }

    /**
     * Liste des catégories d'événement.
     */
    public function categoriesEvenements()
    {
        $categories = CategorieEvenement::withCount('evenements')
            ->get(['id', 'titre', 'description', 'image', 'video']);

        return response()->json($categories);
    }

    /**
     * Détail d'une catégorie d'événement + liste de ses événements.
     */
    public function categorieEvenement(CategorieEvenement $categorieEvenement)
    {
        $categorieEvenement->load([
            'temoignages:id,auteur,poste,avatar,contenu',
            'evenements' => function ($q) {
                $q->with('entreprises:id,nom,logo')
                  ->orderBy('date_debut');
            },
        ]);

        return response()->json($categorieEvenement);
    }

    /**
     * Articles publiés — paginés avec filtre catégorie média.
     */
    public function articles(Request $request)
    {
        // Endpoint home (sans pagination) : prend les 3 derniers
        if (!$request->has('page') && !$request->has('per_page') && !$request->has('media_category_id')) {
            $articles = Article::with('mediaCategories:id,name')
                ->where('is_published', true)
                ->latest()
                ->take(3)
                ->get(['id', 'title', 'content', 'image', 'created_at']);

            return response()->json($articles);
        }

        $query = Article::with('mediaCategories:id,name')
            ->where('is_published', true);

        if ($request->filled('media_category_id')) {
            $query->whereHas('mediaCategories', function ($q) use ($request) {
                $q->where('media_categories.id', $request->media_category_id);
            });
        }

        $perPage = min((int) ($request->per_page ?? 9), 50);

        return response()->json(
            $query->latest()->paginate($perPage)
        );
    }

    /**
     * Référentiels pour les filtres (contrats, niveaux, secteurs).
     */
    public function referentiels()
    {
        return response()->json([
            'job_contracts'    => JobContract::orderBy('name')->get(['id', 'name']),
            'study_levels'     => StudyLevel::orderBy('name')->get(['id', 'name']),
            'activity_sectors' => ActivitySector::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * 3 dernières offres (homepage legacy).
     */
    public function offresHome()
    {
        $offres = Offre::with('entreprise:id,nom,logo')
            ->latest()
            ->take(6)
            ->get(['id', 'titre', 'localisation', 'mission', 'date_limite', 'entreprise_id', 'image']);

        return response()->json($offres);
    }

    /**
     * Détail d'un événement (public) — avec entreprises participantes et leurs offres.
     */
    public function evenementDetail(Evenement $evenement): JsonResponse
    {
        $evenement->load([
            'categorie:id,titre',
            'entreprises' => function ($q) {
                $q->with([
                    'activitySector:id,name',
                    'offres:id,entreprise_id,titre,localisation,date_limite',
                ])->select('entreprises.id', 'user_id', 'nom', 'logo', 'description', 'site_web', 'ville', 'pays', 'activity_sector_id');
            },
        ]);

        return response()->json($evenement);
    }

    /**
     * Détail d'une offre d'emploi.
     */
    public function offreDetail(Offre $offre): JsonResponse
    {
        $offre->load([
            'entreprise:id,nom,logo,description,site_web,ville,pays,activity_sector_id',
            'entreprise.activitySector:id,name',
            'jobContracts:id,name',
            'studyLevels:id,name',
            'experiences:id,name',
            'jobModes:id,name',
        ]);

        return response()->json($offre);
    }

    /**
     * Détail d'une entreprise avec ses offres et articles publiés.
     */
    public function entrepriseDetail(Entreprise $entreprise): JsonResponse
    {
        $entreprise->load([
            'activitySector:id,name',
        ]);

        $offres = Offre::with(['jobContracts:id,name', 'jobModes:id,name', 'skills:id,name', 'experiences:id,name', 'studyLevels:id,name'])
            ->where('entreprise_id', $entreprise->id)
            ->latest()
            ->get(['id', 'titre', 'localisation', 'mission', 'date_limite', 'entreprise_id', 'profil_recherche', 'nombre_candidatures']);

        $articles = Article::with('mediaCategories:id,name')
            ->where('entreprise_id', $entreprise->id)
            ->where('is_published', true)
            ->latest()
            ->get(['id', 'title', 'content', 'image', 'created_at', 'entreprise_id']);

        $evenements = $entreprise->evenements()
            ->where('date_debut', '>=', now())
            ->orderBy('date_debut')
            ->get(['evenements.id', 'evenements.titre', 'evenements.date_debut', 'evenements.date_fin']);

        $participeEvenement = $evenements->isNotEmpty() || $entreprise->evenements()->exists();

        return response()->json([
            'entreprise'          => $entreprise,
            'offres'              => $offres,
            'articles'            => $articles,
            'evenements'          => $evenements,
            'participe_evenement' => $participeEvenement,
        ]);
    }

    /**
     * Détail d'un article publié (accessible sans connexion).
     */
    public function articleDetail(Article $article): JsonResponse
    {
        if (!$article->is_published) {
            return response()->json(['message' => 'Article introuvable.'], 404);
        }

        $article->load([
            'mediaCategories:id,name',
            'entreprise:id,nom,logo',
        ]);

        return response()->json($article);
    }
}
