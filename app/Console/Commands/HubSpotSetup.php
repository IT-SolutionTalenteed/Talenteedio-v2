<?php

namespace App\Console\Commands;

use App\Services\HubSpotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class HubSpotSetup extends Command
{
    protected $signature   = 'hubspot:setup';
    protected $description = 'Crée les propriétés personnalisées Talenteed dans HubSpot (à lancer une seule fois)';

    private string $baseUrl = 'https://api.hubapi.com';

    // ── Définitions des propriétés à créer ────────────────────────────────────

    private array $contactProperties = [
        ['name' => 'talenteed_id',            'label' => 'Talenteed ID',              'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_role',           'label' => 'Talenteed Rôle',            'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_statut_crm',     'label' => 'Talenteed Statut CRM',      'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_disponibilite',  'label' => 'Talenteed Disponibilité',   'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_mobilite',       'label' => 'Talenteed Mobilité',        'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_source',         'label' => 'Talenteed Source',          'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_ref_crm',        'label' => 'Talenteed Réf. ancien CRM', 'type' => 'string', 'fieldType' => 'text'],
    ];

    private array $companyProperties = [
        ['name' => 'talenteed_entreprise_id', 'label' => 'Talenteed Entreprise ID', 'type' => 'string', 'fieldType' => 'text'],
    ];

    private array $dealProperties = [
        ['name' => 'talenteed_candidature_id',     'label' => 'Talenteed Candidature ID',     'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_statut_candidature', 'label' => 'Talenteed Statut Candidature', 'type' => 'string', 'fieldType' => 'text'],
    ];

    // ─────────────────────────────────────────────────────────────────────────

    public function handle(HubSpotService $hubspot): int
    {
        if (!$hubspot->isConfigured()) {
            $this->error('HUBSPOT_TOKEN non configuré dans .env');
            return self::FAILURE;
        }

        $this->info('Création des propriétés personnalisées HubSpot...');
        $this->newLine();

        $created = 0;
        $skipped = 0;
        $errors  = 0;

        foreach ([
            'contacts'  => $this->contactProperties,
            'companies' => $this->companyProperties,
            'deals'     => $this->dealProperties,
        ] as $objectType => $properties) {
            $this->line("── {$objectType} ──────────────────────────");
            foreach ($properties as $prop) {
                $result = $this->createProperty($objectType, $prop);
                if ($result === 'created') {
                    $this->line("  ✅ Créée : {$prop['name']}");
                    $created++;
                } elseif ($result === 'exists') {
                    $this->line("  ⏭  Existe déjà : {$prop['name']}");
                    $skipped++;
                } else {
                    $this->line("  ❌ Erreur : {$prop['name']} — {$result}");
                    $errors++;
                }
            }
            $this->newLine();
        }

        $this->table(
            ['Créées', 'Déjà existantes', 'Erreurs'],
            [[$created, $skipped, $errors]]
        );

        $this->info('Setup terminé. Vous pouvez maintenant lancer : php artisan hubspot:sync');

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Retourne 'created', 'exists', ou un message d'erreur.
     */
    private function createProperty(string $objectType, array $prop): string
    {
        $token = config('services.hubspot.token');

        // Vérifier si elle existe déjà
        $check = Http::withToken($token)
            ->acceptJson()
            ->get("{$this->baseUrl}/crm/v3/properties/{$objectType}/{$prop['name']}");

        if ($check->successful()) {
            return 'exists';
        }

        // Créer la propriété
        $res = Http::withToken($token)
            ->acceptJson()
            ->post("{$this->baseUrl}/crm/v3/properties/{$objectType}", [
                'name'        => $prop['name'],
                'label'       => $prop['label'],
                'type'        => $prop['type'],
                'fieldType'   => $prop['fieldType'],
                'groupName'   => $objectType === 'contacts'  ? 'contactinformation'
                              : ($objectType === 'companies' ? 'companyinformation'
                              :                                'dealinformation'),
            ]);

        if ($res->successful()) {
            return 'created';
        }

        $msg = $res->json('message') ?? $res->body();
        return $msg ?: 'Erreur inconnue';
    }
}
