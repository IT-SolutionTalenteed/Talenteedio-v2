<?php

use App\Http\Controllers\SocialPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
 * Aperçus de partage (Open Graph) servis aux robots des réseaux sociaux.
 * Nginx y redirige les requêtes des crawlers sur /blog/{id}, /annonces/{id},
 * /evenements/{id} et /entreprises/{id} ; les humains vont sur la SPA.
 */
Route::get('/og/{type}/{id}', [SocialPreviewController::class, 'show'])
    ->where('type', 'blog|annonces|evenements|entreprises')
    ->where('id', '[A-Za-z0-9\-_]+');

Route::get('/.well-known/assetlinks.json', function () {
    return response()->file(public_path('.well-known/assetlinks.json'), [
        'Content-Type' => 'application/json',
    ]);
});
