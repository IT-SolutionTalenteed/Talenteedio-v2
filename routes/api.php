<?php

use App\Http\Controllers\AuthController;
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
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Talent\DashboardController as TalentDashboardController;
use App\Http\Controllers\Entreprise\DashboardController as EntrepriseDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Routes publiques d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes publiques (footer, etc.)
Route::get('/legal-pages', [LegalPageController::class, 'index']);

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
    });
    
    Route::middleware('role:talent')->prefix('talent')->group(function () {
        Route::get('/dashboard', [TalentDashboardController::class, 'index']);
        
        // Les talents peuvent voir les catégories actives
        Route::get('/media-categories', [MediaCategoryController::class, 'active']);
    });
    
    Route::middleware('role:entreprise')->prefix('entreprise')->group(function () {
        Route::get('/dashboard', [EntrepriseDashboardController::class, 'index']);
        
        // Les entreprises peuvent voir les catégories actives
        Route::get('/media-categories', [MediaCategoryController::class, 'active']);
    });
});