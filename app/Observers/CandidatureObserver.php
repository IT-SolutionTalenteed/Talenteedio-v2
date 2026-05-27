<?php

namespace App\Observers;

use App\Models\Candidature;
use App\Services\BrevoService;
use App\Services\HubSpotService;
use Illuminate\Support\Facades\Log;

class CandidatureObserver
{
    public function __construct(
        private HubSpotService $hubspot,
        private BrevoService   $brevo,
    ) {}

    public function created(Candidature $candidature): void
    {
        $candidature->load(['talent', 'offre']);

        try {
            $this->hubspot->createDeal($candidature);
        } catch (\Exception $e) {
            Log::error('[HubSpot] CandidatureObserver createDeal failed', ['candidature_id' => $candidature->id, 'error' => $e->getMessage()]);
        }

        if ($candidature->talent) {
            try {
                $this->brevo->upsertContact($candidature->talent);
            } catch (\Exception $e) {
                Log::error('[Brevo] CandidatureObserver sync failed', ['candidature_id' => $candidature->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
