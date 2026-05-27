<?php

namespace App\Services;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\PendingRequest;

class BrevoService
{
    private const BASE = 'https://api.brevo.com/v3';

    private function http(): PendingRequest
    {
        return Http::withHeaders(['api-key' => config('services.brevo.api_key')])
            ->baseUrl(self::BASE)
            ->acceptJson()
            ->timeout(15);
    }

    public function isConfigured(): bool
    {
        return !empty(config('services.brevo.api_key'));
    }

    // ─── Contacts (Talents) ────────────────────────────────────────────────────

    public function upsertContact(User $user): ?int
    {
        if (!$this->isConfigured()) return null;

        try {
            $user->loadMissing(['studyLevel', 'experience', 'languages', 'activitySectors', 'skills']);

            $nameParts = explode(' ', $user->name, 2);

            $nbCandidatures     = $user->candidatures()->count();
            $nbEntretiens       = $user->entretiens()->count();
            $dernierEntretien   = $user->entretiens()
                ->where('statut', 'confirme')
                ->orderByDesc('date')
                ->value('date');

            $attributes = array_filter([
                'PRENOM'                        => $nameParts[0] ?? '',
                'NOM'                           => $nameParts[1] ?? '',
                'SMS'                           => $user->telephone ?? '',
                'VILLE'                         => $user->ville ?? '',
                'PAYS'                          => $user->pays ?? '',
                'TITRE_POSTE'                   => $user->titre_poste ?? '',
                'TALENTEED_ID'                  => (string) $user->id,
                'TALENTEED_ROLE'                => $user->role,
                'TALENTEED_STATUT_CRM'          => $user->statut_crm ?? '',
                'TALENTEED_SOURCE'              => $user->source_provenance ?? '',
                'TALENTEED_CIVILITE'            => $user->civilite ?? '',
                'TALENTEED_DATE_NAISSANCE'      => $user->date_naissance?->format('Y-m-d') ?? '',
                'TALENTEED_NATIONALITE'         => $user->nationalite ?? '',
                'TALENTEED_DISPONIBILITE'       => $user->disponibilite ?? '',
                'TALENTEED_MOBILITE'            => $user->mobilite ?? '',
                'TALENTEED_NIVEAU_ETUDES'       => $user->studyLevel?->name ?? '',
                'TALENTEED_EXPERIENCE'          => $user->experience?->name ?? '',
                'TALENTEED_LANGUES'             => $user->languages->pluck('name')->implode(', '),
                'TALENTEED_SECTEURS'            => $user->activitySectors->pluck('name')->implode(', '),
                'TALENTEED_SKILLS'              => $user->skills->pluck('name')->implode(', '),
                'TALENTEED_NB_CANDIDATURES'     => (string) $nbCandidatures,
                'TALENTEED_NB_ENTRETIENS'       => (string) $nbEntretiens,
                'TALENTEED_DERNIER_ENTRETIEN'   => $dernierEntretien ? (string) $dernierEntretien : '',
            ], fn($v) => $v !== null && $v !== '');

            $res = $this->http()->post('/contacts', [
                'email'          => $user->email,
                'attributes'     => $attributes,
                'updateEnabled'  => true,
            ]);

            if ($res->successful()) {
                $brevoId = $res->json('id') ?? $this->getContactIdByEmail($user->email);
                $user->updateQuietly([
                    'brevo_id'         => $brevoId,
                    'brevo_synced_at'  => now(),
                    'brevo_sync_error' => null,
                ]);
                return $brevoId;
            }

            $errorMsg = $res->json('message') ?? $res->body();
            $user->updateQuietly(['brevo_sync_error' => $errorMsg]);
            Log::error('[Brevo] upsertContact failed', ['user_id' => $user->id, 'error' => $errorMsg]);
            return null;

        } catch (\Exception $e) {
            $user->updateQuietly(['brevo_sync_error' => $e->getMessage()]);
            Log::error('[Brevo] upsertContact exception', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    // ─── Contacts (Entreprises) ────────────────────────────────────────────────

    public function upsertEntreprise(Entreprise $entreprise): ?int
    {
        if (!$this->isConfigured()) return null;

        try {
            $attributes = array_filter([
                'NOM'            => $entreprise->nom ?? '',
                'SMS'            => $entreprise->telephone ?? '',
                'VILLE'          => $entreprise->ville ?? '',
                'TALENTEED_ID'   => (string) $entreprise->id,
                'TALENTEED_ROLE' => 'entreprise',
            ], fn($v) => $v !== null && $v !== '');

            $email = $entreprise->email ?? $entreprise->user?->email;
            if (!$email) {
                Log::warning('[Brevo] upsertEntreprise skipped — no email', ['entreprise_id' => $entreprise->id]);
                return null;
            }

            $res = $this->http()->post('/contacts', [
                'email'         => $email,
                'attributes'    => $attributes,
                'updateEnabled' => true,
            ]);

            if ($res->successful()) {
                $brevoId = $res->json('id') ?? $this->getContactIdByEmail($email);
                $entreprise->updateQuietly([
                    'brevo_id'         => $brevoId,
                    'brevo_synced_at'  => now(),
                    'brevo_sync_error' => null,
                ]);
                return $brevoId;
            }

            $errorMsg = $res->json('message') ?? $res->body();
            $entreprise->updateQuietly(['brevo_sync_error' => $errorMsg]);
            Log::error('[Brevo] upsertEntreprise failed', ['entreprise_id' => $entreprise->id, 'error' => $errorMsg]);
            return null;

        } catch (\Exception $e) {
            $entreprise->updateQuietly(['brevo_sync_error' => $e->getMessage()]);
            Log::error('[Brevo] upsertEntreprise exception', ['entreprise_id' => $entreprise->id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    // ─── Sync batch ────────────────────────────────────────────────────────────

    public function syncAll(): array
    {
        $stats = ['contacts' => 0, 'entreprises' => 0, 'errors' => 0];

        \App\Models\User::whereIn('role', ['talent', 'consultant_externe'])
            ->cursor()
            ->each(function (User $user) use (&$stats) {
                $this->upsertContact($user) ? $stats['contacts']++ : $stats['errors']++;
            });

        \App\Models\Entreprise::cursor()
            ->each(function (Entreprise $e) use (&$stats) {
                $this->upsertEntreprise($e) ? $stats['entreprises']++ : $stats['errors']++;
            });

        return $stats;
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function getContactIdByEmail(string $email): ?int
    {
        $res = $this->http()->get('/contacts/' . urlencode($email));
        return $res->successful() ? (int) $res->json('id') : null;
    }
}
