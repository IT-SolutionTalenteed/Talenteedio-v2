<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\User;
use App\Services\BrevoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class BrevoController extends Controller
{
    public function __construct(private BrevoService $brevo) {}

    public function status()
    {
        $talents = User::whereIn('role', ['talent', 'consultant_externe'])
            ->select('id', 'name', 'email', 'role', 'brevo_id', 'brevo_synced_at', 'brevo_sync_error')
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'type'       => 'talent',
                'name'       => $u->name,
                'email'      => $u->email,
                'brevo_id'   => $u->brevo_id,
                'synced_at'  => $u->brevo_synced_at,
                'sync_error' => $u->brevo_sync_error,
            ]);

        $entreprises = Entreprise::select('id', 'nom', 'email', 'brevo_id', 'brevo_synced_at', 'brevo_sync_error')
            ->orderBy('nom')
            ->get()
            ->map(fn($e) => [
                'id'         => $e->id,
                'type'       => 'entreprise',
                'name'       => $e->nom,
                'email'      => $e->email,
                'brevo_id'   => $e->brevo_id,
                'synced_at'  => $e->brevo_synced_at,
                'sync_error' => $e->brevo_sync_error,
            ]);

        return response()->json([
            'configured' => $this->brevo->isConfigured(),
            'last_sync'  => Cache::get('brevo_last_sync'),
            'contacts'   => $talents->merge($entreprises)->values(),
        ]);
    }

    public function sync(Request $request)
    {
        $request->validate([
            'items'        => 'required|array|min:1',
            'items.*.id'   => 'required|integer',
            'items.*.type' => 'required|in:talent,entreprise',
        ]);

        $success = 0;
        $errors  = 0;

        foreach ($request->input('items') as $item) {
            if ($item['type'] === 'talent') {
                $user = User::find($item['id']);
                ($user && $this->brevo->upsertContact($user) !== null) ? $success++ : $errors++;
            } else {
                $entreprise = Entreprise::find($item['id']);
                $result = $entreprise ? $this->brevo->upsertEntreprise($entreprise) : null;
                if ($result === false || $result === null) {
                    $errors++;
                } else {
                    $success++;
                }
            }
        }

        return response()->json([
            'message' => "{$success} synchronisé(s), {$errors} erreur(s)",
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function syncAll()
    {
        $stats = $this->brevo->syncAll();

        Cache::put('brevo_last_sync', [
            'at'          => now()->toIso8601String(),
            'contacts'    => $stats['contacts'],
            'entreprises' => $stats['entreprises'],
            'skipped'     => $stats['skipped'],
            'errors'      => $stats['errors'],
        ], now()->addDays(30));

        return response()->json([
            'message'   => 'Synchronisation complète terminée',
            'last_sync' => Cache::get('brevo_last_sync'),
        ]);
    }

    public function setup()
    {
        Artisan::call('brevo:setup');
        return response()->json([
            'message' => 'Setup Brevo terminé',
            'output'  => Artisan::output(),
        ]);
    }
}
