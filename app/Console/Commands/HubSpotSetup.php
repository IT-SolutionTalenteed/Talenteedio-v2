<?php

namespace App\Console\Commands;

use App\Services\HubSpotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class HubSpotSetup extends Command
{
    protected $signature   = 'hubspot:setup';
    protected $description = 'Crée les groupes et propriétés personnalisées Talenteed dans HubSpot (idempotent)';

    private string $baseUrl = 'https://api.hubapi.com';

    // ── Groupes de propriétés à créer ─────────────────────────────────────────

    private array $groups = [
        'contacts' => [
            'name'        => 'talenteed',
            'displayName' => 'Talenteed',
        ],
        'companies' => [
            'name'        => 'talenteed_company',
            'displayName' => 'Talenteed',
        ],
        'deals' => [
            'name'        => 'talenteed_deal',
            'displayName' => 'Talenteed',
        ],
    ];

    // ── Définitions des propriétés à créer ────────────────────────────────────

    private array $contactProperties = [
        // Identifiants & rôle
        ['name' => 'talenteed_id',                   'label' => 'ID Talenteed',                    'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_role',                  'label' => 'Rôle',                            'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_statut_crm',            'label' => 'Statut CRM',                      'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_source',                'label' => 'Source / Provenance',             'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_ref_crm',               'label' => 'Réf. ancien CRM',                 'type' => 'string', 'fieldType' => 'text'],
        // Profil personnel
        ['name' => 'talenteed_civilite',              'label' => 'Civilité',                        'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_date_naissance',        'label' => 'Date de naissance',               'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_nationalite',           'label' => 'Nationalité',                     'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_situation_familiale',   'label' => 'Situation familiale',             'type' => 'string', 'fieldType' => 'text'],
        // Disponibilité & mobilité
        ['name' => 'talenteed_disponibilite',         'label' => 'Disponibilité',                   'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_mobilite',              'label' => 'Mobilité',                        'type' => 'string', 'fieldType' => 'text'],
        // Référentiels
        ['name' => 'talenteed_niveau_etudes',         'label' => 'Niveau d\'études',                'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_experience',            'label' => 'Expérience',                      'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_langues',               'label' => 'Langues',                         'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_secteurs',              'label' => 'Secteurs d\'activité',            'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_skills',                'label' => 'Compétences',                     'type' => 'string', 'fieldType' => 'text'],
        // Activité & compteurs
        ['name' => 'talenteed_nb_candidatures',       'label' => 'Nb candidatures',                 'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_nb_entretiens',         'label' => 'Nb entretiens',                   'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_a_entretien_confirme',  'label' => 'A entretien confirmé',            'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_dernier_entretien',     'label' => 'Dernier entretien (date)',        'type' => 'string', 'fieldType' => 'text'],
    ];

    private array $companyProperties = [
        ['name' => 'talenteed_entreprise_id', 'label' => 'ID Entreprise Talenteed', 'type' => 'string', 'fieldType' => 'text'],
    ];

    private array $dealProperties = [
        ['name' => 'talenteed_candidature_id',     'label' => 'ID Candidature',     'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_statut_candidature', 'label' => 'Statut Candidature', 'type' => 'string', 'fieldType' => 'text'],
    ];

    // ─────────────────────────────────────────────────────────────────────────

    public function handle(HubSpotService $hubspot): int
    {
        if (!$hubspot->isConfigured()) {
            $this->error('HUBSPOT_TOKEN non configuré dans .env');
            return self::FAILURE;
        }

        $this->info('=== Setup HubSpot — Groupes & Propriétés Talenteed ===');
        $this->newLine();

        $created = 0;
        $skipped = 0;
        $errors  = 0;

        $propertiesMap = [
            'contacts'  => $this->contactProperties,
            'companies' => $this->companyProperties,
            'deals'     => $this->dealProperties,
        ];

        foreach ($propertiesMap as $objectType => $properties) {
            $group = $this->groups[$objectType];

            $this->line("── {$objectType} (groupe : \"{$group['displayName']}\") ──────────────────────────");

            // 1. Créer le groupe si inexistant
            $groupResult = $this->ensureGroup($objectType, $group);
            if ($groupResult === 'created') {
                $this->line("  ✅ Groupe créé : {$group['name']}");
            } elseif ($groupResult === 'exists') {
                $this->line("  ⏭  Groupe existe déjà : {$group['name']}");
            } else {
                $this->warn("  ⚠️  Groupe {$group['name']} : {$groupResult}");
            }

            // 2. Créer chaque propriété dans ce groupe
            foreach ($properties as $prop) {
                $result = $this->createProperty($objectType, $prop, $group['name']);
                if ($result === 'created') {
                    $this->line("  ✅ {$prop['label']} ({$prop['name']})");
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

        if ($errors === 0) {
            $this->info('✅ Setup terminé. Dans HubSpot, une section "Talenteed" apparaît maintenant sur chaque fiche contact/company/deal.');
            $this->info('   Lancez maintenant : php artisan hubspot:sync --contacts --limit=350');
        } else {
            $this->warn('Setup terminé avec des erreurs. Vérifiez les scopes de votre Private App HubSpot.');
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Crée le groupe de propriétés s'il n'existe pas encore.
     * Retourne 'created', 'exists', ou un message d'erreur.
     */
    private function ensureGroup(string $objectType, array $group): string
    {
        $token = config('services.hubspot.token');

        $check = Http::withToken($token)
            ->acceptJson()
            ->get("{$this->baseUrl}/crm/v3/properties/{$objectType}/groups/{$group['name']}");

        if ($check->successful()) {
            return 'exists';
        }

        $res = Http::withToken($token)
            ->acceptJson()
            ->post("{$this->baseUrl}/crm/v3/properties/{$objectType}/groups", [
                'name'        => $group['name'],
                'displayName' => $group['displayName'],
            ]);

        if ($res->successful()) {
            return 'created';
        }

        return $res->json('message') ?? $res->body() ?? 'Erreur inconnue';
    }

    /**
     * Crée une propriété dans le groupe donné.
     * Retourne 'created', 'exists', ou un message d'erreur.
     */
    private function createProperty(string $objectType, array $prop, string $groupName): string
    {
        $token = config('services.hubspot.token');

        $check = Http::withToken($token)
            ->acceptJson()
            ->get("{$this->baseUrl}/crm/v3/properties/{$objectType}/{$prop['name']}");

        if ($check->successful()) {
            // Propriété existe — mettre à jour le groupName si besoin
            $existing = $check->json('groupName');
            if ($existing !== $groupName) {
                Http::withToken($token)
                    ->acceptJson()
                    ->patch("{$this->baseUrl}/crm/v3/properties/{$objectType}/{$prop['name']}", [
                        'groupName' => $groupName,
                        'label'     => $prop['label'],
                    ]);
            }
            return 'exists';
        }

        $res = Http::withToken($token)
            ->acceptJson()
            ->post("{$this->baseUrl}/crm/v3/properties/{$objectType}", [
                'name'      => $prop['name'],
                'label'     => $prop['label'],
                'type'      => $prop['type'],
                'fieldType' => $prop['fieldType'],
                'groupName' => $groupName,
            ]);

        if ($res->successful()) {
            return 'created';
        }

        return $res->json('message') ?? $res->body() ?? 'Erreur inconnue';
    }
}
