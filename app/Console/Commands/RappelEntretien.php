<?php

namespace App\Console\Commands;

use App\Mail\RappelEntretienMail;
use App\Models\Entretien;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RappelEntretien extends Command
{
    protected $signature   = 'entretien:rappel';
    protected $description = 'M-05 — Envoie un rappel par mail aux talents 1h avant leur entretien confirmé';

    public function handle(): void
    {
        $maintenant = Carbon::now();
        $dans1h     = $maintenant->copy()->addHour();

        // Fenêtre ±5 minutes pour tolérer la latence du scheduler
        $heureDebutMin = $dans1h->copy()->subMinutes(5)->format('H:i');
        $heureDebutMax = $dans1h->copy()->addMinutes(5)->format('H:i');

        $entretiens = Entretien::where('statut', 'confirme')
            ->where('rappel_envoye', false)
            ->where('date', $maintenant->toDateString())
            ->where('heure_debut', '>=', $heureDebutMin)
            ->where('heure_debut', '<=', $heureDebutMax)
            ->with(['talent', 'entreprise', 'evenement'])
            ->get();

        foreach ($entretiens as $entretien) {
            Mail::to($entretien->talent->email)->send(new RappelEntretienMail($entretien));
            $entretien->update(['rappel_envoye' => true]);
            $this->info("Rappel envoyé : entretien #{$entretien->id} — {$entretien->talent->name}");
        }

        if ($entretiens->isEmpty()) {
            $this->info('Aucun rappel à envoyer.');
        }
    }
}
