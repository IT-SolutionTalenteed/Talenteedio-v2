<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Models\MatchingResult;
use App\Models\Offre;
use App\Services\OpenAIMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvenementController extends Controller
{
    /**
     * Historique des matchings du talent connecté.
     */
    public function mesMatchings()
    {
        $matchings = MatchingResult::where('user_id', auth()->id())
            ->with('evenement:id,titre,date_debut,date_fin')
            ->latest()
            ->get();

        return response()->json($matchings);
    }

    /**
     * Liste des événements disponibles.
     */
    public function index()
    {
        $evenements = Evenement::with('categorie')
            ->orderByDesc('date_debut')
            ->get();

        return response()->json($evenements);
    }

    /**
     * G-03 — Matching événement : talent vs entreprises participantes.
     * Profil complet BDD + CV parsé + offres des entreprises.
     */
    public function matching(Request $request, Evenement $evenement, OpenAIMatchingService $matchingService)
    {
        $request->validate([
            'poste_recherche' => 'required|string|max:255',
            'cv'              => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $talent = auth()->user();
        $talent->load(['activitySectors', 'skills', 'languages', 'studyLevel', 'experience', 'secteurSouhaite']);

        // Parsing CV
        $cvText  = null;
        $cvPath  = null;
        if ($request->hasFile('cv')) {
            $file   = $request->file('cv');
            $cvPath = $file->store('matching/cvs', 'public');
            $cvText = $matchingService->parseCv(
                Storage::disk('public')->path($cvPath),
                $file->getClientOriginalName()
            );
        }

        $evenement->load(['entreprises.offres.skills', 'entreprises.offres.activitySector', 'entreprises.activitySector']);

        if ($evenement->entreprises->isEmpty()) {
            return response()->json(['message' => 'Aucune entreprise participante pour cet événement.'], 422);
        }

        $results = $matchingService->matchEvenement($talent, $request->poste_recherche, $cvText, $evenement);

        // Persister pour l'historique
        MatchingResult::create([
            'user_id'         => $talent->id,
            'evenement_id'    => $evenement->id,
            'poste_recherche' => $request->poste_recherche,
            'resultats'       => $results,
            'cv_path'         => $cvPath,
        ]);

        return response()->json([
            'evenement' => $evenement->only(['id', 'titre', 'date_debut', 'date_fin']),
            'resultats' => $results,
        ]);
    }

    /**
     * G-03b — Matching global : talent vs toutes les offres en base.
     * Permet au talent de trouver des offres pertinentes sans passer par un événement.
     */
    public function matchingOffresGlobal(Request $request, OpenAIMatchingService $matchingService)
    {
        $request->validate([
            'poste_recherche' => 'required|string|max:255',
            'cv'              => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'limit'           => 'nullable|integer|min:5|max:50',
        ]);

        $talent = auth()->user();
        $talent->load(['activitySectors', 'skills', 'languages', 'studyLevel', 'experience', 'secteurSouhaite']);

        // Parsing CV
        $cvText = null;
        if ($request->hasFile('cv')) {
            $file   = $request->file('cv');
            $cvPath = $file->store('matching/cvs', 'public');
            $cvText = $matchingService->parseCv(
                Storage::disk('public')->path($cvPath),
                $file->getClientOriginalName()
            );
        }

        // Charger toutes les offres avec toutes leurs relations
        $offres = Offre::with([
            'entreprise.activitySector',
            'activitySector',
            'skills',
            'jobContracts',
            'jobModes',
            'studyLevels',
            'experiences',
        ])->get();

        if ($offres->isEmpty()) {
            return response()->json(['message' => 'Aucune offre disponible.'], 422);
        }

        // Si trop d'offres, pré-filtrer par secteur ou mots-clés pour éviter un prompt trop long
        $limit  = $request->input('limit', 30);
        $offres = $this->preselectOffres($offres, $talent, $request->poste_recherche, $limit);

        $results = $matchingService->matchOffresGlobal($talent, $request->poste_recherche, $cvText, $offres);

        return response()->json([
            'poste_recherche' => $request->poste_recherche,
            'total_offres_analysees' => $offres->count(),
            'resultats' => $results,
        ]);
    }

    /**
     * Pré-sélection légère des offres avant l'envoi à OpenAI.
     * Priorise les offres dont le secteur ou la localisation correspond aux préférences du talent.
     * Tombe en fallback sur les offres les plus récentes si pas assez de correspondances.
     */
    private function preselectOffres($offres, $talent, string $posteRecherche, int $limit)
    {
        $secteurId    = $talent->secteur_souhaite_id;
        $paysSouhaites = $talent->pays_souhaites ?? [];
        $keywords     = collect(explode(' ', strtolower($posteRecherche)))->filter(fn($w) => strlen($w) > 2);

        // Score de priorité rapide (sans IA)
        $scored = $offres->map(function ($offre) use ($secteurId, $paysSouhaites, $keywords) {
            $priority = 0;
            if ($secteurId && ($offre->activity_sector_id === $secteurId || $offre->entreprise?->activity_sector_id === $secteurId)) {
                $priority += 3;
            }
            if (!empty($paysSouhaites) && $offre->entreprise?->pays) {
                foreach ($paysSouhaites as $pays) {
                    if (stripos($offre->entreprise->pays, $pays) !== false) {
                        $priority += 2;
                        break;
                    }
                }
            }
            foreach ($keywords as $kw) {
                if (stripos($offre->titre, $kw) !== false || stripos($offre->description ?? '', $kw) !== false) {
                    $priority += 1;
                }
            }
            $offre->_priority = $priority;
            return $offre;
        });

        return $scored->sortByDesc('_priority')->take($limit)->values();
    }
}
