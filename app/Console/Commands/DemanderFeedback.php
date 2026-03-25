<?php

namespace App\Console\Commands;

use App\Mail\DemanderFeedbackMail;
use App\Models\Entretien;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DemanderFeedback extends Command
{
    protected $signature   = 'entretien:demander-feedback';
    protected $description = 'M-06 — Envoie une demande de feedback 30min après la fin d\'un entretien confirmé';

    public function handle(): void
    {
        $maintenant   = Carbon::now();
        $il_y_a_30min = $maintenant->copy()->subMinutes(30);

        // Fenêtre ±5 minutes
        $heureFinMin = $il_y_a_30min->copy()->subMinutes(5)->format('H:i');
        $heureFinMax = $il_y_a_30min->copy()->addMinutes(5)->format('H:i');

        $entretiens = Entretien::where('statut', 'confirme')
            ->whereNull('feedback_demande_at')
            ->where('date', $maintenant->toDateString())
            ->where('heure_fin', '>=', $heureFinMin)
            ->where('heure_fin', '<=', $heureFinMax)
            ->with(['talent', 'entreprise', 'evenement'])
            ->get();

        foreach ($entretiens as $entretien) {
            Mail::to($entretien->talent->email)->send(new DemanderFeedbackMail($entretien));
            $entretien->update(['feedback_demande_at' => now()]);
            $this->info("Demande feedback envoyée : entretien #{$entretien->id} — {$entretien->talent->name}");
        }

        if ($entretiens->isEmpty()) {
            $this->info('Aucune demande de feedback à envoyer.');
        }
    }
}
