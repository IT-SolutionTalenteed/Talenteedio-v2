<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Entreprise;
use App\Models\Evenement;
use App\Models\Offre;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

/**
 * Aperçus de partage (Open Graph) pour les réseaux sociaux.
 *
 * Le front est une SPA Vue : les meta tags posés en JavaScript ne sont pas lus
 * par les robots de Facebook, WhatsApp, LinkedIn, X, Slack... qui n'exécutent
 * pas de JS. Sans cette page, tout lien partagé affiche les meta tags
 * génériques du index.html.
 *
 * Nginx redirige les requêtes de ces robots vers ces routes (voir nginx.conf),
 * les visiteurs humains continuent d'aller sur la SPA normalement.
 */
class SocialPreviewController extends Controller
{
    /**
     * Longueur maximale d'une description Open Graph avant troncature.
     */
    private const DESCRIPTION_LENGTH = 200;

    public function show(string $type, string $id): View
    {
        $preview = match ($type) {
            'blog'        => $this->article($id),
            'annonces'    => $this->offre($id),
            'evenements'  => $this->evenement($id),
            'entreprises' => $this->entreprise($id),
            default       => null,
        };

        $preview ??= $this->fallback();

        // Un contenu sans description (entreprise sans texte, etc.) affiche la
        // baseline du site plutôt qu'un aperçu vide.
        $preview['description'] = $preview['description'] ?: $this->fallback()['description'];

        $preview['url']   = config('frontend.url') . '/' . $type . '/' . $id;
        $preview['image'] = $this->publicImageUrl($preview['image']) ?: $this->defaultImage();

        return view('social-preview', $preview);
    }

    /**
     * @return array{title: string, description: string, image: ?string, type: string}|null
     */
    private function article(string $id): ?array
    {
        $article = Article::find($id);

        if (!$article || !$article->is_published || $article->isArchived()) {
            return null;
        }

        return [
            'title'       => $article->title,
            'description' => $this->excerpt($article->content),
            'image'       => $article->image_url,
            'type'        => 'article',
        ];
    }

    private function offre(string $id): ?array
    {
        $offre = Offre::with('entreprise:id,nom,logo')->find($id);

        if (!$offre || $offre->isArchived()) {
            return null;
        }

        return [
            'title'       => $offre->titre,
            'description' => $this->excerpt($offre->mission ?: $offre->description ?: $offre->profil_recherche),
            'image'       => $offre->image_url ?: $offre->entreprise?->logo_url,
            'type'        => 'article',
        ];
    }

    private function evenement(string $id): ?array
    {
        $evenement = Evenement::find($id);

        if (!$evenement) {
            return null;
        }

        return [
            'title'       => $evenement->titre,
            'description' => $this->excerpt($evenement->description ?: $evenement->details_supplementaires),
            'image'       => $evenement->image_mise_en_avant_url,
            'type'        => 'article',
        ];
    }

    private function entreprise(string $id): ?array
    {
        $entreprise = Entreprise::find($id);

        if (!$entreprise || $entreprise->status === 'suspended') {
            return null;
        }

        return [
            'title'       => $entreprise->nom,
            'description' => $this->excerpt($entreprise->description),
            'image'       => $entreprise->logo_url,
            'type'        => 'profile',
        ];
    }

    /**
     * Contenu générique quand la ressource n'existe plus ou n'est pas publique.
     */
    private function fallback(): array
    {
        return [
            'title'       => 'Talenteedio',
            'description' => "Talenteedio est la première plateforme stratégique européenne dédiée à la mobilisation de l'intelligence collective et des compétences rares de la diaspora.",
            'image'       => null,
            'type'        => 'website',
        ];
    }

    private function defaultImage(): string
    {
        return config('frontend.url') . '/favicon.png';
    }

    /**
     * Les accesseurs des modèles construisent les URL d'images à partir du host
     * du backend (et en http derrière le proxy nginx). Les robots sociaux
     * refusent une image en http sur un site https : on réécrit les fichiers
     * de /storage sur le domaine public, que nginx proxifie déjà vers Laravel.
     */
    private function publicImageUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (!$path || !str_starts_with($path, '/storage/')) {
            return $url;
        }

        return config('frontend.url') . $path;
    }

    /**
     * Transforme du contenu HTML (CKEditor) en texte plat tronqué.
     */
    private function excerpt(?string $html): string
    {
        if (!$html) {
            return '';
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        return Str::limit($text, self::DESCRIPTION_LENGTH);
    }
}
