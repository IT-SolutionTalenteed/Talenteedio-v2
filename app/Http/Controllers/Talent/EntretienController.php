<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Entretien;
use App\Models\Evenement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EntretienController extends Controller
{
    /**
     * Retourne les créneaux disponibles pour une entreprise à un événement.
     * Créneaux de 15min entre heure_debut_journee et heure_fin_journee, sur toutes les dates.
     */
    public function creneaux(Request $request, Evenement $evenement)
    {
        $request->validate([
            'entreprise_id' => 'required|integer|exists:entreprises,id',
        ]);

        $creneaux = $this->genererCreneaux($evenement, $request->entreprise_id);

        return response()->json($creneaux);
    }

    /**
     * Réserver un créneau (G-04).
     */
    public function reserver(Request $request, Evenement $evenement)
    {
        $request->validate([
            'entreprise_id' => 'required|integer|exists:entreprises,id',
            'date'          => 'required|date',
            'heure_debut'   => 'required|date_format:H:i',
        ]);

        // Vérifier que l'entreprise participe à l'événement
        $participe = $evenement->entreprises()->where('entreprises.id', $request->entreprise_id)->exists();
        if (!$participe) {
            return response()->json(['message' => "Cette entreprise ne participe pas à l'événement."], 422);
        }

        // Vérifier que le talent n'a pas déjà un entretien avec cette entreprise à cet événement
        $existing = Entretien::where('talent_id', auth()->id())
            ->where('entreprise_id', $request->entreprise_id)
            ->where('evenement_id', $evenement->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Vous avez déjà un entretien réservé avec cette entreprise.'], 422);
        }

        // Vérifier que le créneau est libre
        $heureDebut = $request->heure_debut;
        $heureFin   = Carbon::createFromFormat('H:i', $heureDebut)->addMinutes(15)->format('H:i');

        $creneauPris = Entretien::where('entreprise_id', $request->entreprise_id)
            ->where('evenement_id', $evenement->id)
            ->where('date', $request->date)
            ->where('heure_debut', $heureDebut)
            ->whereIn('statut', ['en_attente', 'confirme'])
            ->exists();

        if ($creneauPris) {
            return response()->json(['message' => 'Ce créneau est déjà pris.'], 422);
        }

        $entretien = Entretien::create([
            'talent_id'     => auth()->id(),
            'entreprise_id' => $request->entreprise_id,
            'evenement_id'  => $evenement->id,
            'date'          => $request->date,
            'heure_debut'   => $heureDebut,
            'heure_fin'     => $heureFin,
            'statut'        => 'en_attente',
        ]);

        return response()->json($entretien->load(['entreprise', 'evenement']), 201);
    }

    /**
     * Mes entretiens réservés.
     */
    public function mesEntretiens()
    {
        $entretiens = Entretien::where('talent_id', auth()->id())
            ->with(['entreprise', 'evenement'])
            ->orderBy('date')
            ->orderBy('heure_debut')
            ->get();

        return response()->json($entretiens);
    }

    /**
     * Annuler un entretien.
     */
    public function annuler(Entretien $entretien)
    {
        abort_if($entretien->talent_id !== auth()->id(), 403);
        $entretien->update(['statut' => 'annule']);
        return response()->json($entretien);
    }

    private function genererCreneaux(Evenement $evenement, int $entrepriseId): array
    {
        $creneaux = [];

        $dateDebut = Carbon::parse($evenement->date_debut);
        $dateFin   = Carbon::parse($evenement->date_fin);

        // Entretiens déjà pris pour cette entreprise à cet événement
        $prisList = Entretien::where('entreprise_id', $entrepriseId)
            ->where('evenement_id', $evenement->id)
            ->whereIn('statut', ['en_attente', 'confirme'])
            ->get()
            ->groupBy(fn($e) => $e->date->format('Y-m-d'))
            ->map(fn($group) => $group->pluck('heure_debut')->toArray());

        $current = $dateDebut->copy();

        while ($current->lte($dateFin)) {
            $dateStr = $current->format('Y-m-d');
            $prisPourJour = $prisList->get($dateStr, []);

            $heure = Carbon::createFromFormat('H:i', substr($evenement->heure_debut_journee, 0, 5));
            $fin   = Carbon::createFromFormat('H:i', substr($evenement->heure_fin_journee, 0, 5));

            $slots = [];
            while ($heure->copy()->addMinutes(15)->lte($fin)) {
                $slotHeure = $heure->format('H:i');
                $slots[] = [
                    'heure_debut' => $slotHeure,
                    'heure_fin'   => $heure->copy()->addMinutes(15)->format('H:i'),
                    'disponible'  => !in_array($slotHeure, $prisPourJour),
                ];
                $heure->addMinutes(15);
            }

            $creneaux[] = [
                'date'  => $dateStr,
                'slots' => $slots,
            ];

            $current->addDay();
        }

        return $creneaux;
    }
}
