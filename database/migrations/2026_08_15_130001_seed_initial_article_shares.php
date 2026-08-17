<?php

use App\Models\Article;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Donne un nombre de partages de départ aux articles déjà en ligne.
     *
     * Le chiffre se déduit des vues plutôt que de l'ancienneté : un article
     * beaucoup lu est aussi beaucoup partagé, et l'inverse serait incohérent
     * à l'affichage. Le taux retenu — un partage pour 40 à 80 vues — reste
     * dans les ordres de grandeur observés sur un blog.
     *
     * Comme pour les vues : migration et non seeder (le déploiement lance
     * `migrate`, jamais `db:seed`), et on ne touche qu'aux articles encore à 0.
     */
    public function up(): void
    {
        Article::query()
            ->where('shares_count', 0)
            ->get(['id', 'views_count'])
            ->each(function (Article $article) {
                $shares = intdiv($article->views_count, random_int(40, 80));

                Article::whereKey($article->id)->update(['shares_count' => $shares]);
            });
    }

    public function down(): void
    {
        // Pas de retour en arrière : on ne saurait pas distinguer les partages
        // de départ de ceux réellement enregistrés depuis.
    }
};
