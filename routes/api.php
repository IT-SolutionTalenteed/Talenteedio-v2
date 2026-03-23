<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\MediaCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Talent\DashboardController as TalentDashboardController;
use App\Http\Controllers\Entreprise\DashboardController as EntrepriseDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Routes publiques d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

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