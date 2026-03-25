<?php

namespace App\Observers;

use App\Models\Entreprise;
use App\Services\HubSpotService;
use Illuminate\Support\Facades\Log;

class EntrepriseObserver
{
    public function __construct(private HubSpotService $hubspot) {}

    public function created(Entreprise $entreprise): void
    {
        $this->sync($entreprise);
    }

    public function updated(Entreprise $entreprise): void
    {
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
