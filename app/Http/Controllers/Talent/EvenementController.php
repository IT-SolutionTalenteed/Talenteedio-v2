<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Models\MatchingResult;
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
     * G-03 — Lancer le matching OpenAI pour un événement.
     * Le talent fournit son profil + éventuellement son CV en upload.
     */
    public function matching(Request $request, Evenement $evenement, OpenAIMatchingService $matchingService)
    {
        $request->validate([
            'poste_recherche' => 'required|string|max:255',
            'competences'     => 'nullable|string|max:1000',
            'cv'              => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $talent = auth()->user();

        // Construire le profil textuel
        $talentProfile = "Nom: {$talent->name}\nPoste recherché: {$request->poste_recherche}";
        if ($request->competences) {
            $talentProfile .= "\nCompétences: {$request->competences}";
        }

        // Lire le texte du CV si fourni (pour PDF on stocke et lit le nom, le parsing texte
        // nécessiterait une lib dédiée — on passe le nom de fichier comme indication)
        $cvText = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('matching/cvs', 'public');
            $cvText = "Fichier CV fourni: " . $request->file('cv')->getClientOriginalName();
        }

        $evenement->load(['entreprises.offres']);

        if ($evenement->entreprises->isEmpty()) {
            return response()->json(['message' => 'Aucune entreprise participante pour cet événement.'], 422);
        }

        $results = $matchingService->match($talentProfile, $cvText, $evenement);

        // Persister les résultats pour l'historique "Mes matchings"
        MatchingResult::create([
            'user_id'         => $talent->id,
            'evenement_id'    => $evenement->id,
            'poste_recherche' => $request->poste_recherche,
            'resultats'       => $results,
            'cv_path'         => $cvPath ?? null,
        ]);

        return response()->json([
            'evenement' => $evenement->only(['id', 'titre', 'date_debut', 'date_fin']),
            'resultats' => $results,
        ]);
    }
}
