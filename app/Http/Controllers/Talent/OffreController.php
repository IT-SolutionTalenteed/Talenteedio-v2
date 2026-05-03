<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Offre;
use App\Models\OffreMatching;
use Illuminate\Http\Request;

class OffreController extends Controller
{
    private function checkCandidatureLimit(Offre $offre): void
    {
        $max = $offre->entreprise?->plan?->max_candidatures_par_offre ?? null;
        if ($max === null) return;

        if ($offre->candidatures()->count() >= $max) {
            abort(403, "Cette offre ne peut plus recevoir de candidatures (limite de {$max} atteinte).");
        }
    }
    /**
     * Liste toutes les offres publiées (publiques pour les talents).
     */
    public function index(Request $request)
    {
        $offres = Offre::with(['entreprise', 'jobContracts', 'jobModes', 'skills'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($offres);
    }

    public function show(Offre $offre)
    {
        return response()->json(
            $offre->load(['entreprise', 'jobContracts', 'jobModes', 'skills', 'studyLevels', 'experiences'])
        );
    }

    /**
     * Postuler à une offre (G-01).
     */
    public function postuler(Request $request, Offre $offre)
    {
        $request->validate([
            'cv'      => 'required|file|mimes:pdf,doc,docx|max:5120',
            'message' => 'nullable|string|max:1000',
        ]);

        $talent = auth()->user();

        // Vérifier la limite de candidatures par offre selon le plan de l'entreprise
        $offre->loadMissing('entreprise.plan');
        $this->checkCandidatureLimit($offre);

        // Vérifier si le talent a déjà postulé
        $existing = Candidature::where('talent_id', $talent->id)
            ->where('offre_id', $offre->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Vous avez déjà postulé à cette offre.'], 422);
        }

        $cvPath = $request->file('cv')->store('candidatures/cvs', 'public');

        // Vérifier si un matching existe pour cette offre
        $matching = OffreMatching::where('talent_id', $talent->id)
            ->where('offre_id', $offre->id)
            ->first();

        $matchingScore = $matching ? $matching->score : 0;

        // Déterminer le statut en fonction du score de matching
        // Score >= 80% : validation automatique
        // Score < 80% : en attente de validation admin
        $statut = $matchingScore >= 80 ? 'en_attente' : 'en_attente_validation';

        $candidature = Candidature::create([
            'talent_id' => $talent->id,
            'offre_id'  => $offre->id,
            'statut'    => $statut,
            'cv'        => $cvPath,
            'message'   => $request->message,
        ]);

        // Charger les relations nécessaires
        $candidature->load(['offre.entreprise', 'talent']);

        // Envoyer les emails en fonction du score
        if ($matchingScore >= 80) {
            // Validation automatique : envoyer directement à l'entreprise
            \Illuminate\Support\Facades\Mail::to($candidature->offre->entreprise->email)
                ->send(new \App\Mail\CandidatureValideeEntrepriseMail($candidature, $matchingScore));

            // Confirmation au talent
            \Illuminate\Support\Facades\Mail::to($talent->email)
                ->send(new \App\Mail\CandidatureValideeConfirmationMail($candidature, $matchingScore));

            \Illuminate\Support\Facades\Log::info("Candidature auto-validée", [
                'candidature_id' => $candidature->id,
                'matching_score' => $matchingScore,
            ]);
        } else {
            // Score < 80% : en attente de validation admin
            // Email au talent pour patienter
            \Illuminate\Support\Facades\Mail::to($talent->email)
                ->send(new \App\Mail\CandidatureEnAttenteValidationMail($candidature, $matchingScore));

            // Email à l'admin pour validation
            $adminEmail = config('mail.admin_email', 'admin@talenteed.io');
            \Illuminate\Support\Facades\Mail::to($adminEmail)
                ->send(new \App\Mail\CandidatureEnAttenteAdminMail($candidature, $matchingScore));

            \Illuminate\Support\Facades\Log::info("Candidature en attente de validation admin", [
                'candidature_id' => $candidature->id,
                'matching_score' => $matchingScore,
            ]);
        }

        return response()->json([
            'candidature' => $candidature,
            'matching_score' => $matchingScore,
            'auto_validated' => $matchingScore >= 80,
        ], 201);
    }

    /**
     * Mes candidatures.
     */
    public function mesCandidatures()
    {
        $candidatures = Candidature::where('talent_id', auth()->id())
            ->with(['offre.entreprise'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($candidatures);
    }

    /**
     * Matcher le CV du talent avec une offre spécifique.
     */
    public function matchWithJob(Request $request, Offre $offre)
    {
        $talent = auth()->user();
        
        // Vérifier si un matching existe déjà pour ce talent et cette offre
        $existingMatching = OffreMatching::where('talent_id', $talent->id)
            ->where('offre_id', $offre->id)
            ->first();

        // Si un matching existe et qu'aucun nouveau CV n'est fourni, retourner le résultat en cache
        if ($existingMatching && !$request->hasFile('cv')) {
            \Illuminate\Support\Facades\Log::info("Returning cached matching result", [
                'talent_id' => $talent->id,
                'offre_id' => $offre->id,
                'cached_at' => $existingMatching->created_at,
            ]);

            return response()->json([
                'offre' => [
                    'id' => $offre->id,
                    'titre' => $offre->titre,
                    'entreprise' => $offre->entreprise?->nom,
                    'logo_url' => $offre->entreprise?->logo_url,
                ],
                'matching' => [
                    'score' => $existingMatching->score,
                    'raison' => $existingMatching->raison,
                    'details' => $existingMatching->details,
                ],
                'cached' => true,
                'cached_at' => $existingMatching->created_at->toIso8601String(),
            ]);
        }

        // Si un nouveau CV est fourni, faire un nouveau matching
        $request->validate([
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $matchingService = app(\App\Services\OpenAIMatchingService::class);
        $talent->load(['activitySectors', 'skills', 'languages', 'studyLevel', 'experience', 'secteurSouhaite']);

        // Parser le CV
        $file = $request->file('cv');
        $cvPath = $file->store('matching/cvs', 'public');
        $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($cvPath);
        
        \Illuminate\Support\Facades\Log::info("Attempting to parse CV", [
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $cvPath,
            'full_path' => $fullPath,
            'file_exists' => file_exists($fullPath),
            'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0,
        ]);

        $cvText = $matchingService->parseCv($fullPath, $file->getClientOriginalName());

        // Si le parsing échoue complètement, on utilise quand même le profil
        if (!$cvText) {
            \Illuminate\Support\Facades\Log::warning("CV parsing returned null, using profile data only");
            $cvText = "CV uploadé - analyse basée sur le profil utilisateur";
        }

        // Charger l'offre avec toutes ses relations
        $offre->load([
            'entreprise.activitySector',
            'activitySector',
            'skills',
            'jobContracts',
            'jobModes',
            'studyLevels',
            'experiences',
        ]);

        // Effectuer le matching
        $result = $matchingService->matchSingleOffre($talent, $cvText, $offre);

        // Sauvegarder ou mettre à jour le résultat du matching
        OffreMatching::updateOrCreate(
            [
                'talent_id' => $talent->id,
                'offre_id' => $offre->id,
            ],
            [
                'cv_path' => $cvPath,
                'score' => $result['score'],
                'raison' => $result['raison'],
                'details' => $result['details'] ?? null,
            ]
        );

        \Illuminate\Support\Facades\Log::info("Matching result saved", [
            'talent_id' => $talent->id,
            'offre_id' => $offre->id,
            'score' => $result['score'],
        ]);

        return response()->json([
            'offre' => [
                'id' => $offre->id,
                'titre' => $offre->titre,
                'entreprise' => $offre->entreprise?->nom,
                'logo_url' => $offre->entreprise?->logo_url,
            ],
            'matching' => $result,
            'cached' => false,
            'debug' => [
                'cv_text_length' => strlen($cvText),
                'cv_preview' => substr($cvText, 0, 200),
            ]
        ]);
    }

    /**
     * Test de parsing de CV (debug uniquement).
     */
    public function testCvParsing(Request $request)
    {
        $request->validate([
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $matchingService = app(\App\Services\OpenAIMatchingService::class);
        $file = $request->file('cv');
        $cvPath = $file->store('matching/cvs', 'public');
        $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($cvPath);

        $cvText = $matchingService->parseCv($fullPath, $file->getClientOriginalName());

        return response()->json([
            'success' => !empty($cvText),
            'file_info' => [
                'original_name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ],
            'storage_info' => [
                'stored_path' => $cvPath,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0,
            ],
            'parsing_result' => [
                'text_length' => $cvText ? strlen($cvText) : 0,
                'preview' => $cvText ? substr($cvText, 0, 500) : null,
            ],
        ]);
    }

    /**
     * Récupérer le matching existant pour une offre.
     */
    public function getExistingMatch(Offre $offre)
    {
        $talent = auth()->user();
        
        $matching = OffreMatching::where('talent_id', $talent->id)
            ->where('offre_id', $offre->id)
            ->first();

        if (!$matching) {
            return response()->json([
                'exists' => false,
            ]);
        }

        return response()->json([
            'exists' => true,
            'matching' => [
                'score' => $matching->score,
                'raison' => $matching->raison,
                'details' => $matching->details,
                'created_at' => $matching->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Historique des matchings du talent.
     */
    public function mesMatchings()
    {
        $matchings = OffreMatching::where('talent_id', auth()->id())
            ->with(['offre.entreprise'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($matching) {
                return [
                    'id' => $matching->id,
                    'score' => $matching->score,
                    'raison' => $matching->raison,
                    'details' => $matching->details,
                    'created_at' => $matching->created_at->toIso8601String(),
                    'offre' => [
                        'id' => $matching->offre->id,
                        'titre' => $matching->offre->titre,
                        'localisation' => $matching->offre->localisation,
                        'entreprise' => [
                            'nom' => $matching->offre->entreprise?->nom,
                            'logo_url' => $matching->offre->entreprise?->logo_url,
                        ],
                    ],
                ];
            });

        return response()->json($matchings);
    }
}
