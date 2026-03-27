<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvenementDemande;
use Illuminate\Http\Request;

class EvenementDemandeController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementDemande::with(['entreprise', 'evenement']);

        if ($request->filled('evenement_id')) {
            $query->where('evenement_id', $request->evenement_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        return response()->json($query->orderByDesc('created_at')->paginate(20));
    }

    public function updateStatut(Request $request, EvenementDemande $evenementDemande)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,acceptee,refusee',
        ]);

        $evenementDemande->update(['statut' => $request->statut]);

        return response()->json($evenementDemande->load(['entreprise', 'evenement']));
    }
}
