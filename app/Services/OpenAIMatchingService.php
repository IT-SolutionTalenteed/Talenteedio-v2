<?php

namespace App\Services;

use App\Models\Evenement;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Smalot\PdfParser\Parser as PdfParser;

class OpenAIMatchingService
{
    /**
     * Matching événement : talent vs entreprises participantes (avec leurs offres).
     *
     * @param  User       $talent     Talent authentifié (avec relations chargées)
     * @param  string     $posteRecherche
     * @param  string|null $cvText    Contenu textuel du CV parsé
     * @param  Evenement  $evenement  Événement avec entreprises + offres
     * @return array      Liste triée d'entreprises avec score, raison, offres matchées
     */
    public function matchEvenement(User $talent, string $posteRecherche, ?string $cvText, Evenement $evenement, array $overrides = []): array
    {
        $evenement->loadMissing(['entreprises.offres.skills', 'entreprises.offres.activitySector', 'entreprises.activitySector']);

        if ($evenement->entreprises->isEmpty()) {
            return [];
        }

        $talentBlock  = $this->buildTalentBlock($talent, $posteRecherche, $cvText, $overrides);
        $entreprisesBlock = $this->buildEntreprisesBlock($evenement->entreprises);

        $systemPrompt = $this->buildSystemPrompt('evenement');
        $userContent  = $talentBlock . "\n\n" . $entreprisesBlock;

        $rawResults = $this->callOpenAI($systemPrompt, $userContent, 2000);

        // Réenrichir avec les données locales complètes
        return collect($rawResults)->map(function ($item) use ($evenement) {
            $entreprise = $evenement->entreprises->firstWhere('id', $item['entreprise_id'] ?? null);
            if ($entreprise) {
                $item['logo_url']    = $entreprise->logo_url;
                $item['description'] = $entreprise->description;
                $item['ville']       = $entreprise->ville;
                $item['pays']        = $entreprise->pays;
                $item['secteur']     = $entreprise->activitySector?->name;
            }
            return $item;
        })->sortByDesc('score')->values()->toArray();
    }

    /**
     * Matching global : talent vs toutes les offres en base.
     *
     * @param  User        $talent
     * @param  string      $posteRecherche
     * @param  string|null $cvText
     * @param  array       $offres   Collection d'Offre avec relations chargées
     * @return array       Liste triée d'offres avec score et raison
     */
    public function matchOffresGlobal(User $talent, string $posteRecherche, ?string $cvText, $offres, array $overrides = []): array
    {
        if ($offres->isEmpty()) {
            return [];
        }

        $talentBlock = $this->buildTalentBlock($talent, $posteRecherche, $cvText, $overrides);
        $offresBlock = $this->buildOffresBlock($offres);

        $systemPrompt = $this->buildSystemPrompt('offres');
        $userContent  = $talentBlock . "\n\n" . $offresBlock;

        $rawResults = $this->callOpenAI($systemPrompt, $userContent, 3000);

        // Réenrichir avec les données locales
        return collect($rawResults)->map(function ($item) use ($offres) {
            $offre = $offres->firstWhere('id', $item['offre_id'] ?? null);
            if ($offre) {
                $item['titre']       = $offre->titre;
                $item['localisation']= $offre->localisation;
                $item['entreprise']  = $offre->entreprise?->nom;
                $item['logo_url']    = $offre->entreprise?->logo_url;
                $item['secteur']     = $offre->activitySector?->name ?? $offre->entreprise?->activitySector?->name;
            }
            return $item;
        })->sortByDesc('score')->values()->toArray();
    }

    /**
     * Matching d'un talent avec une offre spécifique.
     *
     * @param  User   $talent
     * @param  string $cvText
     * @param  Offre  $offre
     * @return array  {score: int, raison: string, details: array}
     */
    public function matchSingleOffre(User $talent, string $cvText, $offre): array
    {
        $talentBlock = $this->buildTalentBlockSimple($talent, $cvText);
        $offreBlock = $this->buildSingleOffreBlock($offre);

        $systemPrompt = $this->buildSystemPromptSingle();
        $userContent  = $talentBlock . "\n\n" . $offreBlock;

        $result = $this->callOpenAI($systemPrompt, $userContent, 1000);

        // Le résultat devrait être un objet avec score, raison, et détails
        if (is_array($result) && isset($result['score'])) {
            return $result;
        }

        // Si c'est un tableau d'objets, prendre le premier
        if (is_array($result) && !empty($result) && isset($result[0]['score'])) {
            return $result[0];
        }

        return [
            'score' => 0,
            'raison' => 'Erreur lors de l\'analyse du matching.',
            'details' => []
        ];
    }

    // ─────────────────────────────────────────────
    //  Parsing CV
    // ─────────────────────────────────────────────

    /**
     * Extrait le texte brut d'un fichier CV (PDF ou DOCX).
     * Retourne null si le parsing échoue ou si le fichier n'est pas supporté.
     */
    public function parseCv(string $filePath, string $originalName): ?string
    {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        Log::info("Parsing CV: {$originalName} (extension: {$ext})");

        try {
            if ($ext === 'pdf') {
                if (!file_exists($filePath)) {
                    Log::error("PDF file not found: {$filePath}");
                    return null;
                }

                $parser = new PdfParser();
                $pdf    = $parser->parseFile($filePath);
                $text   = $pdf->getText();
                
                Log::info("PDF parsed successfully, text length: " . strlen($text));
                
                if (empty(trim($text))) {
                    Log::warning("PDF parsing returned empty text");
                    // Essayer une méthode alternative pour les PDFs scannés
                    return "CV PDF (contenu non extractible - possiblement scanné)";
                }
                
                return $this->sanitizeCvText($text);
            }

            if (in_array($ext, ['docx', 'doc'])) {
                if (!file_exists($filePath)) {
                    Log::error("DOCX file not found: {$filePath}");
                    return null;
                }

                // phpoffice/phpword n'est pas installé — on lit le XML interne du DOCX
                if ($ext === 'docx') {
                    $text = $this->extractDocxText($filePath);
                    if ($text) {
                        Log::info("DOCX parsed successfully, text length: " . strlen($text));
                        return $text;
                    }
                }
                
                // .doc binaire : on ne peut pas parser sans lib dédiée
                Log::warning("DOC format not fully supported");
                return "CV Word (format .doc non supporté - utilisez .docx ou .pdf)";
            }

            Log::warning("Unsupported file extension: {$ext}");
            return null;
        } catch (\Throwable $e) {
            Log::error("CV parsing failed for {$originalName}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Retourner un texte minimal plutôt que null pour permettre le matching
            return "CV uploadé (extraction automatique échouée - analyse basée sur le profil utilisateur)";
        }
    }

    /**
     * Extrait le texte d'un .docx en lisant word/document.xml dans le ZIP.
     */
    private function extractDocxText(string $filePath): ?string
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) !== true) {
                Log::error("Failed to open DOCX as ZIP: {$filePath}");
                return null;
            }

            $xml = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($xml === false) {
                Log::error("Failed to extract word/document.xml from DOCX");
                return null;
            }

            // Supprimer les balises XML et garder le texte
            $text = strip_tags(str_replace(['</w:p>', '</w:tr>'], "\n", $xml));
            return $this->sanitizeCvText($text);
        } catch (\Throwable $e) {
            Log::error("DOCX extraction error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Nettoie et tronque le texte du CV pour éviter de surcharger le prompt.
     */
    private function sanitizeCvText(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        // Tronquer à 3000 caractères pour rester dans les limites de tokens
        return mb_substr($text, 0, 3000);
    }

    // ─────────────────────────────────────────────
    //  Builders de blocs texte
    // ─────────────────────────────────────────────

    /**
     * Extrait les compétences et un résumé de profil depuis le texte d'un CV.
     * Appelé par l'endpoint POST /talent/cv/parse pour pré-remplir le formulaire.
     *
     * @return array{competences: string[], resume: string|null}
     */
    public function extractCvSkills(string $cvText): array
    {
        $systemPrompt = <<<PROMPT
Tu es un assistant RH. Analyse ce CV et extrait :
1. La liste des compétences techniques et soft skills mentionnées
2. Un résumé du profil en 1-2 phrases

Réponds UNIQUEMENT avec un JSON valide, sans markdown autour :
{
  "competences": ["PHP", "Laravel", "React", ...],
  "resume": "Développeur full-stack avec 5 ans d'expérience..."
}
PROMPT;

        $result = $this->callOpenAI($systemPrompt, "CV :\n{$cvText}", 600);

        // callOpenAI retourne un array, mais ici on attend un objet — on reparse
        if (is_array($result) && isset($result['competences'])) {
            return $result;
        }

        return ['competences' => [], 'resume' => null];
    }

    /**
     * Construit le bloc texte du talent pour le prompt OpenAI.
     * Les $overrides (issus du formulaire) priment sur les valeurs du profil BDD.
     *
     * @param array $overrides {
     *   pays_souhaites?: string[],
     *   villes_souhaitees?: string[],
     *   secteur_souhaite_id?: int,
     *   competences_libres?: string,   // compétences extraites du CV ou saisies manuellement
     * }
     */
    private function buildTalentBlock(User $talent, string $posteRecherche, ?string $cvText, array $overrides = []): string
    {
        $lines = ["## PROFIL DU TALENT"];
        $lines[] = "Nom: {$talent->name}";
        $lines[] = "Poste recherché: {$posteRecherche}";

        if ($talent->titre_poste) {
            $lines[] = "Titre actuel: {$talent->titre_poste}";
        }

        // Localisation actuelle
        $locActuelle = implode(', ', array_filter([$talent->ville, $talent->pays]));
        if ($locActuelle) {
            $lines[] = "Localisation actuelle: {$locActuelle}";
        }

        // Préférences géographiques — override formulaire en priorité
        $paysSouhaites = $overrides['pays_souhaites'] ?? $talent->pays_souhaites;
        if (!empty($paysSouhaites)) {
            $lines[] = "Pays où il souhaite travailler: " . implode(', ', (array) $paysSouhaites);
        } else {
            $lines[] = "Pays où il souhaite travailler: flexible (peu importe)";
        }

        $villesSouhaitees = $overrides['villes_souhaitees'] ?? $talent->villes_souhaitees;
        if (!empty($villesSouhaitees)) {
            $lines[] = "Villes souhaitées: " . implode(', ', (array) $villesSouhaitees);
        } else {
            $lines[] = "Villes souhaitées: flexible (peu importe)";
        }

        // Secteur souhaité — override formulaire en priorité
        if (!empty($overrides['secteur_souhaite_id'])) {
            $secteur = \App\Models\ActivitySector::find($overrides['secteur_souhaite_id']);
            if ($secteur) {
                $lines[] = "Secteur d'activité souhaité: {$secteur->name}";
            }
        } else {
            $talent->loadMissing('secteurSouhaite');
            if ($talent->secteurSouhaite) {
                $lines[] = "Secteur d'activité souhaité: {$talent->secteurSouhaite->name}";
            }
        }

        // Secteurs d'activité du profil
        $talent->loadMissing('activitySectors');
        if ($talent->activitySectors->isNotEmpty()) {
            $lines[] = "Secteurs d'activité: " . $talent->activitySectors->pluck('name')->join(', ');
        }

        // Compétences — override formulaire (extraites du CV) en priorité, puis profil BDD
        if (!empty($overrides['competences_libres'])) {
            $lines[] = "Compétences (extraites du CV): " . $overrides['competences_libres'];
        } else {
            $talent->loadMissing('skills');
            if ($talent->skills->isNotEmpty()) {
                $lines[] = "Compétences: " . $talent->skills->pluck('name')->join(', ');
            }
        }

        // Langues
        $talent->loadMissing('languages');
        if ($talent->languages->isNotEmpty()) {
            $lines[] = "Langues: " . $talent->languages->pluck('name')->join(', ');
        }

        // Niveau d'études & expérience
        $talent->loadMissing(['studyLevel', 'experience']);
        if ($talent->studyLevel) {
            $lines[] = "Niveau d'études: {$talent->studyLevel->name}";
        }
        if ($talent->experience) {
            $lines[] = "Années d'expérience: {$talent->experience->name}";
        }

        // Mobilité
        if ($talent->mobilite) {
            $lines[] = "Mobilité: {$talent->mobilite}";
        }

        // Contenu parsé du CV (contexte complet pour OpenAI)
        if ($cvText) {
            $lines[] = "\n### CONTENU DU CV (extrait)";
            $lines[] = $cvText;
        }

        return implode("\n", $lines);
    }

    /**
     * Version simplifiée du bloc talent pour le matching d'une seule offre.
     */
    private function buildTalentBlockSimple(User $talent, string $cvText): string
    {
        $lines = ["## PROFIL DU CANDIDAT"];
        $lines[] = "Nom: {$talent->name}";

        if ($talent->titre_poste) {
            $lines[] = "Titre actuel: {$talent->titre_poste}";
        }

        // Localisation
        $locActuelle = implode(', ', array_filter([$talent->ville, $talent->pays]));
        if ($locActuelle) {
            $lines[] = "Localisation: {$locActuelle}";
        }

        // Secteurs d'activité
        $talent->loadMissing('activitySectors');
        if ($talent->activitySectors->isNotEmpty()) {
            $lines[] = "Secteurs d'activité: " . $talent->activitySectors->pluck('name')->join(', ');
        }

        // Compétences
        $talent->loadMissing('skills');
        if ($talent->skills->isNotEmpty()) {
            $lines[] = "Compétences: " . $talent->skills->pluck('name')->join(', ');
        }

        // Langues
        $talent->loadMissing('languages');
        if ($talent->languages->isNotEmpty()) {
            $lines[] = "Langues: " . $talent->languages->pluck('name')->join(', ');
        }

        // Niveau d'études & expérience
        $talent->loadMissing(['studyLevel', 'experience']);
        if ($talent->studyLevel) {
            $lines[] = "Niveau d'études: {$talent->studyLevel->name}";
        }
        if ($talent->experience) {
            $lines[] = "Expérience: {$talent->experience->name}";
        }

        // CV
        if ($cvText) {
            $lines[] = "\n### CONTENU DU CV";
            $lines[] = $cvText;
        }

        return implode("\n", $lines);
    }

    /**
     * Construit le bloc pour une seule offre.
     */
    private function buildSingleOffreBlock($offre): string
    {
        $lines = ["## OFFRE D'EMPLOI"];
        $lines[] = "Titre: {$offre->titre}";

        if ($offre->entreprise) {
            $lines[] = "Entreprise: {$offre->entreprise->nom}";
            if ($offre->entreprise->activitySector) {
                $lines[] = "Secteur entreprise: {$offre->entreprise->activitySector->name}";
            }
            $locEntreprise = implode(', ', array_filter([$offre->entreprise->ville, $offre->entreprise->pays]));
            if ($locEntreprise) {
                $lines[] = "Siège entreprise: {$locEntreprise}";
            }
        }

        if ($offre->localisation) {
            $lines[] = "Lieu du poste: {$offre->localisation}";
        }

        if ($offre->activitySector) {
            $lines[] = "Secteur du poste: {$offre->activitySector->name}";
        }

        if ($offre->description) {
            $lines[] = "\nDescription du poste:";
            $lines[] = $offre->description;
        }

        if ($offre->mission) {
            $lines[] = "\nMission:";
            $lines[] = $offre->mission;
        }

        if ($offre->profil_recherche) {
            $lines[] = "\nProfil recherché:";
            $lines[] = $offre->profil_recherche;
        }

        if ($offre->skills->isNotEmpty()) {
            $lines[] = "\nCompétences requises: " . $offre->skills->pluck('name')->join(', ');
        }

        if ($offre->fourchette_salariale) {
            $lines[] = "Salaire: {$offre->fourchette_salariale}";
        }

        if ($offre->jobContracts->isNotEmpty()) {
            $lines[] = "Type de contrat: " . $offre->jobContracts->pluck('name')->join(', ');
        }

        if ($offre->jobModes->isNotEmpty()) {
            $lines[] = "Mode de travail: " . $offre->jobModes->pluck('name')->join(', ');
        }

        if ($offre->studyLevels->isNotEmpty()) {
            $lines[] = "Niveau d'études requis: " . $offre->studyLevels->pluck('name')->join(', ');
        }

        if ($offre->experiences->isNotEmpty()) {
            $lines[] = "Expérience requise: " . $offre->experiences->pluck('name')->join(', ');
        }

        return implode("\n", $lines);
    }

    private function buildEntreprisesBlock($entreprises): string
    {
        $lines = ["## ENTREPRISES PARTICIPANTES À L'ÉVÉNEMENT"];

        foreach ($entreprises as $e) {
            $lines[] = "\n### Entreprise ID={$e->id}: {$e->nom}";
            if ($e->activitySector) {
                $lines[] = "Secteur: {$e->activitySector->name}";
            }
            $loc = implode(', ', array_filter([$e->ville, $e->pays]));
            if ($loc) {
                $lines[] = "Localisation: {$loc}";
            }
            if ($e->description) {
                $lines[] = "Description: {$e->description}";
            }

            if ($e->offres->isNotEmpty()) {
                $lines[] = "Offres publiées:";
                foreach ($e->offres as $o) {
                    $lines[] = "  - Offre ID={$o->id}: {$o->titre}";
                    if ($o->localisation) {
                        $lines[] = "    Lieu: {$o->localisation}";
                    }
                    if ($o->activitySector) {
                        $lines[] = "    Secteur: {$o->activitySector->name}";
                    }
                    if ($o->description) {
                        $lines[] = "    Description: " . mb_substr($o->description, 0, 300);
                    }
                    if ($o->profil_recherche) {
                        $lines[] = "    Profil recherché: " . mb_substr($o->profil_recherche, 0, 300);
                    }
                    if ($o->skills->isNotEmpty()) {
                        $lines[] = "    Compétences requises: " . $o->skills->pluck('name')->join(', ');
                    }
                    if ($o->fourchette_salariale) {
                        $lines[] = "    Salaire: {$o->fourchette_salariale}";
                    }
                }
            } else {
                $lines[] = "Offres publiées: aucune";
            }
        }

        return implode("\n", $lines);
    }

    private function buildOffresBlock($offres): string
    {
        $lines = ["## OFFRES D'EMPLOI DISPONIBLES"];

        foreach ($offres as $o) {
            $lines[] = "\n### Offre ID={$o->id}: {$o->titre}";
            if ($o->entreprise) {
                $lines[] = "Publiée par: {$o->entreprise->nom}";
                if ($o->entreprise->activitySector) {
                    $lines[] = "Secteur entreprise: {$o->entreprise->activitySector->name}";
                }
                $locEntreprise = implode(', ', array_filter([$o->entreprise->ville, $o->entreprise->pays]));
                if ($locEntreprise) {
                    $lines[] = "Siège entreprise: {$locEntreprise}";
                }
            }
            if ($o->localisation) {
                $lines[] = "Lieu du poste: {$o->localisation}";
            }
            if ($o->activitySector) {
                $lines[] = "Secteur du poste: {$o->activitySector->name}";
            }
            if ($o->description) {
                $lines[] = "Description: " . mb_substr($o->description, 0, 300);
            }
            if ($o->mission) {
                $lines[] = "Mission: " . mb_substr($o->mission, 0, 200);
            }
            if ($o->profil_recherche) {
                $lines[] = "Profil recherché: " . mb_substr($o->profil_recherche, 0, 300);
            }
            if ($o->skills->isNotEmpty()) {
                $lines[] = "Compétences requises: " . $o->skills->pluck('name')->join(', ');
            }
            if ($o->fourchette_salariale) {
                $lines[] = "Salaire: {$o->fourchette_salariale}";
            }
            if ($o->jobContracts->isNotEmpty()) {
                $lines[] = "Type de contrat: " . $o->jobContracts->pluck('name')->join(', ');
            }
            if ($o->jobModes->isNotEmpty()) {
                $lines[] = "Mode de travail: " . $o->jobModes->pluck('name')->join(', ');
            }
        }

        return implode("\n", $lines);
    }

    // ─────────────────────────────────────────────
    //  Prompts système
    // ─────────────────────────────────────────────

    private function buildSystemPrompt(string $mode): string
    {
        $scoring = <<<SCORING
Calcule un score de matching global (0 à 100) en pondérant ces critères :
- 40% — Adéquation CV / compétences du talent avec les compétences requises par le poste/offre
- 25% — Adéquation du secteur d'activité du talent avec le secteur de l'entreprise/offre
- 20% — Adéquation géographique : pays et ville souhaités par le talent vs localisation du poste (si le talent est flexible, ce critère ne pénalise pas)
- 15% — Adéquation du poste recherché avec le titre de l'offre

Règles importantes :
- Si le talent n'a pas de préférence géographique (flexible), ne pas pénaliser pour la géographie.
- Si le CV contenu est fourni, il prévaut sur les seules compétences listées.
- Fournis une raison courte et précise en français (1-2 phrases maximum).
SCORING;

        if ($mode === 'evenement') {
            return <<<PROMPT
Tu es un expert en recrutement. On te donne le profil complet d'un talent (avec son CV si disponible) et la liste des entreprises participantes à un événement de speed recruiting avec leurs offres.
Classe chaque entreprise selon leur pertinence pour ce talent.

{$scoring}

Réponds UNIQUEMENT avec un JSON valide, sans texte ni markdown autour, sous cette forme exacte :
[
  {"entreprise_id": 1, "nom": "...", "score": 85, "raison": "...", "offres_matchees": [{"offre_id": 12, "titre": "...", "score_offre": 88}]},
  ...
]
Classe TOUTES les entreprises. Le tableau "offres_matchees" liste les offres les plus pertinentes de l'entreprise (max 3), triées par score décroissant.
PROMPT;
        }

        // mode offres
        return <<<PROMPT
Tu es un expert en recrutement. On te donne le profil complet d'un talent (avec son CV si disponible) et une liste d'offres d'emploi avec toutes leurs données (entreprise, secteur, localisation, compétences requises, type de contrat, etc.).
Classe chaque offre selon sa pertinence pour ce talent.

{$scoring}

Réponds UNIQUEMENT avec un JSON valide, sans texte ni markdown autour, sous cette forme exacte :
[
  {"offre_id": 1, "titre": "...", "entreprise": "...", "score": 85, "raison": "..."},
  ...
]
Classe TOUTES les offres.
PROMPT;
    }

    /**
     * Prompt système pour le matching d'une seule offre.
     */
    private function buildSystemPromptSingle(): string
    {
        return <<<PROMPT
Tu es un expert en recrutement. On te donne le profil complet d'un candidat (avec son CV) et une offre d'emploi détaillée.
Analyse la compatibilité entre le candidat et l'offre.

Calcule un score de matching global (0 à 100) en pondérant ces critères :
- 40% — Adéquation des compétences du candidat avec les compétences requises
- 25% — Adéquation du secteur d'activité et de l'expérience
- 20% — Adéquation géographique et mobilité
- 15% — Adéquation du niveau d'études et du type de contrat

Fournis également :
- Une raison globale du score (2-3 phrases)
- Des détails par catégorie avec un score et une explication courte

Réponds UNIQUEMENT avec un JSON valide, sans texte ni markdown autour, sous cette forme exacte :
{
  "score": 85,
  "raison": "Le candidat possède les compétences clés requises et son expérience correspond bien au poste. La localisation est compatible.",
  "details": {
    "competences": {"score": 90, "explication": "Maîtrise excellente des technologies requises (PHP, Laravel, React)"},
    "secteur": {"score": 80, "explication": "Expérience dans le secteur IT, compatible avec l'offre"},
    "localisation": {"score": 85, "explication": "Localisation compatible, mobilité possible"},
    "formation": {"score": 85, "explication": "Niveau d'études et expérience correspondent aux attentes"}
  }
}
PROMPT;
    }

    // ─────────────────────────────────────────────
    //  Appel OpenAI
    // ─────────────────────────────────────────────

    private function callOpenAI(string $systemPrompt, string $userContent, int $maxTokens): array
    {
        try {
            $response = OpenAI::chat()->create([
                'model'       => 'gpt-4o-mini',
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userContent],
                ],
                'max_tokens'  => $maxTokens,
                'temperature' => 0.2,
            ]);

            $content = $response->choices[0]->message->content ?? '[]';

            // Nettoyer si OpenAI ajoute des balises markdown
            $content = preg_replace('/^```json\s*/m', '', $content);
            $content = preg_replace('/^```\s*/m', '', $content);
            $content = trim($content);

            $results = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('OpenAI matching JSON decode error: ' . json_last_error_msg(), ['content' => $content]);
                return [];
            }

            return $results ?? [];
        } catch (\Throwable $e) {
            Log::error('OpenAI matching API error: ' . $e->getMessage());
            return [];
        }
    }
}
