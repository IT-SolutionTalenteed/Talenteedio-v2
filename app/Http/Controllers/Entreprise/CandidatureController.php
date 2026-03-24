<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Entreprise;
use App\Models\Offre;
use Illuminate\Http\Request;

class CandidatureController extends Controller
{
    private function getEntreprise(): Entreprise
    {
        return Entreprise::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * Liste toutes les candidatures pour les offres de l'entreprise.
     * Optionnellement filtrées par offre_id.
     */
    public function index(Request $request)
    {
        $entreprise = $this->getEntreprise();

        $query = Candidature::whereHas('offre', fn($q) => $q->where('entreprise_id', $entreprise->id))
            ->with(['talent', 'offre']);

        if ($request->has('offre_id')) {
            $query->where('offre_id', $request->offre_id);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    /**
     * Changer le statut d'une candidature (acceptée / refusée / en_attente).
     */
    public function updateStatut(Request $request, Candidature $candidature)
    {
        $entreprise = $this->getEntreprise();
        abort_if($candidature->offre->entreprise_id !== $entreprise->id, 403);

        $request->validate([
            'statut' => 'required|in:en_attente,acceptee,refusee',
        ]);

        $candidature->update(['statut' => $request->statut]);

        return response()->json($candidature->load(['talent', 'offre']));
    }
}
