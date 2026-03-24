<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Offre;
use Illuminate\Http\Request;

class OffreController extends Controller
{
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

        // Vérifier si le talent a déjà postulé
        $existing = Candidature::where('talent_id', auth()->id())
            ->where('offre_id', $offre->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Vous avez déjà postulé à cette offre.'], 422);
        }

        $cvPath = $request->file('cv')->store('candidatures/cvs', 'public');

        $candidature = Candidature::create([
            'talent_id' => auth()->id(),
            'offre_id'  => $offre->id,
            'statut'    => 'en_attente',
            'cv'        => $cvPath,
            'message'   => $request->message,
        ]);

        return response()->json($candidature->load(['offre', 'talent']), 201);
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
}
