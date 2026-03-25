<?php

namespace App\Services;

use App\Models\Candidature;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\PendingRequest;

class HubSpotService
{
    private const BASE = 'https://api.hubapi.com';

    // ─── HTTP client ───────────────────────────────────────────────────────────

    private function http(): PendingRequest
    {
        return Http::withToken(config('services.hubspot.token'))
            ->baseUrl(self::BASE)
            ->acceptJson()
            ->timeout(15);
    }

    /** Retourne true si le token est configuré */
    public function isConfigured(): bool
    {
        return !empty(config('services.hubspot.token'))
            && config('services.hubspot.token') !== 'REMPLACER_PAR_VOTRE_PRIVATE_APP_TOKEN';
    }

    // ─── Contacts (Talents) ────────────────────────────────────────────────────

    /**
     * Crée ou met à jour un contact HubSpot depuis un User Talenteed.
     * Retourne l'ID HubSpot du contact, ou null en cas d'erreur.
     */
    public function upsertContact(User $user): ?int
    {
        if (!$this->isConfigured()) return null;

        try {
            // Charger les relations nécessaires si pas déjà chargées
            $user->loadMissing(['studyLevel', 'experience', 'languages', 'activitySectors', 'skills']);

            $nameParts = explode(' ', $user->name, 2);

            // Comptes calculés
            $nbCandidatures = $user->candidatures()->count();
            $nbEntretiens   = $user->entretiens()->count();
            $aEntretienConfirme = $user->entretiens()->where('statut', 'confirme')->exists();
            $dernierEntretien   = $user->entretiens()
                ->where('statut', 'confirme')
                ->orderByDesc('date')
                ->value('date');

            $props = array_filter([
                // Champs HubSpot natifs
                'email'                    => $user->email,
                'firstname'                => $nameParts[0] ?? '',
                'lastname'                 => $nameParts[1] ?? '',
                'phone'                    => $user->telephone,
                'city'                     => $user->ville,
                'country'                  => $user->pays,
                'jobtitle'                 => $user->titre_poste,

                // Identifiants Talenteed
                'talenteed_id'             => (string) $user->id,
                'talenteed_role'           => $user->role,
                'talenteed_statut_crm'     => $user->statut_crm ?? '',
                'talenteed_source'         => $user->source_provenance ?? '',
                'talenteed_ref_crm'        => $user->ref_ancien_crm ?? '',

                // Profil étendu (K-02)
                'talenteed_civilite'           => $user->civilite ?? '',
                'talenteed_date_naissance'     => $user->date_naissance?->format('Y-m-d') ?? '',
                'talenteed_nationalite'        => $user->nationalite ?? '',
                'talenteed_situation_familiale' => $user->situation_familiale ?? '',
                'talenteed_disponibilite'      => $user->disponibilite ?? '',
                'talenteed_mobilite'           => $user->mobilite ?? '',

                // Relations référentielles
                'talenteed_niveau_etudes'  => $user->studyLevel?->name ?? '',
                'talenteed_experience'     => $user->experience?->name ?? '',
                'talenteed_langues'        => $user->languages->pluck('name')->implode(', '),
                'talenteed_secteurs'       => $user->activitySectors->pluck('name')->implode(', '),
                'talenteed_skills'         => $user->skills->pluck('name')->implode(', '),

                // Compteurs & activité
                'talenteed_nb_candidatures'      => (string) $nbCandidatures,
                'talenteed_nb_entretiens'        => (string) $nbEntretiens,
                'talenteed_a_entretien_confirme' => $aEntretienConfirme ? 'true' : 'false',
                'talenteed_dernier_entretien'    => $dernierEntretien ? (string) $dernierEntretien : '',
            ], fn($v) => $v !== null && $v !== '');

            $existingId = $this->searchContactByEmail($user->email);

            if ($existingId) {
                $this->http()->patch("/crm/v3/objects/contacts/{$existingId}", ['properties' => $props]);
                return $existingId;
            }

            $res = $this->http()->post('/crm/v3/objects/contacts', ['properties' => $props]);
            return $res->successful() ? (int) $res->json('id') : null;

        } catch (\Exception $e) {
            Log::error('[HubSpot] upsertContact failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function searchContactByEmail(string $email): ?int
    {
        $res = $this->http()->post('/crm/v3/objects/contacts/search', [
            'filterGroups' => [[
                'filters' => [[
                    'propertyName' => 'email',
                    'operator'     => 'EQ',
                    'value'        => $email,
                ]],
            ]],
            'limit' => 1,
        ]);

        $results = $res->json('results', []);
        return !empty($results) ? (int) $results[0]['id'] : null;
    }

    // ─── Companies (Entreprises) ───────────────────────────────────────────────

    /**
     * Crée ou met à jour une company HubSpot depuis une Entreprise Talenteed.
     */
    public function upsertCompany(Entreprise $entreprise): ?int
    {
        if (!$this->isConfigured()) return null;

        try {
            $props = array_filter([
                'name'                        => $entreprise->nom,
                'phone'                       => $entreprise->telephone,
                'address'                     => $entreprise->adresse,
                'city'                        => $entreprise->ville,
                'country'                     => $entreprise->pays,
                'website'                     => $entreprise->site_web,
                'description'                 => $entreprise->description,
                'talenteed_entreprise_id'     => (string) $entreprise->id,
            ], fn($v) => $v !== null && $v !== '');

            // Utiliser l'ID HubSpot stocké en base si disponible (évite la recherche par nom)
            $hubspotId = $entreprise->hubspot_company_id;

            if ($hubspotId) {
                $this->http()->patch("/crm/v3/objects/companies/{$hubspotId}", ['properties' => $props]);
                return $hubspotId;
            }

            // Première sync : chercher par nom puis stocker l'ID
            $existingId = $this->searchCompanyByName($entreprise->nom);

            if ($existingId) {
                $this->http()->patch("/crm/v3/objects/companies/{$existingId}", ['properties' => $props]);
                $entreprise->updateQuietly(['hubspot_company_id' => $existingId]);
                return $existingId;
            }

            $res = $this->http()->post('/crm/v3/objects/companies', ['properties' => $props]);
            $newId = $res->successful() ? (int) $res->json('id') : null;

            if ($newId) {
                $entreprise->updateQuietly(['hubspot_company_id' => $newId]);
            }

            return $newId;

        } catch (\Exception $e) {
            Log::error('[HubSpot] upsertCompany failed', ['entreprise_id' => $entreprise->id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function searchCompanyByName(string $name): ?int
    {
        $res = $this->http()->post('/crm/v3/objects/companies/search', [
            'filterGroups' => [[
                'filters' => [[
                    'propertyName' => 'name',
                    'operator'     => 'EQ',
                    'value'        => $name,
                ]],
            ]],
            'limit' => 1,
        ]);

        $results = $res->json('results', []);
        return !empty($results) ? (int) $results[0]['id'] : null;
    }

    // ─── Deals (Candidatures) ─────────────────────────────────────────────────

    /**
     * Crée un Deal HubSpot pour une candidature.
     */
    public function createDeal(Candidature $candidature): ?int
    {
        if (!$this->isConfigured()) return null;

        try {
            $talent = $candidature->talent;
            $offre  = $candidature->offre;

            $dealName = sprintf(
                'Candidature #%d — %s (%s)',
                $candidature->id,
                $offre?->titre ?? 'Offre',
                $talent?->name ?? 'Talent'
            );

            $res = $this->http()->post('/crm/v3/objects/deals', [
                'properties' => [
                    'dealname'                    => $dealName,
                    'dealstage'                   => 'appointmentscheduled',
                    'pipeline'                    => 'default',
                    'talenteed_candidature_id'    => (string) $candidature->id,
                    'talenteed_statut_candidature' => $candidature->statut ?? 'en_attente',
                ],
            ]);

            $dealId = $res->successful() ? (int) $res->json('id') : null;

            // Associer le deal au contact (talent)
            if ($dealId && $talent) {
                $contactId = $this->searchContactByEmail($talent->email);
                if ($contactId) {
                    $this->http()->put(
                        "/crm/v3/objects/deals/{$dealId}/associations/contacts/{$contactId}/3",
                        []
                    );
                }
            }

            return $dealId;

        } catch (\Exception $e) {
            Log::error('[HubSpot] createDeal failed', ['candidature_id' => $candidature->id, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
