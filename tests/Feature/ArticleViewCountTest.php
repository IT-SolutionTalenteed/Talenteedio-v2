<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleViewCountTest extends TestCase
{
    use RefreshDatabase;

    private function publishedArticle(array $attributes = []): Article
    {
        $user = User::factory()->create();

        return Article::create(array_merge([
            'title'        => 'Un article publié',
            'content'      => '<p>Contenu</p>',
            'slug'         => 'un-article-publie',
            'is_published' => true,
            'published_at' => now(),
            'user_id'      => $user->id,
        ], $attributes));
    }

    public function test_un_article_neuf_demarre_a_zero_vue(): void
    {
        $article = $this->publishedArticle();

        $this->getJson("/api/public/articles/{$article->id}")
            ->assertOk()
            ->assertJsonPath('views_count', 0);
    }

    public function test_enregistrer_une_vue_incremente_le_compteur(): void
    {
        $article = $this->publishedArticle();

        $this->postJson("/api/public/articles/{$article->id}/view")
            ->assertOk()
            ->assertJsonPath('views_count', 1);

        $this->assertSame(1, $article->fresh()->views_count);
    }

    public function test_le_meme_visiteur_ne_recompte_pas_dans_la_fenetre_de_24h(): void
    {
        $article = $this->publishedArticle();

        $this->postJson("/api/public/articles/{$article->id}/view");
        $this->postJson("/api/public/articles/{$article->id}/view")
            ->assertOk()
            ->assertJsonPath('views_count', 1);

        $this->assertSame(1, $article->fresh()->views_count);
    }

    public function test_un_autre_visiteur_compte_pour_une_vue_supplementaire(): void
    {
        $article = $this->publishedArticle();

        $this->postJson("/api/public/articles/{$article->id}/view");

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->withHeaders(['User-Agent' => 'un-autre-navigateur'])
            ->postJson("/api/public/articles/{$article->id}/view")
            ->assertOk()
            ->assertJsonPath('views_count', 2);
    }

    public function test_un_article_non_publie_ne_peut_pas_etre_compte(): void
    {
        $article = $this->publishedArticle(['is_published' => false]);

        $this->postJson("/api/public/articles/{$article->id}/view")
            ->assertNotFound();

        $this->assertSame(0, $article->fresh()->views_count);
    }

    public function test_un_article_archive_ne_peut_pas_etre_compte(): void
    {
        $article = $this->publishedArticle(['archived_at' => now()]);

        $this->postJson("/api/public/articles/{$article->id}/view")
            ->assertNotFound();

        $this->assertSame(0, $article->fresh()->views_count);
    }

    public function test_les_vues_de_depart_suivent_l_anciennete_de_l_article(): void
    {
        $recent = $this->publishedArticle(['published_at' => now()->subDays(2)]);
        $ancien = $this->publishedArticle([
            'slug'         => 'un-article-ancien',
            'published_at' => now()->subDays(100),
        ]);

        $migration = require database_path('migrations/2026_08_15_120001_seed_initial_article_views.php');
        $migration->up();

        // 40 + jours * (1,8 à 9,2) + écart final de 0 à 30
        $this->assertGreaterThanOrEqual(43, $recent->fresh()->views_count);
        $this->assertLessThanOrEqual(88, $recent->fresh()->views_count);
        $this->assertGreaterThanOrEqual(220, $ancien->fresh()->views_count);
        $this->assertLessThanOrEqual(990, $ancien->fresh()->views_count);
        $this->assertGreaterThan($recent->fresh()->views_count, $ancien->fresh()->views_count);
    }

    public function test_les_vues_de_depart_n_ecrasent_pas_le_trafic_deja_compte(): void
    {
        $article = $this->publishedArticle(['published_at' => now()->subDays(100)]);
        Article::whereKey($article->id)->update(['views_count' => 7]);

        $migration = require database_path('migrations/2026_08_15_120001_seed_initial_article_views.php');
        $migration->up();

        $this->assertSame(7, $article->fresh()->views_count);
    }

    public function test_le_compteur_n_est_pas_modifiable_par_affectation_de_masse(): void
    {
        $article = $this->publishedArticle();

        $article->update(['title' => 'Titre modifié', 'views_count' => 9999, 'shares_count' => 9999]);

        $this->assertSame(0, $article->fresh()->views_count);
        $this->assertSame(0, $article->fresh()->shares_count);
    }

    // ── Partages ──

    public function test_enregistrer_un_partage_incremente_le_compteur(): void
    {
        $article = $this->publishedArticle();

        $this->postJson("/api/public/articles/{$article->id}/share", ['network' => 'facebook'])
            ->assertOk()
            ->assertJsonPath('shares_count', 1);

        $this->assertSame(1, $article->fresh()->shares_count);
    }

    public function test_repartager_sur_le_meme_reseau_ne_recompte_pas_dans_les_24h(): void
    {
        $article = $this->publishedArticle();

        $this->postJson("/api/public/articles/{$article->id}/share", ['network' => 'facebook']);
        $this->postJson("/api/public/articles/{$article->id}/share", ['network' => 'facebook'])
            ->assertOk()
            ->assertJsonPath('shares_count', 1);

        $this->assertSame(1, $article->fresh()->shares_count);
    }

    public function test_partager_sur_un_autre_reseau_compte_une_fois_de_plus(): void
    {
        $article = $this->publishedArticle();

        $this->postJson("/api/public/articles/{$article->id}/share", ['network' => 'facebook']);
        $this->postJson("/api/public/articles/{$article->id}/share", ['network' => 'linkedin'])
            ->assertOk()
            ->assertJsonPath('shares_count', 2);
    }

    public function test_un_reseau_inconnu_est_refuse(): void
    {
        $article = $this->publishedArticle();

        $this->postJson("/api/public/articles/{$article->id}/share", ['network' => 'myspace'])
            ->assertStatus(422);

        $this->assertSame(0, $article->fresh()->shares_count);
    }

    public function test_un_partage_sans_reseau_est_refuse(): void
    {
        $article = $this->publishedArticle();

        $this->postJson("/api/public/articles/{$article->id}/share", [])
            ->assertStatus(422);

        $this->assertSame(0, $article->fresh()->shares_count);
    }

    public function test_un_article_non_publie_ne_peut_pas_etre_partage(): void
    {
        $article = $this->publishedArticle(['is_published' => false]);

        $this->postJson("/api/public/articles/{$article->id}/share", ['network' => 'facebook'])
            ->assertNotFound();

        $this->assertSame(0, $article->fresh()->shares_count);
    }

    public function test_les_partages_de_depart_sont_proportionnels_aux_vues(): void
    {
        $article = $this->publishedArticle();
        Article::whereKey($article->id)->update(['views_count' => 1200]);

        $migration = require database_path('migrations/2026_08_15_130001_seed_initial_article_shares.php');
        $migration->up();

        // 1200 / (40 à 80) → 15 à 30
        $this->assertGreaterThanOrEqual(15, $article->fresh()->shares_count);
        $this->assertLessThanOrEqual(30, $article->fresh()->shares_count);
    }

    public function test_les_partages_de_depart_n_ecrasent_pas_les_partages_deja_comptes(): void
    {
        $article = $this->publishedArticle();
        Article::whereKey($article->id)->update(['views_count' => 1200, 'shares_count' => 3]);

        $migration = require database_path('migrations/2026_08_15_130001_seed_initial_article_shares.php');
        $migration->up();

        $this->assertSame(3, $article->fresh()->shares_count);
    }
}
