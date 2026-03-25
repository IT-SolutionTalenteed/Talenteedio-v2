<?php

namespace App\Console\Commands;

use App\Models\Entreprise;
use App\Models\User;
use App\Services\HubSpotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class HubSpotSync extends Command
{
    protected $signature   = 'hubspot:sync {--contacts : Sync contacts seulement} {--companies : Sync companies seulement}';
    protected $description = 'Synchronise tous les talents et entreprises vers HubSpot CRM (batch)';

    public function handle(HubSpotService $hubspot): int
    {
        if (!$hubspot->isConfigured()) {
            $this->error('HUBSPOT_TOKEN non configuré dans .env — sync annulée.');
            return self::FAILURE;
        }

        $syncContacts  = $this->option('contacts')  || (!$this->option('companies'));
        $syncCompanies = $this->option('companies') || (!$this->option('contacts'));

        $stats = ['contacts' => 0, 'companies' => 0, 'errors' => 0];

        // ── Contacts (talents + consultants externes) ─────────────────────────
        if ($syncContacts) {
            $users = User::whereIn('role', ['talent', 'consultant_externe'])->cursor();
            $total = User::whereIn('role', ['talent', 'consultant_externe'])->count();

            $this->info("Sync contacts : {$total} utilisateurs...");
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            foreach ($users as $user) {
                $result = $hubspot->upsertContact($user);
                $result ? $stats['contacts']++ : $stats['errors']++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        // ── Companies (entreprises) ───────────────────────────────────────────
        if ($syncCompanies) {
            $entreprises = Entreprise::all();

            $this->info("Sync companies : {$entreprises->count()} entreprises...");
            $bar = $this->output->createProgressBar($entreprises->count());
            $bar->start();

            foreach ($entreprises as $entreprise) {
                $result = $hubspot->upsertCompany($entreprise);
                $result ? $stats['companies']++ : $stats['errors']++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        // ── Stocker le statut de la dernière sync ─────────────────────────────
        Cache::put('hubspot_last_sync', [
            'at'        => now()->toIso8601String(),
            'contacts'  => $stats['contacts'],
            'companies' => $stats['companies'],
            'errors'    => $stats['errors'],
        ], now()->addDays(30));

        $this->table(
            ['Contacts synchés', 'Companies synchées', 'Erreurs'],
            [[$stats['contacts'], $stats['companies'], $stats['errors']]]
        );

        return self::SUCCESS;
    }
}
