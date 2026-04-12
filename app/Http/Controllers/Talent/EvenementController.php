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
     * POST /talent/cv/parse
     * Parse un CV uploadé et retourne les compétences extraites + résumé du profil.
     * Utilisé pour pré-remplir le formulaire de matching côté client.
     */
    public function parseCv(Request $request, OpenAIMatchingService $matchingService)
    {
        $request->validate([
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $file   = $request->file('cv');
        $cvPath = $file->store('matching/cvs', 'public');
        $cvText = $matchingService->parseCv(
            Storage::disk('public')->path($cvPath),
            $file->getClientOriginalName()
        );

        if (!$cvText) {
            return response()->json([
                'competences' => [],
                'resume'      => null,
                'message'     => 'Impossible d\'extraire le contenu de ce fichier.',
            ]);
        }

        // Demander à OpenAI d'extraire les compétences du CV
        $extracted = $matchingService->extractCvSkills($cvText);

        return response()->json([
            'competences' => $extracted['competences'] ?? [],
            'resume'      => $extracted['resume']      ?? null,
            'cv_path'     => $cvPath,
        ]);
    }

    /**
     * G-03 — Matching événement : talent vs entreprises participantes.
     * Les champs du formulaire (pays_souhaites, villes_souhaitees, secteur_souhaite_id, competences)
     * overrident les valeurs du profil BDD.
     */
    public function matching(Request $request, Evenement $evenement, OpenAIMatchingService $matchingService)
    {
        $request->validate([
            'poste_recherche'    => 'required|string|max:255',
            'cv'                 => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'cv_path'            => 'nullable|string',   // chemin si déjà uploadé via parseCv
            'competences'        => 'nullable|string',   // compétences extraites du CV ou saisies
            'pays_souhaites'     => 'nullable|array',
            'pays_souhaites.*'   => 'string|max:100',
            'villes_souhaitees'  => 'nullable|array',
            'villes_souhaitees.*'=> 'string|max:100',
            'secteur_souhaite_id'=> 'nullable|integer|exists:activity_sectors,id',
        ]);

        $talent = auth()->user();
        $talent->load(['activitySectors', 'skills', 'languages', 'studyLevel', 'experience', 'secteurSouhaite']);

        // Parsing CV (nouveau fichier ou réutilisation du cv_path déjà parsé)
        $cvText = null;
        $cvPath = $request->input('cv_path');

        if ($request->hasFile('cv')) {
            $file   = $request->file('cv');
            $cvPath = $file->store('matching/cvs', 'public');
            $cvText = $matchingService->parseCv(
                Storage::disk('public')->path($cvPath),
                $file->getClientOriginalName()
            );
        } elseif ($cvPath) {
            // CV déjà parsé lors de l'étape parseCv — on reparse depuis le disque
            $fullPath = Storage::disk('public')->path($cvPath);
            if (file_exists($fullPath)) {
                $cvText = $matchingService->parseCv($fullPath, basename($cvPath));
            }
        }

        // Overrides formulaire → profil temporaire pour ce matching
        $overrides = [
            'pays_souhaites'      => $request->input('pays_souhaites'),
            'villes_souhaitees'   => $request->input('villes_souhaitees'),
            'secteur_souhaite_id' => $request->input('secteur_souhaite_id'),
            'competences_libres'  => $request->input('competences'), // texte libre extrait du CV
        ];

        $evenement->load(['entreprises.offres.skills', 'entreprises.offres.activitySector', 'entreprises.activitySector']);

        if ($evenement->entreprises->isEmpty()) {
            return response()->json(['message' => 'Aucune entreprise participante pour cet événement.'], 422);
        }

        $results = $matchingService->matchEvenement($talent, $request->poste_recherche, $cvText, $evenement, $overrides);

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
     */
    public function matchingOffresGlobal(Request $request, OpenAIMatchingService $matchingService)
    {
        $request->validate([
            'poste_recherche'    => 'required|string|max:255',
            'cv'                 => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'cv_path'            => 'nullable|string',
            'competences'        => 'nullable|string',
            'pays_souhaites'     => 'nullable|array',
            'pays_souhaites.*'   => 'string|max:100',
            'villes_souhaitees'  => 'nullable|array',
            'villes_souhaitees.*'=> 'string|max:100',
            'secteur_souhaite_id'=> 'nullable|integer|exists:activity_sectors,id',
            'limit'              => 'nullable|integer|min:5|max:50',
        ]);

        $talent = auth()->user();
        $talent->load(['activitySectors', 'skills', 'languages', 'studyLevel', 'experience', 'secteurSouhaite']);

        $cvText = null;
        $cvPath = $request->input('cv_path');

        if ($request->hasFile('cv')) {
            $file   = $request->file('cv');
            $cvPath = $file->store('matching/cvs', 'public');
            $cvText = $matchingService->parseCv(
                Storage::disk('public')->path($cvPath),
                $file->getClientOriginalName()
            );
        } elseif ($cvPath) {
            $fullPath = Storage::disk('public')->path($cvPath);
            if (file_exists($fullPath)) {
                $cvText = $matchingService->parseCv($fullPath, basename($cvPath));
            }
        }

        $overrides = [
            'pays_souhaites'      => $request->input('pays_souhaites'),
            'villes_souhaitees'   => $request->input('villes_souhaitees'),
            'secteur_souhaite_id' => $request->input('secteur_souhaite_id'),
            'competences_libres'  => $request->input('competences'),
        ];

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

        $limit  = $request->input('limit', 30);
        $offres = $this->preselectOffres($offres, $talent, $request->poste_recherche, $limit, $overrides);

        $results = $matchingService->matchOffresGlobal($talent, $request->poste_recherche, $cvText, $offres, $overrides);

        return response()->json([
            'poste_recherche'        => $request->poste_recherche,
            'total_offres_analysees' => $offres->count(),
            'resultats'              => $results,
        ]);
    }

    /**
     * Pré-sélection légère des offres avant envoi à OpenAI.
     * Utilise les overrides formulaire en priorité, puis le profil BDD.
     */
    private function preselectOffres($offres, $talent, string $posteRecherche, int $limit, array $overrides = [])
    {
        $secteurId     = $overrides['secteur_souhaite_id'] ?? $talent->secteur_souhaite_id;
        $paysSouhaites = $overrides['pays_souhaites']      ?? $talent->pays_souhaites ?? [];
        $keywords      = collect(explode(' ', strtolower($posteRecherche)))->filter(fn($w) => strlen($w) > 2);

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
