<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entretien;
use App\Models\Evenement;
use Illuminate\Http\Request;

class EntretienController extends Controller
{
    /**
     * D-05 — Liste des entretiens par événement, groupés par stand (entreprise).
     */
    public function index(Request $request)
    {
        $request->validate([
            'evenement_id' => 'required|integer|exists:evenements,id',
        ]);

        $entretiens = Entretien::where('evenement_id', $request->evenement_id)
            ->with(['talent', 'entreprise'])
            ->orderBy('entreprise_id')
            ->orderBy('date')
            ->orderBy('heure_debut')
            ->get()
            ->groupBy('entreprise_id')
            ->map(fn($group) => [
                'entreprise' => $group->first()->entreprise,
                'entretiens' => $group->values(),
            ])
            ->values();

        return response()->json($entretiens);
    }

    public function evenementsList()
    {
        $evenements = Evenement::orderByDesc('date_debut')->get(['id', 'titre', 'date_debut', 'date_fin', 'is_featured']);
        return response()->json($evenements);
    }

    public function featuredEvenement()
    {
        $evenement = Evenement::where('is_featured', true)->first(['id', 'titre', 'date_debut', 'date_fin', 'is_featured']);
        return response()->json($evenement);
    }
}
