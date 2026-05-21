<?php

namespace App\Console\Commands;

use App\Models\ActivitySector;
use App\Models\Entreprise;
use App\Models\Offre;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DeduplicateActivitySectors extends Command
{
    protected $signature = 'activity-sectors:deduplicate
                            {--dry-run : Afficher les actions sans modifier la base}';

    protected $description = 'Fusionne les secteurs en doublon en conservant ceux créés via l\'admin';

    /** @var list<list<string>> */
    private const DUPLICATE_GROUPS = [
        ['Banque', 'Banque / Finance'],
        ['Conseil', 'Consulting / Conseil'],
        ['Energie', 'Énergie / Mines'],
        ['Logistique', 'Transport / Logistique'],
        ['Autres', 'Autre'],
        ['Grande distribution', 'Commerce / Distribution'],
        ['Édition de logiciels', 'Informatique', 'Tech', 'IT / Programming', 'Technologie / IT'],
        ['Bâtiments', 'BTP / Immobilier'],
        ['Pharmacie', 'Santé'],
    ];

    private const LEGACY_SEEDER_NAMES = [
        'Aéronautique', 'Aérospatial', 'Assurance', 'Automobile', 'Autres', 'Banque', 'Bâtiments',
        'Conseil', 'Défense', 'Édition de logiciels', 'Energie', 'Environnement', 'Grande distribution',
        'Infrastructure', 'Logistique', 'Pharmacie', 'Secteur public', 'Services', 'Télécommunications',
    ];

    private const ATS_SEEDER_NAMES = [
        'Banque / Finance', 'Assurance', 'Technologie / IT', 'Santé', 'Industrie',
        'Commerce / Distribution', 'BTP / Immobilier', 'Énergie / Mines', 'Agriculture / Agroalimentaire',
        'Télécommunications', 'Consulting / Conseil', 'Éducation / Formation', 'Transport / Logistique', 'Autre',
    ];

    /** Horodatages des imports / seeders en masse (pas admin). */
    private const BULK_PREFIXES = [
        '2026-03-25 01:25',
        '2026-03-26 01:12',
        '2026-04-16 20:08',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode dry-run — aucune modification.');
        }

        $this->backfillOrigins($dryRun);

        $deleted = 0;

        foreach (self::DUPLICATE_GROUPS as $names) {
            $sectors = ActivitySector::whereIn('name', $names)->orderBy('id')->get();

            if ($sectors->count() < 2) {
                continue;
            }

            $keeper = $this->pickKeeper($sectors);
            $toRemove = $sectors->where('id', '!=', $keeper->id);

            $this->line('');
            $this->info("Groupe : " . implode(' · ', $names));
            $this->line("  → Conserver : [{$keeper->id}] {$keeper->name} ({$keeper->origin})");

            foreach ($toRemove as $sector) {
                $stats = $this->usageStats($sector->id);
                $this->line("  → Supprimer : [{$sector->id}] {$sector->name} ({$sector->origin}) — {$stats}");

                if ($dryRun) {
                    continue;
                }

                $this->reassignReferences($sector->id, $keeper->id);
                $sector->delete();
                $deleted++;
            }
        }

        $this->newLine();
        $this->info($dryRun
            ? 'Dry-run terminé. Relancez sans --dry-run pour appliquer.'
            : "Terminé. {$deleted} secteur(s) en doublon supprimé(s).");

        return self::SUCCESS;
    }

    private function backfillOrigins(bool $dryRun): void
    {
        $sectors = ActivitySector::all();

        foreach ($sectors as $sector) {
            $origin = $this->detectOrigin($sector);

            if ($sector->origin === $origin) {
                continue;
            }

            if (!$dryRun) {
                $sector->update(['origin' => $origin]);
            }
        }

        $this->info('Origines détectées : admin, ats_seeder, legacy_seeder, import.');
    }

    private function detectOrigin(ActivitySector $sector): string
    {
        if ($sector->origin === 'admin') {
            return 'admin';
        }

        if ($this->isAdminCreated($sector)) {
            return 'admin';
        }

        if (in_array($sector->name, self::ATS_SEEDER_NAMES, true)) {
            return 'ats_seeder';
        }

        if (in_array($sector->name, self::LEGACY_SEEDER_NAMES, true)) {
            return 'legacy_seeder';
        }

        return 'import';
    }

    private function isAdminCreated(ActivitySector $sector): bool
    {
        if ($sector->origin === 'admin') {
            return true;
        }

        $created = $sector->created_at instanceof Carbon
            ? $sector->created_at->format('Y-m-d H:i')
            : Carbon::parse($sector->created_at)->format('Y-m-d H:i');

        foreach (self::BULK_PREFIXES as $prefix) {
            if (str_starts_with($created, $prefix)) {
                return false;
            }
        }

        return true;
    }

    private function pickKeeper($sectors): ActivitySector
    {
        $admin = $sectors->first(fn (ActivitySector $s) => $this->isAdminCreated($s));
        if ($admin) {
            return $admin;
        }

        $ats = $sectors->first(fn (ActivitySector $s) => in_array($s->name, self::ATS_SEEDER_NAMES, true));
        if ($ats) {
            return $ats;
        }

        return $sectors->sortByDesc(fn (ActivitySector $s) => $this->usageCount($s->id))->first();
    }

    private function usageCount(int $sectorId): int
    {
        return Entreprise::where('activity_sector_id', $sectorId)->count()
            + User::where('secteur_souhaite_id', $sectorId)->count()
            + Offre::where('activity_sector_id', $sectorId)->count()
            + DB::table('user_activity_sector')->where('activity_sector_id', $sectorId)->count();
    }

    private function usageStats(int $sectorId): string
    {
        return sprintf(
            'ent:%d usr:%d off:%d pivot:%d',
            Entreprise::where('activity_sector_id', $sectorId)->count(),
            User::where('secteur_souhaite_id', $sectorId)->count(),
            Offre::where('activity_sector_id', $sectorId)->count(),
            DB::table('user_activity_sector')->where('activity_sector_id', $sectorId)->count(),
        );
    }

    private function reassignReferences(int $fromId, int $toId): void
    {
        Entreprise::where('activity_sector_id', $fromId)->update(['activity_sector_id' => $toId]);
        User::where('secteur_souhaite_id', $fromId)->update(['secteur_souhaite_id' => $toId]);
        Offre::where('activity_sector_id', $fromId)->update(['activity_sector_id' => $toId]);

        $pivotRows = DB::table('user_activity_sector')->where('activity_sector_id', $fromId)->get();

        foreach ($pivotRows as $row) {
            $exists = DB::table('user_activity_sector')
                ->where('user_id', $row->user_id)
                ->where('activity_sector_id', $toId)
                ->exists();

            if ($exists) {
                DB::table('user_activity_sector')
                    ->where('user_id', $row->user_id)
                    ->where('activity_sector_id', $fromId)
                    ->delete();
            } else {
                DB::table('user_activity_sector')
                    ->where('user_id', $row->user_id)
                    ->where('activity_sector_id', $fromId)
                    ->update(['activity_sector_id' => $toId]);
            }
        }
    }
}
