<?php

namespace App\Console\Commands;

use App\Services\HubSpotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class HubSpotClear extends Command
{
    protected $signature   = 'hubspot:clear {--object=contacts : Object type to clear (contacts|companies|deals)}';
    protected $description = 'Archive (supprime) tous les enregistrements HubSpot d\'un type donné (contacts par défaut)';

    private string $baseUrl = 'https://api.hubapi.com';

    public function handle(HubSpotService $hubspot): int
    {
        if (!$hubspot->isConfigured()) {
            $this->error('HUBSPOT_TOKEN non configuré dans .env');
            return self::FAILURE;
        }

        $objectType = $this->option('object');
        $allowed    = ['contacts', 'companies', 'deals'];

        if (!in_array($objectType, $allowed)) {
            $this->error("Type invalide. Choisir parmi : " . implode(', ', $allowed));
            return self::FAILURE;
        }

        if (!$this->confirm("⚠️  Supprimer TOUS les {$objectType} HubSpot ? Cette action est irréversible.")) {
            $this->info('Annulé.');
            return self::SUCCESS;
        }

        $token    = config('services.hubspot.token');
        $deleted  = 0;
        $after    = null;

        $this->info("Récupération des {$objectType}...");

        do {
            $params = ['limit' => 100, 'properties' => 'id'];
            if ($after) $params['after'] = $after;

            $res = Http::withToken($token)
                ->acceptJson()
                ->get("{$this->baseUrl}/crm/v3/objects/{$objectType}", $params);

            if (!$res->successful()) {
                $this->error('Erreur API : ' . $res->body());
                return self::FAILURE;
            }

            $results = $res->json('results', []);
            $after   = $res->json('paging.next.after');

            if (empty($results)) break;

            // Archive par batch de 100
            $ids = array_map(fn($r) => ['id' => $r['id']], $results);

            $archiveRes = Http::withToken($token)
                ->acceptJson()
                ->post("{$this->baseUrl}/crm/v3/objects/{$objectType}/batch/archive", ['inputs' => $ids]);

            if ($archiveRes->successful() || $archiveRes->status() === 204) {
                $deleted += count($ids);
                $this->line("  Supprimés : {$deleted}");
            } else {
                $this->warn("  Erreur batch archive : " . $archiveRes->body());
            }

        } while ($after);

        $this->info("✅ {$deleted} {$objectType} supprimés de HubSpot.");
        return self::SUCCESS;
    }
}
