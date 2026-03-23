<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\SimpleRole;

// Test du nouveau système de rôles
echo "=== Test du système de rôles simplifié ===\n\n";

// Test 1: Créer un utilisateur avec un rôle
echo "1. Test de création d'utilisateur avec rôle:\n";
$user = new User([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'role' => 'admin'
]);

echo "Utilisateur créé avec le rôle: " . $user->role . "\n";
echo "hasRole('admin'): " . ($user->hasRole('admin') ? 'true' : 'false') . "\n";
echo "hasRole('talent'): " . ($user->hasRole('talent') ? 'true' : 'false') . "\n\n";

// Test 2: Test hasAnyRole
echo "2. Test hasAnyRole:\n";
echo "hasAnyRole(['admin', 'talent']): " . ($user->hasAnyRole(['admin', 'talent']) ? 'true' : 'false') . "\n";
echo "hasAnyRole(['talent', 'entreprise']): " . ($user->hasAnyRole(['talent', 'entreprise']) ? 'true' : 'false') . "\n\n";

// Test 3: Changement de rôle
echo "3. Test de changement de rôle:\n";
$user->role = 'talent';
echo "Nouveau rôle: " . $user->role . "\n";
echo "hasRole('admin'): " . ($user->hasRole('admin') ? 'true' : 'false') . "\n";
echo "hasRole('talent'): " . ($user->hasRole('talent') ? 'true' : 'false') . "\n\n";

echo "=== Tests terminés ===\n";