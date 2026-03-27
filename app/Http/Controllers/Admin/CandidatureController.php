<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use Illuminate\Http\Request;

class CandidatureController extends Controller
{
    public function index(Request $request)
    {
        $query = Candidature::with(['talent', 'offre.entreprise']);

        if ($request->filled('offre_id')) {
            $query->where('offre_id', $request->offre_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        return response()->json($query->orderByDesc('created_at')->paginate(20));
    }

    public function updateStatut(Request $request, Candidature $candidature)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,acceptee,refusee',
        ]);

        $candidature->update(['statut' => $request->statut]);

        return response()->json($candidature->load(['talent', 'offre.entreprise']));
    }

    public function destroy(Candidature $candidature)
    {
        $candidature->delete();

        return response()->json(['message' => 'Candidature supprimée.']);
    }
}
