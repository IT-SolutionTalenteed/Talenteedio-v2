<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Mail\EvenementDemandeMail;
use App\Models\Entreprise;
use App\Models\Evenement;
use App\Models\EvenementDemande;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EvenementController extends Controller
{
    private function getEntreprise(): Entreprise
    {
        return Entreprise::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * Liste des événements disponibles avec statut de la demande de l'entreprise.
     */
    public function index()
    {
        $entreprise = $this->getEntreprise();

        $evenements = Evenement::with(['categorie'])
            ->orderByDesc('date_debut')
            ->get()
            ->map(function ($evenement) use ($entreprise) {
                $demande = EvenementDemande::where('entreprise_id', $entreprise->id)
                    ->where('evenement_id', $evenement->id)
                    ->first();

                return array_merge($evenement->toArray(), [
                    'demande_statut' => $demande?->statut,
                    'demande_id'     => $demande?->id,
                ]);
            });

        return response()->json($evenements);
    }

    /**
     * Soumettre une demande de participation à un événement (F-03 / M-02).
     */
    public function demandeParticipation(Request $request, Evenement $evenement)
    {
        $entreprise = $this->getEntreprise();

        $request->validate([
            'message' => 'nullable|string|max:1000',
        ]);

        $demande = EvenementDemande::updateOrCreate(
            ['entreprise_id' => $entreprise->id, 'evenement_id' => $evenement->id],
            ['statut' => 'en_attente', 'message' => $request->message]
        );

        // Notifier tous les admins par email
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(
                new EvenementDemandeMail($entreprise->nom, $evenement->titre, $request->message)
            );
        }

        return response()->json($demande, 201);
    }

    /**
     * Mes demandes en cours.
     */
    public function mesDemandes()
    {
        $entreprise = $this->getEntreprise();

        $demandes = EvenementDemande::where('entreprise_id', $entreprise->id)
            ->with('evenement')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($demandes);
    }
}
