<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HubSpotService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class HubSpotController extends Controller
{
    /**
     * Statut de la dernière synchronisation.
     */
    public function status(HubSpotService $hubspot)
    {
        $lastSync = Cache::get('hubspot_last_sync');

        return response()->json([
            'configured' => $hubspot->isConfigured(),
            'last_sync'  => $lastSync,
        ]);
    }

    /**
     * Déclenche manuellement la sync HubSpot.
     */
    public function sync()
    {
        Artisan::call('hubspot:sync');
        $output   = Artisan::output();
        $lastSync = Cache::get('hubspot_last_sync');

        return response()->json([
            'message'   => 'Synchronisation terminée',
            'last_sync' => $lastSync,
            'output'    => $output,
        ]);
    }
}
