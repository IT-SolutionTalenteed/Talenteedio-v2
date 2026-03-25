<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Article;
use App\Models\Offre;

class PublicController extends Controller
{
    /**
     * Événement mis en avant (is_featured = true).
     */
    public function featuredEvent()
    {
        $event = Evenement::with(['entreprises:id,nom,logo', 'categorie:id,titre'])
            ->where('is_featured', true)
            ->latest()
            ->first();

        return response()->json($event);
    }

    /**
     * 3 derniers articles publiés.
     */
    public function articles()
    {
        $articles = Article::with('mediaCategories:id,name')
            ->where('is_published', true)
            ->latest()
            ->take(3)
            ->get(['id', 'title', 'content', 'image', 'created_at']);

        return response()->json($articles);
    }

    /**
     * 3 dernières offres d'emploi.
     */
    public function offres()
    {
        $offres = Offre::with('entreprise:id,nom,logo')
            ->latest()
            ->take(3)
            ->get(['id', 'titre', 'localisation', 'mission', 'date_limite', 'entreprise_id']);

        return response()->json($offres);
    }
}