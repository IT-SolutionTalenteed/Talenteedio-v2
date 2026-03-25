<?php

namespace App\Observers;

use App\Models\User;
use App\Services\HubSpotService;
use Illuminate\Support\Facades\Log;

class TalentObserver
{
    public function __construct(private HubSpotService $hubspot) {}

    public function created(User $user): void
    {
        if (!in_array($user->role, ['talent', 'consultant_externe'])) return;
        $this->sync($user);
    }

    public function updated(User $user): void
    {
        if (!in_array($user->role, ['talent', 'consultant_externe'])) return;
        $this->sync($user);
    }

    private function sync(User $user): void
    {
        try {
            $this->hubspot->upsertContact($user);
        } catch (\Exception $e) {
            Log::error('[HubSpot] TalentObserver sync failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
