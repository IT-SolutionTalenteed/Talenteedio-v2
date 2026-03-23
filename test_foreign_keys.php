<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\MediaCategory;
use Illuminate\Support\Facades\DB;

echo "=== Test des Foreign Keys ===\n\n";

try {
    // Test 1: Créer un admin
    echo "1. Test de création d'un admin:\n";
    $admin = User::create([
        'name' => 'Admin Test FK',
        'email' => 'admin.fk@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin'
    ]);
    echo "✅ Admin créé avec ID: " . $admin->id . "\n\n";

    // Test 2: Créer une catégorie avec FK valide
    echo "2. Test de création d'une catégorie avec FK valide:\n";
    $category = MediaCategory::create([
        'name' => 'Test FK Category',
        'description' => 'Test de foreign key',
        'created_by' => $admin->id
    ]);
    echo "✅ Catégorie créée avec ID: " . $category->id . "\n";
    echo "   Créée par l'admin ID: " . $category->created_by . "\n\n";

    // Test 3: Vérifier la relation
    echo "3. Test de la relation:\n";
    $creator = $category->creator;
    echo "✅ Créateur trouvé: " . $creator->name . " (" . $creator->email . ")\n\n";

    // Test 4: Test de la relation inverse
    echo "4. Test de la relation inverse (admin -> catégories):\n";
    $adminCategories = $admin->mediaCategories;
    echo "✅ L'admin a créé " . $adminCategories->count() . " catégorie(s)\n";
    foreach ($adminCategories as $cat) {
        echo "   - " . $cat->name . "\n";
    }
    echo "\n";

    // Test 5: Tentative de création avec FK invalide (doit échouer)
    echo "5. Test avec FK invalide (doit échouer):\n";
    try {
        MediaCategory::create([
            'name' => 'Invalid FK Category',
            'description' => 'Test avec FK invalide',
            'created_by' => 99999 // ID qui n'existe pas
        ]);
        echo "❌ ERREUR: La création aurait dû échouer!\n";
    } catch (Exception $e) {
        echo "✅ Échec attendu: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 6: Test CASCADE DELETE
    echo "6. Test CASCADE DELETE:\n";
    $categoryCount = MediaCategory::where('created_by', $admin->id)->count();
    echo "Catégories avant suppression: " . $categoryCount . "\n";
    
    $admin->delete();
    
    $categoryCountAfter = MediaCategory::where('created_by', $admin->id)->count();
    echo "Catégories après suppression de l'admin: " . $categoryCountAfter . "\n";
    
    if ($categoryCountAfter == 0) {
        echo "✅ CASCADE DELETE fonctionne correctement\n";
    } else {
        echo "❌ ERREUR: CASCADE DELETE ne fonctionne pas\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur lors des tests: " . $e->getMessage() . "\n";
}

echo "\n=== Tests des Foreign Keys terminés ===\n";
echo "\nRésumé des contraintes:\n";
echo "- media_categories.created_by → users.id (CASCADE)\n";
echo "- Validation au niveau application pour users.role\n";
echo "- Index automatiques sur les FK\n";