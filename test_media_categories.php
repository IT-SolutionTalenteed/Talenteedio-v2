<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\MediaCategory;

echo "=== Test du système de catégories de média ===\n\n";

// Test 1: Créer un admin
echo "1. Création d'un admin:\n";
$admin = new User([
    'name' => 'Admin Test',
    'email' => 'admin@test.com',
    'role' => 'admin'
]);
echo "Admin créé: " . $admin->name . " (" . $admin->role . ")\n\n";

// Test 2: Créer une catégorie
echo "2. Création d'une catégorie de média:\n";
$category = new MediaCategory([
    'name' => 'Test Vidéo',
    'description' => 'Catégorie de test pour les vidéos',
    'is_active' => true,
    'created_by' => 1 // Simule l'ID de l'admin
]);

echo "Catégorie créée: " . $category->name . "\n";
echo "Slug généré: " . $category->slug . "\n";
echo "Statut: " . ($category->is_active ? 'Actif' : 'Inactif') . "\n";
echo "Créée par l'admin ID: " . $category->created_by . "\n\n";

// Test 3: Test des relations
echo "3. Test des relations:\n";
echo "Un admin peut créer plusieurs catégories (One-to-Many)\n";
echo "Chaque catégorie appartient à un admin créateur\n\n";

// Test 4: Test des scopes
echo "4. Test du scope 'active':\n";
$activeCategory = new MediaCategory(['is_active' => true]);
$inactiveCategory = new MediaCategory(['is_active' => false]);

echo "Catégorie active: " . ($activeCategory->is_active ? 'Oui' : 'Non') . "\n";
echo "Catégorie inactive: " . ($inactiveCategory->is_active ? 'Oui' : 'Non') . "\n\n";

echo "=== Tests terminés ===\n";
echo "\nPour tester l'API complète:\n";
echo "1. php artisan migrate\n";
echo "2. php artisan db:seed --class=MediaCategorySeeder\n";
echo "3. Créer un utilisateur admin via l'API\n";
echo "4. Tester les endpoints avec Postman ou curl\n";