<?php

namespace App\Console\Commands;

use App\Models\ActivitySector;
use App\Models\Experience;
use App\Models\Language;
use App\Models\StudyLevel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportCandidats extends Command
{
    protected $signature = 'import:candidats
                            {file=candidats.xls : Chemin du fichier XLS (relatif à la racine du projet)}
                            {--dry-run : Simuler sans écrire en base}';

    protected $description = 'Importe les candidats depuis un fichier XLS (ancien CRM)';

    private const ETAPE_MAP = [
        'a traiter !'           => 'a_traiter',
        'en cours de qualif.'   => 'en_cours_qualif',
        'vivier'                => 'vivier',
        'top profil'            => 'top_profil',
        'converti en ressource' => 'converti_ressource',
        'recruté par client'    => 'recrute_client',
        'ne plus contacter'     => 'ne_plus_contacter',
    ];

    private const TYPE_MAP = [
        'candidat'              => 'talent',
        'consultant externe'    => 'consultant_externe',
        'business dev. externe' => 'consultant_externe',
        'consultant interne'    => null,
        'business dev. interne' => null,
    ];

    private array $stats = ['created' => 0, 'skipped' => 0, 'existing' => 0, 'errors' => 0];

    public function handle(): int
    {
        $dryRun   = $this->option('dry-run');
        $filePath = base_path($this->argument('file'));

        if (!file_exists($filePath)) {
            $this->error("Fichier introuvable : {$filePath}");
            return self::FAILURE;
        }

        $this->info("Chargement de {$filePath}...");
        $sheet = IOFactory::load($filePath)->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, false);

        $headers = array_map(
            fn($h) => $this->fixEncoding(trim((string) $h)),
            array_shift($rows)
        );

        $this->info(count($rows) . " lignes à traiter" . ($dryRun ? ' [DRY-RUN]' : '') . "...");
        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            $this->processRow(array_combine($headers, $row), $dryRun);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Créés', 'Ignorés (internes)', 'Déjà existants', 'Erreurs'],
            [[$this->stats['created'], $this->stats['skipped'], $this->stats['existing'], $this->stats['errors']]]
        );

        if ($dryRun) {
            $this->warn('Mode dry-run : aucune donnée écrite en base.');
        }

        return self::SUCCESS;
    }

    private function processRow(array $data, bool $dryRun): void
    {
        try {
            // Rôle
            $type = strtolower(trim($this->fixEncoding((string) ($data['Type'] ?? ''))));
            $role = self::TYPE_MAP[$type] ?? null;
            if ($role === null) {
                $this->stats['skipped']++;
                return;
            }

            // Email
            $email = trim($this->fixEncoding((string) ($data['Email 1'] ?? '')));
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->stats['errors']++;
                return;
            }

            // Doublon
            if (User::where('email', $email)->exists()) {
                $this->stats['existing']++;
                return;
            }

            if ($dryRun) {
                $this->stats['created']++;
                return;
            }

            // Nom
            $nom    = $this->fixEncoding(trim((string) ($data['Nom'] ?? '')));
            $prenom = $this->fixEncoding(trim((string) ($data['Prénom'] ?? '')));
            $name   = trim("{$prenom} {$nom}") ?: $email;

            // Statut CRM
            $etape     = strtolower(trim($this->fixEncoding((string) ($data['…tape'] ?? ''))));
            $statutCrm = self::ETAPE_MAP[$etape] ?? null;

            // Téléphone (peut être un nombre Excel)
            $tel = $this->fixEncoding((string) ($data['Téléphone 1'] ?? ''));
            $tel = is_numeric($tel) ? (string) (int) $tel : trim($tel);

            // Date naissance (numéro de série Excel → date)
            $dateNaissance = $this->excelDateToCarbon($data['Date de naissance'] ?? null)?->format('Y-m-d');

            // Date création historique
            $createdAt = $this->excelDateToCarbon($data['Date de création'] ?? null);

            // Provenance
            $provenance = $this->fixEncoding(trim((string) ($data['Provenance - Type'] ?? '')));
            if (strtolower($provenance) === 'jobboard') {
                $provenance = 'Talenteed';
            }

            // Study level
            $formationRaw = $this->fixEncoding(trim((string) ($data['Formation'] ?? '')));
            $studyLevel   = $formationRaw ? StudyLevel::firstOrCreate(['name' => $formationRaw]) : null;

            // Experience
            $expRaw     = $this->fixEncoding(trim((string) ($data['Expérience'] ?? '')));
            $experience = $expRaw ? Experience::firstOrCreate(['name' => $expRaw]) : null;

            // Créer l'user
            $user = User::create([
                'name'              => $name,
                'email'             => $email,
                'password'          => Hash::make(Str::random(32)),
                'role'              => $role,
                'is_suspended'      => false,
                'is_banned'         => $statutCrm === 'ne_plus_contacter',
                'civilite'          => $this->fixEncoding(trim((string) ($data['Civilité'] ?? ''))) ?: null,
                'titre_poste'       => $this->fixEncoding(trim((string) ($data['Titre'] ?? ''))) ?: null,
                'telephone'         => $tel ?: null,
                'date_naissance'    => $dateNaissance,
                'nationalite'       => $this->fixEncoding(trim((string) ($data['Nationalité'] ?? ''))) ?: null,
                'ville'             => $this->fixEncoding(trim((string) ($data['Ville'] ?? ''))) ?: null,
                'pays'              => $this->fixEncoding(trim((string) ($data['Pays'] ?? ''))) ?: null,
                'disponibilite'     => $this->fixEncoding(trim((string) ($data['Disponibilité'] ?? ''))) ?: null,
                'mobilite'          => $this->fixEncoding(trim((string) ($data['Mobilité'] ?? ''))) ?: null,
                'source_provenance' => $provenance ?: null,
                'ref_ancien_crm'    => $this->fixEncoding(trim((string) ($data['Référence interne'] ?? ''))) ?: null,
                'statut_crm'        => $statutCrm,
                'study_level_id'    => $studyLevel?->id,
                'experience_id'     => $experience?->id,
            ]);

            // Préserver la date historique de création
            if ($createdAt) {
                $user->timestamps = false;
                $user->created_at = $createdAt;
                $user->save();
                $user->timestamps = true;
            }

            // Secteurs (many-to-many)
            $secteursRaw = $this->fixEncoding((string) ($data['Secteurs'] ?? ''));
            if ($secteursRaw) {
                $sectorIds = collect(explode(',', $secteursRaw))
                    ->map(fn($s) => trim($s))
                    ->filter()
                    ->map(fn($name) => ActivitySector::firstOrCreate(['name' => $name])->id)
                    ->toArray();
                $user->activitySectors()->sync($sectorIds);
            }

            // Langues — format "Français (),Anglais (niveau),..."
            $languesRaw = $this->fixEncoding((string) ($data['Langues'] ?? ''));
            if ($languesRaw) {
                $languageIds = collect(explode(',', $languesRaw))
                    ->map(fn($l) => trim(preg_replace('/\s*\(.*?\)/', '', $l)))
                    ->filter(fn($l) => mb_strlen($l) >= 2)
                    ->unique()
                    ->map(fn($name) => Language::firstOrCreate(['name' => ucfirst(mb_strtolower($name))])->id)
                    ->toArray();
                $user->languages()->sync($languageIds);
            }

            $this->stats['created']++;

        } catch (\Exception $e) {
            $this->stats['errors']++;
            $this->newLine();
            $this->warn("Erreur (email: " . ($data['Email 1'] ?? '?') . ") : " . $e->getMessage());
        }
    }

    /**
     * Convertit un numéro de série Excel en Carbon (ou null si invalide).
     */
    private function excelDateToCarbon(mixed $value): ?Carbon
    {
        if (!is_numeric($value) || $value < 1000) {
            return null;
        }
        try {
            return Carbon::createFromTimestamp((int) (($value - 25569) * 86400));
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Corrige l'encodage Windows-1252 → UTF-8 si nécessaire.
     */
    private function fixEncoding(string $value): string
    {
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }
        return mb_convert_encoding($value, 'UTF-8', 'Windows-1252') ?: $value;
    }
}
