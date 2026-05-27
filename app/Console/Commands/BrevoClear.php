<?php

namespace App\Console\Commands;

use App\Services\BrevoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BrevoClear extends Command
{
    protected $signature   = 'brevo:clear {--force : Forcer l\'exécution en production}';
    protected $description = 'Supprime tous les contacts Brevo et réinitialise les champs brevo_* en base (dev uniquement)';

    public function handle(BrevoService $brevo): int
    {
        if (!$brevo->isConfigured()) {
            $this->error('BREVO_API_KEY non configuré dans .env');
            return self::FAILURE;
        }

        if (app()->isProduction() && !$this->option('force')) {
            $this->error('Commande refusée en production. Utilisez --force pour confirmer.');
            return self::FAILURE;
        }

        if (!$this->confirm('⚠️  Supprimer TOUS les contacts Brevo et réinitialiser brevo_id/synced_at/sync_error en base ? Action irréversible.')) {
            $this->info('Annulé.');
            return self::SUCCESS;
        }

        $deleted = 0;
        $limit   = 500;

        $this->info('Récupération des contacts Brevo...');

        do {
            $res = Http::withHeaders(['api-key' => config('services.brevo.api_key')])
                ->acceptJson()
                ->get('https://api.brevo.com/v3/contacts', ['limit' => $limit, 'offset' => 0]);

            if (!$res->successful()) {
                $this->error('Erreur API : ' . $res->body());
                return self::FAILURE;
            }

            $contacts = $res->json('contacts', []);
            if (empty($contacts)) break;

            foreach ($contacts as $contact) {
                $delRes = Http::withHeaders(['api-key' => config('services.brevo.api_key')])
                    ->delete("https://api.brevo.com/v3/contacts/{$contact['email']}");
                if ($delRes->successful() || $delRes->status() === 204) {
                    $deleted++;
                    $this->output->write('.');
                } else {
                    $this->warn("  Impossible de supprimer le contact {$contact['email']} : " . $delRes->status());
                }
                usleep(100000); // 0.1s to respect Brevo rate limit
            }

            $this->line("  Supprimés : {$deleted}");

        } while (count($contacts) === $limit);

        // Réinitialiser les champs brevo_* en base
        \App\Models\User::whereNotNull('brevo_id')
            ->update(['brevo_id' => null, 'brevo_synced_at' => null, 'brevo_sync_error' => null]);

        \App\Models\Entreprise::whereNotNull('brevo_id')
            ->update(['brevo_id' => null, 'brevo_synced_at' => null, 'brevo_sync_error' => null]);

        $this->info("✅ {$deleted} contacts supprimés de Brevo. Champs brevo_* réinitialisés en base.");
        return self::SUCCESS;
    }
}
