<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
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
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Tableau de bord admin']);
        });
        
        // Gestion des utilisateurs (admin seulement)
        Route::apiResource('users', UserController::class);
        Route::get('/roles', [UserController::class, 'roles']);
    });
    
    Route::middleware('role:talent')->group(function () {
        Route::get('/talent/dashboard', function () {
            return response()->json(['message' => 'Tableau de bord talent']);
        });
    });
    
    Route::middleware('role:entreprise')->group(function () {
        Route::get('/entreprise/dashboard', function () {
            return response()->json(['message' => 'Tableau de bord entreprise']);
        });
    });
});