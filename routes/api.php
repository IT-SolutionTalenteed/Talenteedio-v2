<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\MediaCategoryController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\JobContractController;
use App\Http\Controllers\Admin\JobModeController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\StudyLevelController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\ActivitySectorController;
use App\Http\Controllers\Admin\LegalPageController;
use App\Http\Controllers\Admin\OffreController;
use App\Http\Controllers\Admin\CategorieEvenementController;
use App\Http\Controllers\Admin\EvenementController;
use App\Http\Controllers\Admin\EntrepriseController;
use App\Http\Controllers\Admin\TalentController;
use App\Http\Controllers\Admin\EntretienController as AdminEntretienController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\HubSpotController as AdminHubSpotController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\TemoignageController as AdminTemoignageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Talent\DashboardController as TalentDashboardController;
use App\Http\Controllers\Talent\OffreController as TalentOffreController;
use App\Http\Controllers\Talent\FavoriController as TalentFavoriController;
use App\Http\Controllers\Talent\EvenementController as TalentEvenementController;
use App\Http\Controllers\Talent\EntretienController as TalentEntretienController;
use App\Http\Controllers\Talent\FeedbackController as TalentFeedbackController;
use App\Http\Controllers\Entreprise\DashboardController as EntrepriseDashboardController;
use App\Http\Controllers\Entreprise\OffreController as EntrepriseOffreController;
use App\Http\Controllers\Entreprise\CandidatureController as EntrepriseCandidatureController;
use App\Http\Controllers\Entreprise\EvenementController as EntrepriseEvenementController;
use App\Http\Controllers\Entreprise\ArticleController as EntrepriseArticleController;
use App\Http\Controllers\Entreprise\EntretienController as EntrepriseEntretienController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Routes publiques d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Google OAuth (Socialite)
Route::get('/auth/google/redirect',  [SocialiteController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback',  [SocialiteController::class, 'handleGoogleCallback']);

// Routes publiques (footer, etc.)
Route::get('/legal-pages', [LegalPageController::class, 'index']);
Route::get('/skills', [SkillController::class, 'index']);

// Routes publiques — site visiteurs
Route::prefix('public')->group(function () {
    Route::get('/featured-event',                             [PublicController::class, 'featuredEvent']);
    Route::get('/evenements/{evenement}',                     [PublicController::class, 'evenementDetail']);
    Route::get('/offres',                                     [PublicController::class, 'offres']);
    Route::get('/offres-home',                                [PublicController::class, 'offresHome']);
    Route::get('/entreprises',                                [PublicController::class, 'entreprises']);
    Route::get('/categories-evenements',                      [PublicController::class, 'categoriesEvenements']);
    Route::get('/categories-evenements/{categorieEvenement}', [PublicController::class, 'categorieEvenement']);
    Route::get('/articles',                                   [PublicController::class, 'articles']);
    Route::get('/articles/{article}',                         [PublicController::class, 'articleDetail']);
    Route::get('/offres/{offre}',                             [PublicController::class, 'offreDetail']);
    Route::get('/entreprises/{entreprise}',                   [PublicController::class, 'entrepriseDetail']);
    Route::get('/referentiels',                               [PublicController::class, 'referentiels']);
});

// Routes protégées par Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Routes spécifiques aux rôles
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        
        // Gestion des utilisateurs (admin seulement)
        Route::apiResource('users', UserController::class);
        Route::get('/roles', [UserController::class, 'roles']);
        
        // Gestion des catégories de média (admin seulement)
        Route::apiResource('media-categories', MediaCategoryController::class);
        Route::patch('/media-categories/{mediaCategory}/toggle-status', [MediaCategoryController::class, 'toggleStatus']);
        Route::get('/media-categories-active', [MediaCategoryController::class, 'active']);
        
        // Gestion des articles (admin seulement)
        Route::apiResource('articles', ArticleController::class);
        Route::get('/articles-media-categories', [ArticleController::class, 'getMediaCategories']);

        // Référentiels
        Route::apiResource('job-contracts', JobContractController::class);
        Route::apiResource('job-modes', JobModeController::class);
        Route::apiResource('skills', SkillController::class);
        Route::apiResource('study-levels', StudyLevelController::class);
        Route::apiResource('experiences', ExperienceController::class);
        Route::apiResource('languages', LanguageController::class);
        Route::apiResource('activity-sectors', ActivitySectorController::class);
        Route::apiResource('legal-pages', LegalPageController::class);

        // Offres d'emploi
        Route::apiResource('offres', OffreController::class);
        Route::get('/offres-referentiels', [OffreController::class, 'referentiels']);

        // Événements
        Route::apiResource('categorie-evenements', CategorieEvenementController::class);
        Route::delete('/categorie-evenements/{categorieEvenement}/galerie', [CategorieEvenementController::class, 'removeGalerieItem']);
        Route::apiResource('evenements', EvenementController::class);
        Route::patch('/evenements/{evenement}/toggle-featured', [EvenementController::class, 'toggleFeatured']);
        Route::get('/evenements-referentiels', [EvenementController::class, 'referentiels']);

        // Gestion des entreprises
        Route::apiResource('entreprises', EntrepriseController::class);
        Route::get('/entreprises-referentiels', [EntrepriseController::class, 'referentiels']);

        // Gestion des talents
        Route::get('/talents', [TalentController::class, 'index']);
        Route::get('/talents/{user}', [TalentController::class, 'show']);
        Route::patch('/talents/{user}/profil', [TalentController::class, 'updateProfil']);
        Route::patch('/talents/{user}/suspend', [TalentController::class, 'toggleSuspend']);
        Route::patch('/talents/{user}/ban', [TalentController::class, 'toggleBan']);
        Route::patch('/talents/{user}/statut-crm', [TalentController::class, 'updateStatutCrm']);
        Route::delete('/talents/{user}', [TalentController::class, 'destroy']);

        // Entretiens par stand (D-05)
        Route::get('/entretiens', [AdminEntretienController::class, 'index']);
        Route::get('/entretiens-evenements', [AdminEntretienController::class, 'evenementsList']);

        // Feedbacks (D-06)
        Route::get('/feedbacks', [AdminFeedbackController::class, 'index']);

        // Import XLS (H-08)
        Route::post('/import/candidats', [ImportController::class, 'importCandidats']);

        // HubSpot CRM (I-06)
        Route::get('/hubspot/status',  [AdminHubSpotController::class, 'status']);
        Route::post('/hubspot/sync',   [AdminHubSpotController::class, 'sync']);
        Route::post('/hubspot/setup',  [AdminHubSpotController::class, 'setup']);

        // Témoignages (réutilisables)
        Route::get('/temoignages', [AdminTemoignageController::class, 'index']);
        Route::post('/temoignages', [AdminTemoignageController::class, 'store']);
        Route::post('/temoignages/{temoignage}', [AdminTemoignageController::class, 'update']);
        Route::delete('/temoignages/{temoignage}', [AdminTemoignageController::class, 'destroy']);
        Route::post('/categorie-evenements/{categorieEvenement}/temoignages', [AdminTemoignageController::class, 'attach']);
        Route::delete('/categorie-evenements/{categorieEvenement}/temoignages/{temoignage}', [AdminTemoignageController::class, 'detach']);
    });
    
    Route::middleware('role:talent')->prefix('talent')->group(function () {
        Route::get('/dashboard', [TalentDashboardController::class, 'index']);

        // Offres d'emploi (consultation + candidature) (G-01)
        Route::get('/offres', [TalentOffreController::class, 'index']);
        Route::get('/offres/{offre}', [TalentOffreController::class, 'show']);
        Route::post('/offres/{offre}/postuler', [TalentOffreController::class, 'postuler']);
        Route::get('/mes-candidatures', [TalentOffreController::class, 'mesCandidatures']);

        // Favoris offres (G-02)
        Route::get('/favoris', [TalentFavoriController::class, 'index']);
        Route::post('/offres/{offre}/favori', [TalentFavoriController::class, 'toggle']);

        // Événements + matching OpenAI (G-03)
        Route::get('/evenements', [TalentEvenementController::class, 'index']);
        Route::post('/evenements/{evenement}/matching', [TalentEvenementController::class, 'matching']);

        // Réservation entretiens créneaux 15min (G-04)
        Route::get('/evenements/{evenement}/creneaux', [TalentEntretienController::class, 'creneaux']);
        Route::post('/evenements/{evenement}/reserver', [TalentEntretienController::class, 'reserver']);
        Route::get('/mes-entretiens', [TalentEntretienController::class, 'mesEntretiens']);
        Route::patch('/entretiens/{entretien}/annuler', [TalentEntretienController::class, 'annuler']);

        // Feedbacks post-entretien (G-05)
        Route::get('/mes-feedbacks', [TalentFeedbackController::class, 'index']);
        Route::post('/entretiens/{entretien}/feedback', [TalentFeedbackController::class, 'store']);
        Route::put('/feedbacks/{feedback}', [TalentFeedbackController::class, 'update']);
        Route::delete('/feedbacks/{feedback}', [TalentFeedbackController::class, 'destroy']);
    });
    
    Route::middleware('role:entreprise')->prefix('entreprise')->group(function () {
        Route::get('/dashboard', [EntrepriseDashboardController::class, 'index']);

        // Offres d'emploi (F-01)
        Route::apiResource('offres', EntrepriseOffreController::class);
        Route::get('/offres-referentiels', [EntrepriseOffreController::class, 'referentiels']);

        // Candidatures reçues (F-02)
        Route::get('/candidatures', [EntrepriseCandidatureController::class, 'index']);
        Route::patch('/candidatures/{candidature}/statut', [EntrepriseCandidatureController::class, 'updateStatut']);

        // Événements — demande de participation (F-03)
        Route::get('/evenements', [EntrepriseEvenementController::class, 'index']);
        Route::post('/evenements/{evenement}/demande', [EntrepriseEvenementController::class, 'demandeParticipation']);
        Route::get('/mes-demandes', [EntrepriseEvenementController::class, 'mesDemandes']);

        // Entretiens stand (F-05)
        Route::get('/entretiens', [EntrepriseEntretienController::class, 'index']);
        Route::patch('/entretiens/{entretien}/statut', [EntrepriseEntretienController::class, 'updateStatut']);

        // Articles (F-04)
        Route::apiResource('articles', EntrepriseArticleController::class);
        Route::get('/articles-referentiels', [EntrepriseArticleController::class, 'referentiels']);
    });
});