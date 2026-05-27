<?php

namespace App\Console\Commands;

use App\Services\BrevoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BrevoSetup extends Command
{
    protected $signature   = 'brevo:setup';
    protected $description = 'Vérifie la connexion Brevo et crée les attributs de contact personnalisés (idempotent)';

    private array $attributes = [
        ['name' => 'TALENTEED_ID',                'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_ROLE',              'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_STATUT_CRM',        'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_SOURCE',            'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_CIVILITE',          'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_DATE_NAISSANCE',    'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_NATIONALITE',       'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_DISPONIBILITE',     'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_MOBILITE',          'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_NIVEAU_ETUDES',     'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_EXPERIENCE',        'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_LANGUES',           'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_SECTEURS',          'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_SKILLS',            'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_NB_CANDIDATURES',   'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_NB_ENTRETIENS',     'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_DERNIER_ENTRETIEN', 'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_SITUATION_FAMILIALE', 'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_REF_CRM',           'category' => 'normal', 'type' => 'text'],
        ['name' => 'TALENTEED_A_ENTRETIEN_CONFIRME', 'category' => 'normal', 'type' => 'text'],
        ['name' => 'TITRE_POSTE',                 'category' => 'normal', 'type' => 'text'],
        ['name' => 'VILLE',                       'category' => 'normal', 'type' => 'text'],
        ['name' => 'PAYS',                        'category' => 'normal', 'type' => 'text'],
    ];

    public function handle(BrevoService $brevo): int
    {
        if (!$brevo->isConfigured()) {
            $this->error('BREVO_API_KEY non configuré dans .env');
            return self::FAILURE;
        }

        $this->info('=== Setup Brevo — Vérification connexion & attributs ===');
        $this->newLine();

        // Test de connexion
        $res = Http::withHeaders(['api-key' => config('services.brevo.api_key')])
            ->acceptJson()
            ->get('https://api.brevo.com/v3/account');

        if (!$res->successful()) {
            $this->error('Connexion Brevo échouée : ' . $res->body());
            return self::FAILURE;
        }

        $account = $res->json('companyName') ?? $res->json('email');
        $this->info("✅ Connecté à Brevo — compte : {$account}");
        $this->newLine();

        // Récupérer les attributs existants
        $existing = Http::withHeaders(['api-key' => config('services.brevo.api_key')])
            ->acceptJson()
            ->get('https://api.brevo.com/v3/contacts/attributes');

        $existingNames = collect($existing->json('attributes', []))->pluck('name')->toArray();

        $created = 0;
        $skipped = 0;
        $errors  = 0;

        foreach ($this->attributes as $attr) {
            if (in_array($attr['name'], $existingNames)) {
                $this->line("  ⏭  Existe déjà : {$attr['name']}");
                $skipped++;
                continue;
            }

            $res = Http::withHeaders(['api-key' => config('services.brevo.api_key')])
                ->acceptJson()
                ->post("https://api.brevo.com/v3/contacts/attributes/normal/{$attr['name']}", [
                    'type' => $attr['type'],
                ]);

            if ($res->successful() || $res->status() === 201) {
                $this->line("  ✅ Créé : {$attr['name']}");
                $created++;
            } else {
                $this->line("  ❌ Erreur : {$attr['name']} — " . ($res->json('message') ?? $res->body()));
                $errors++;
            }
        }

        $this->newLine();
        $this->table(['Créés', 'Déjà existants', 'Erreurs'], [[$created, $skipped, $errors]]);

        if ($errors === 0) {
            $this->info('✅ Setup terminé. Lancez maintenant : php artisan brevo:sync');
        } else {
            $this->warn('Setup terminé avec des erreurs — vérifiez votre clé API Brevo.');
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
