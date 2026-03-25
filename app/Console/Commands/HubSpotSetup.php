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
        // Identifiants & rôle
        ['name' => 'talenteed_id',                   'label' => 'Talenteed ID',                    'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_role',                  'label' => 'Talenteed Rôle',                  'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_statut_crm',            'label' => 'Talenteed Statut CRM',            'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_source',                'label' => 'Talenteed Source / Provenance',   'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_ref_crm',               'label' => 'Talenteed Réf. ancien CRM',       'type' => 'string', 'fieldType' => 'text'],
        // Profil personnel
        ['name' => 'talenteed_civilite',              'label' => 'Talenteed Civilité',              'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_date_naissance',        'label' => 'Talenteed Date de naissance',     'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_nationalite',           'label' => 'Talenteed Nationalité',           'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_situation_familiale',   'label' => 'Talenteed Situation familiale',   'type' => 'string', 'fieldType' => 'text'],
        // Disponibilité & mobilité
        ['name' => 'talenteed_disponibilite',         'label' => 'Talenteed Disponibilité',         'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_mobilite',              'label' => 'Talenteed Mobilité',              'type' => 'string', 'fieldType' => 'text'],
        // Référentiels
        ['name' => 'talenteed_niveau_etudes',         'label' => 'Talenteed Niveau d\'études',      'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_experience',            'label' => 'Talenteed Expérience',            'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_langues',               'label' => 'Talenteed Langues',               'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_secteurs',              'label' => 'Talenteed Secteurs d\'activité',  'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_skills',                'label' => 'Talenteed Compétences',           'type' => 'string', 'fieldType' => 'text'],
        // Activité & compteurs
        ['name' => 'talenteed_nb_candidatures',       'label' => 'Talenteed Nb candidatures',       'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_nb_entretiens',         'label' => 'Talenteed Nb entretiens',         'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_a_entretien_confirme',  'label' => 'Talenteed A entretien confirmé',  'type' => 'string', 'fieldType' => 'text'],
        ['name' => 'talenteed_dernier_entretien',     'label' => 'Talenteed Dernier entretien',     'type' => 'string', 'fieldType' => 'text'],
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
