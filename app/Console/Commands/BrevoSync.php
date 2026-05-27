<?php

namespace App\Console\Commands;

use App\Services\BrevoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class BrevoSync extends Command
{
    protected $signature   = 'brevo:sync {--contacts : Sync contacts seulement} {--entreprises : Sync entreprises seulement} {--limit=0 : Limiter le nombre de contacts (0 = tous)}';
    protected $description = 'Synchronise tous les talents et entreprises vers Brevo (batch)';

    public function handle(BrevoService $brevo): int
    {
        if (!$brevo->isConfigured()) {
            $this->error('BREVO_API_KEY non configuré dans .env — sync annulée.');
            return self::FAILURE;
        }

        $syncContacts    = $this->option('contacts')    || (!$this->option('entreprises'));
        $syncEntreprises = $this->option('entreprises') || (!$this->option('contacts'));

        $stats = ['contacts' => 0, 'entreprises' => 0, 'skipped' => 0, 'errors' => 0];

        if ($syncContacts) {
            $limit = (int) $this->option('limit');
            $query = \App\Models\User::whereIn('role', ['talent', 'consultant_externe'])->orderBy('id');

            if ($limit > 0) {
                $users = $query->limit($limit)->get();
                $total = $users->count();
            } else {
                $users = $query->cursor();
                $total = \App\Models\User::whereIn('role', ['talent', 'consultant_externe'])->count();
            }

            $this->info("Sync contacts : {$total} utilisateurs...");
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            foreach ($users as $user) {
                $brevo->upsertContact($user) ? $stats['contacts']++ : $stats['errors']++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        if ($syncEntreprises) {
            $totalEntreprises = \App\Models\Entreprise::count();
            $this->info("Sync entreprises : {$totalEntreprises} entreprises...");
            $bar = $this->output->createProgressBar($totalEntreprises);
            $bar->start();

            foreach (\App\Models\Entreprise::cursor() as $entreprise) {
                $result = $brevo->upsertEntreprise($entreprise);
                if ($result === false) {
                    $stats['skipped']++;
                } elseif ($result === null) {
                    $stats['errors']++;
                } else {
                    $stats['entreprises']++;
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        Cache::put('brevo_last_sync', [
            'at'          => now()->toIso8601String(),
            'contacts'    => $stats['contacts'],
            'entreprises' => $stats['entreprises'],
            'skipped'     => $stats['skipped'],
            'errors'      => $stats['errors'],
        ], now()->addDays(30));

        $this->table(
            ['Contacts synchés', 'Entreprises synchées', 'Ignorées (sans email)', 'Erreurs'],
            [[$stats['contacts'], $stats['entreprises'], $stats['skipped'], $stats['errors']]]
        );

        return self::SUCCESS;
    }
}
