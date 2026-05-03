<?php

namespace App\Observers;

use App\Mail\EntrepriseActivatedMail;
use App\Models\Entreprise;
use App\Services\HubSpotService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EntrepriseObserver
{
    public function __construct(private HubSpotService $hubspot) {}

    public function created(Entreprise $entreprise): void
    {
        $this->sync($entreprise);
    }

    public function updated(Entreprise $entreprise): void
    {
        // Envoyer mail d'activation quand l'admin passe le statut à 'active'
        if ($entreprise->wasChanged('status') && $entreprise->status === 'active') {
            $user = $entreprise->user;
            if ($user) {
                try {
                    Mail::to($user->email)->send(new EntrepriseActivatedMail($user));
                } catch (\Exception $e) {
                    Log::error('[Mail] EntrepriseActivatedMail failed', ['entreprise_id' => $entreprise->id, 'error' => $e->getMessage()]);
                }
            }
        }

        $this->sync($entreprise);
    }

    private function sync(Entreprise $entreprise): void
    {
        try {
            $this->hubspot->upsertCompany($entreprise);
        } catch (\Exception $e) {
            Log::error('[HubSpot] EntrepriseObserver sync failed', ['entreprise_id' => $entreprise->id, 'error' => $e->getMessage()]);
        }
    }
}
