<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Mail\EntretienReponseMail;
use App\Models\Entretien;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EntretienController extends Controller
{
    private function getEntreprise(): Entreprise
    {
        return Entreprise::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * F-05 — Liste des entretiens sur le stand de l'entreprise.
     */
    public function index(Request $request)
    {
        $entreprise = $this->getEntreprise();

        $entretiens = Entretien::where('entreprise_id', $entreprise->id)
            ->with(['talent', 'evenement'])
            ->orderBy('date')
            ->orderBy('heure_debut')
            ->get();

        return response()->json($entretiens);
    }

    /**
     * F-05 — Confirmer ou refuser un entretien.
     */
    public function updateStatut(Request $request, Entretien $entretien)
    {
        $entreprise = $this->getEntreprise();
        abort_if($entretien->entreprise_id !== $entreprise->id, 403);

        $request->validate([
            'statut' => 'required|in:confirme,refuse',
        ]);

        $entretien->update(['statut' => $request->statut]);

        $entretien->load(['talent', 'entreprise', 'evenement']);

        // M-04 — Mail talent avec la réponse
        Mail::to($entretien->talent->email)->send(new EntretienReponseMail($entretien));

        return response()->json($entretien);
    }
}
