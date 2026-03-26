<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Entreprise;
use App\Models\MediaCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ImportArticlesFromLegacySeeder extends Seeder
{
    /**
     * Importe les articles depuis l'export JSON de l'ancien projet.
     *
     * Placer le fichier JSON ici : database/data/articles_legacy.json
     */
    public function run(): void
    {
        $jsonPath = database_path('data/articles_legacy.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("Fichier introuvable : {$jsonPath}");
            $this->command->info("Placez votre export JSON dans database/data/articles_legacy.json");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['articles'])) {
            $this->command->error('JSON invalide ou clé "articles" manquante.');
            return;
        }

        $articles = $data['articles'];
        $this->command->info("Import de {$data['total']} articles...");

        // Admin par défaut (fallback si l'email de l'auteur n'existe pas)
        $defaultAdmin = User::where('role', 'admin')->first();

        if (!$defaultAdmin) {
            $this->command->error('Aucun admin trouvé. Lancez AdminUserSeeder d\'abord.');
            return;
        }

        $imported   = 0;
        $skipped    = 0;
        $noImage    = 0;

        foreach ($articles as $raw) {
            // Skip si déjà importé (idempotent par slug)
            $slug = Str::slug($raw['slug'] ?? $raw['title']);
            if (Article::where('slug', $slug)->exists()) {
                $skipped++;
                continue;
            }

            // Résolution user_id : chercher l'admin par email, sinon fallback
            $userId = $defaultAdmin->id;
            if (!empty($raw['admin']['email'])) {
                $user = User::where('email', $raw['admin']['email'])->first();
                if ($user) {
                    $userId = $user->id;
                }
            }

            // Résolution entreprise_id par nom
            $entrepriseId = null;
            if (!empty($raw['company']['name'])) {
                $entreprise = Entreprise::where('nom', $raw['company']['name'])->first();
                if ($entreprise) {
                    $entrepriseId = $entreprise->id;
                }
            }

            // Image : les URLs externes (localhost ou talenteed.io) ne peuvent pas
            // être stockées via Storage — on les ignore. Re-uploader depuis l'admin.
            $image = null;
            if (!empty($raw['image']['fileUrl'])) {
                $url = $raw['image']['fileUrl'];
                // Conserver uniquement les URLs talenteed.io de production
                if (str_contains($url, 'talenteed.io')) {
                    // Stockée comme URL brute dans le champ image pour référence
                    // L'admin devra re-uploader via l'interface
                    $image = null;
                    $noImage++;
                } else {
                    $noImage++;
                }
            }

            // Mapping statut
            $isPublished = ($raw['status'] ?? 'draft') === 'public';

            $article = Article::create([
                'title'        => $raw['title'],
                'slug'         => $slug,
                'content'      => $raw['content'] ?? '',
                'is_published' => $isPublished,
                'user_id'      => $userId,
                'entreprise_id'=> $entrepriseId,
                'image'        => $image,
            ]);

            // Rattachement des catégories média (find or create par nom)
            if (!empty($raw['categories'])) {
                $categoryIds = [];
                foreach ($raw['categories'] as $cat) {
                    $mediaCategory = MediaCategory::firstOrCreate(
                        ['name' => $cat['name']],
                        [
                            'slug'       => Str::slug($cat['name']),
                            'is_active'  => ($cat['status'] ?? 'public') === 'public',
                            'created_by' => $defaultAdmin->id,
                        ]
                    );
                    $categoryIds[] = $mediaCategory->id;
                }
                $article->mediaCategories()->sync($categoryIds);
            }

            $imported++;
        }

        $this->command->info("✓ {$imported} articles importés.");
        if ($skipped > 0) {
            $this->command->warn("⚠ {$skipped} articles ignorés (slug déjà existant).");
        }
        if ($noImage > 0) {
            $this->command->warn("⚠ {$noImage} images non importées (URLs externes) — re-uploader via l'admin.");
        }
    }
}
