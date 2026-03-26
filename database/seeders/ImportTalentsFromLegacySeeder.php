<?php

namespace Database\Seeders;

use App\Models\ActivitySector;
use App\Models\Experience;
use App\Models\Language;
use App\Models\Skill;
use App\Models\StudyLevel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportTalentsFromLegacySeeder extends Seeder
{
    /**
     * Importe les talents depuis l'export JSON de l'ancien projet.
     *
     * Placer le fichier JSON ici : database/data/talents.json
     *
     * Les mots de passe $2b$ (Node/bcrypt) sont convertis en $2y$ (PHP/bcrypt) :
     * les deux sont algorithmiquement identiques, les utilisateurs gardent leur mot de passe.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/talents.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("Fichier introuvable : {$jsonPath}");
            $this->command->info("Placez votre export JSON dans database/data/talents.json");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['talents'])) {
            $this->command->error('JSON invalide ou clé "talents" manquante.');
            return;
        }

        $talents  = $data['talents'];
        $this->command->info("Import de {$data['total']} talents...");

        $imported = 0;
        $skipped  = 0;

        foreach ($talents as $raw) {
            $userData = $raw['user'] ?? null;
            $email    = $userData['email'] ?? null;

            if (empty($email)) {
                $skipped++;
                continue;
            }

            // Idempotent par email
            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            // Mot de passe : $2b$ (Node) → $2y$ (PHP) — même algorithme, préfixe différent
            $rawHash  = $userData['password'] ?? '';
            $password = str_replace('$2b$', '$2y$', $rawHash);

            // Nom complet
            $firstname = trim($userData['firstname'] ?? '');
            $lastname  = trim($userData['lastname'] ?? '');
            $name      = trim("{$firstname} {$lastname}") ?: $email;

            // Civilité
            $civilite = match (strtolower($raw['gender'] ?? '')) {
                'male'   => 'M.',
                'female' => 'Mme',
                default  => null,
            };

            // Disponibilité (date ISO → Y-m-d)
            $disponibilite = null;
            if (!empty($raw['availabilityDate'])) {
                try {
                    $disponibilite = Carbon::parse($raw['availabilityDate'])->format('Y-m-d');
                } catch (\Exception) {
                    $disponibilite = null;
                }
            }

            // study_level_id
            $studyLevelId = null;
            if (!empty($raw['educationLevel'])) {
                $studyLevelId = StudyLevel::firstOrCreate(['name' => $raw['educationLevel']])->id;
            }

            // experience_id
            $experienceId = null;
            if (!empty($raw['experience'])) {
                $experienceId = Experience::firstOrCreate(['name' => $raw['experience']])->id;
            }

            // Insertion directe via query builder pour contourner le cast 'hashed' du modèle
            $createdAt = !empty($userData['createdAt'])
                ? Carbon::parse($userData['createdAt'])
                : now();

            $userId = DB::table('users')->insertGetId([
                'name'             => $name,
                'email'            => $email,
                'password'         => $password,
                'role'             => 'talent',
                'civilite'         => $civilite,
                'titre_poste'      => $raw['title'] ?? null,
                'telephone'        => $raw['contact']['phone'] ?? null,
                'nationalite'      => $raw['country'] ?? null,
                'ville'            => $raw['city'] ?? null,
                'pays'             => $raw['country'] ?? null,
                'disponibilite'    => $disponibilite,
                'mobilite'         => $raw['mobility'] ?? null,
                'source_provenance'=> 'legacy_import',
                'ref_ancien_crm'   => $raw['id'] ?? null,
                'study_level_id'   => $studyLevelId,
                'experience_id'    => $experienceId,
                'created_at'       => $createdAt,
                'updated_at'       => now(),
            ]);

            // Charger le modèle pour les pivots
            $user = User::find($userId);

            // Skills (tableau d'objets [{name: ...}])
            if (!empty($raw['skills'])) {
                $skillIds = [];
                foreach ($raw['skills'] as $s) {
                    if (!empty($s['name'])) {
                        $skillIds[] = Skill::firstOrCreate(['name' => $s['name']])->id;
                    }
                }
                if ($skillIds) {
                    $user->skills()->sync($skillIds);
                }
            }

            // Langues (chaîne CSV avec entités HTML, ex: "Fran&#231;ais, Anglais")
            if (!empty($raw['languages'])) {
                $langIds = [];
                $langs   = explode(',', html_entity_decode($raw['languages'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                foreach ($langs as $lang) {
                    $lang = trim($lang);
                    if ($lang !== '') {
                        $langIds[] = Language::firstOrCreate(['name' => $lang])->id;
                    }
                }
                if ($langIds) {
                    $user->languages()->sync($langIds);
                }
            }

            // Secteur d'activité souhaité (champ texte simple)
            if (!empty($raw['desiredSector'])) {
                $sector = ActivitySector::firstOrCreate(['name' => html_entity_decode(
                    $raw['desiredSector'], ENT_QUOTES | ENT_HTML5, 'UTF-8'
                )]);
                $user->activitySectors()->sync([$sector->id]);
            }

            $imported++;
        }

        $this->command->info("✓ {$imported} talents importés.");
        if ($skipped > 0) {
            $this->command->warn("⚠ {$skipped} talents ignorés (email déjà existant, email vide, ou ref_ancien_crm en double).");
        }
    }
}
