<?php

namespace Database\Seeders;

use App\Models\Entreprise;
use App\Models\Experience;
use App\Models\JobContract;
use App\Models\JobMode;
use App\Models\Offre;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class ImportJobsFromLegacySeeder extends Seeder
{
    /**
     * Importe les offres d'emploi depuis l'export JSON de l'ancien projet.
     *
     * Placer le fichier JSON ici : database/data/jobs.json
     */

    // jobType names that map to JobContract vs JobMode
    private const CONTRACT_TYPES = ['Full Time', 'Part Time', 'Freelance', 'Internship', 'Temporary', 'Contract', 'CDI', 'CDD'];
    private const MODE_TYPES     = ['Hybrid', 'On-Site', 'Remote', 'Télétravail'];

    public function run(): void
    {
        $jsonPath = database_path('data/jobs.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("Fichier introuvable : {$jsonPath}");
            $this->command->info("Placez votre export JSON dans database/data/jobs.json");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['jobs'])) {
            $this->command->error('JSON invalide ou clé "jobs" manquante.');
            return;
        }

        $jobs = $data['jobs'];
        $this->command->info("Import de {$data['total']} offres...");

        $imported   = 0;
        $skipped    = 0;
        $noCompany  = 0;

        foreach ($jobs as $raw) {
            // Skip si déjà importé (idempotent par titre)
            $titre = $raw['title'] ?? '';
            if (empty($titre) || Offre::where('titre', $titre)->exists()) {
                $skipped++;
                continue;
            }

            // Résolution entreprise_id par nom
            $entrepriseId = null;
            if (!empty($raw['company']['name'])) {
                $entreprise = Entreprise::where('nom', $raw['company']['name'])->first();
                if ($entreprise) {
                    $entrepriseId = $entreprise->id;
                } else {
                    $noCompany++;
                }
            }

            // Fourchette salariale
            $fourchette = null;
            if (!empty($raw['salaryMin']) || !empty($raw['salaryMax'])) {
                $min = $raw['salaryMin'] ?? '';
                $max = $raw['salaryMax'] ?? '';
                $type = !empty($raw['salaryType']) ? ' (' . $raw['salaryType'] . ')' : '';
                $fourchette = trim("{$min} - {$max}{$type}", ' -');
            }

            $offre = Offre::create([
                'titre'              => $titre,
                'mission'            => $raw['content'] ?? null,
                'description'        => $raw['metaDescription'] ?? null,
                'localisation'       => $raw['location']['name'] ?? null,
                'date_limite'        => !empty($raw['expirationDate']) ? $raw['expirationDate'] : null,
                'fourchette_salariale' => $fourchette,
                'entreprise_id'      => $entrepriseId,
            ]);

            // jobType → JobContract ou JobMode selon le type
            if (!empty($raw['jobType']['name'])) {
                $typeName = $raw['jobType']['name'];
                if (in_array($typeName, self::CONTRACT_TYPES)) {
                    $contract = JobContract::firstOrCreate(['name' => $typeName]);
                    $offre->jobContracts()->sync([$contract->id]);
                } elseif (in_array($typeName, self::MODE_TYPES)) {
                    $mode = JobMode::firstOrCreate(['name' => $typeName]);
                    $offre->jobModes()->sync([$mode->id]);
                } else {
                    // Inconnu → on le met en JobContract par défaut
                    $contract = JobContract::firstOrCreate(['name' => $typeName]);
                    $offre->jobContracts()->sync([$contract->id]);
                }
            }

            // Expérience (find or create par nom)
            if (!empty($raw['experience'])) {
                $experience = Experience::firstOrCreate(['name' => $raw['experience']]);
                $offre->experiences()->sync([$experience->id]);
            }

            // Skills (find or create par nom)
            if (!empty($raw['skills'])) {
                $skillIds = [];
                foreach ($raw['skills'] as $s) {
                    if (!empty($s['name'])) {
                        $skill = Skill::firstOrCreate(['name' => $s['name']]);
                        $skillIds[] = $skill->id;
                    }
                }
                if ($skillIds) {
                    $offre->skills()->sync($skillIds);
                }
            }

            $imported++;
        }

        $this->command->info("✓ {$imported} offres importées.");
        if ($skipped > 0) {
            $this->command->warn("⚠ {$skipped} offres ignorées (titre déjà existant ou vide).");
        }
        if ($noCompany > 0) {
            $this->command->warn("⚠ {$noCompany} offres sans entreprise associée (nom introuvable en base).");
        }
    }
}
