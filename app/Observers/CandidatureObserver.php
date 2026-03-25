<?php

namespace App\Observers;

use App\Models\Candidature;
use App\Services\HubSpotService;
use Illuminate\Support\Facades\Log;

class CandidatureObserver
{
    public function __construct(private HubSpotService $hubspot) {}

    public function created(Candidature $candidature): void
    {
        try {
            $this->hubspot->createDeal($candidature->load(['talent', 'offre']));
        } catch (\Exception $e) {
            Log::error('[HubSpot] CandidatureObserver createDeal failed', ['candidature_id' => $candidature->id, 'error' => $e->getMessage()]);
        }
    }
}
