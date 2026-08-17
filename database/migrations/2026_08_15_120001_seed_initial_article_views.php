<?php

use App\Models\Article;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Donne un nombre de vues de départ aux articles déjà en ligne.
     *
     * Sans ça, tous les articles publiés depuis des mois afficheraient « 0 vue »
     * le jour de la mise en production. Les chiffres sont proportionnels à
     * l'ancienneté de l'article (~40 vues au départ, puis 1,8 à 9,2 par jour
     * depuis la publication) pour rester plausibles les uns par rapport aux
     * autres. Le rythme est tiré au centième près et complété d'un petit écart
     * final : beaucoup d'articles partagent la même date de publication, et un
     * rythme en nombres entiers leur donnerait à tous le même total.
     *
     * On passe par une migration et non par un seeder : le déploiement lance
     * `php artisan migrate`, jamais `db:seed`. Ne touche que les articles encore
     * à 0, donc rejouable sans écraser du trafic réel.
     */
    public function up(): void
    {
        Article::query()
            ->where('views_count', 0)
            ->get(['id', 'published_at', 'created_at'])
            ->each(function (Article $article) {
                $since = $article->published_at ?? $article->created_at;
                $days  = $since ? max(0, (int) $since->diffInDays(now())) : 0;

                $views = 40 + intdiv($days * random_int(180, 920), 100) + random_int(0, 30);

                Article::whereKey($article->id)->update(['views_count' => $views]);
            });
    }

    public function down(): void
    {
        // Pas de retour en arrière : on ne saurait pas distinguer les vues
        // de départ du trafic réel accumulé depuis.
    }
};
