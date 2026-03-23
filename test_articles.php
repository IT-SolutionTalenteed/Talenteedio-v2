<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Bootstrap Laravel
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        api: __DIR__.'/routes/api.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Article;
use App\Models\User;
use App\Models\MediaCategory;

echo "=== Test des Articles ===\n\n";

try {
    // Test 1: Récupérer tous les articles avec leurs relations
    echo "1. Articles avec leurs relations :\n";
    $articles = Article::with(['user', 'mediaCategories'])->get();
    
    foreach ($articles as $article) {
        echo "- {$article->title} (par {$article->user->name})\n";
        echo "  Publié: " . ($article->is_published ? 'Oui' : 'Non') . "\n";
        echo "  Catégories: " . $article->mediaCategories->pluck('name')->join(', ') . "\n";
        echo "  Slug: {$article->slug}\n\n";
    }

    // Test 2: Articles d'un utilisateur spécifique
    echo "2. Articles par utilisateur :\n";
    $admin = User::where('role', 'admin')->first();
    if ($admin) {
        $userArticles = $admin->articles()->with('mediaCategories')->get();
        echo "Articles de {$admin->name}: {$userArticles->count()}\n";
        foreach ($userArticles as $article) {
            echo "- {$article->title}\n";
        }
    }
    echo "\n";

    // Test 3: Articles par catégorie média
    echo "3. Articles par catégorie média :\n";
    $categories = MediaCategory::with('articles')->get();
    foreach ($categories as $category) {
        echo "Catégorie '{$category->name}': {$category->articles->count()} articles\n";
        foreach ($category->articles as $article) {
            echo "- {$article->title}\n";
        }
        echo "\n";
    }

    // Test 4: Articles publiés seulement
    echo "4. Articles publiés :\n";
    $publishedArticles = Article::where('is_published', true)->get();
    echo "Nombre d'articles publiés: {$publishedArticles->count()}\n";
    foreach ($publishedArticles as $article) {
        echo "- {$article->title}\n";
    }

    echo "\n=== Tests terminés avec succès ! ===\n";

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}