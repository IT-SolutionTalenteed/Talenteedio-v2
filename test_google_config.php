<?php

/**
 * Script de test de la configuration Google OAuth
 * Usage: php test_google_config.php
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== Test de configuration Google OAuth ===\n\n";

// Vérifier les variables d'environnement
$checks = [
    'GOOGLE_CLIENT_ID' => env('GOOGLE_CLIENT_ID'),
    'GOOGLE_CLIENT_SECRET' => env('GOOGLE_CLIENT_SECRET'),
    'GOOGLE_REDIRECT_URI' => env('GOOGLE_REDIRECT_URI'),
    'FRONTEND_URL' => env('FRONTEND_URL'),
];

$hasErrors = false;

foreach ($checks as $key => $value) {
    $status = $value ? '✓' : '✗';
    $color = $value ? "\033[32m" : "\033[31m";
    $reset = "\033[0m";
    
    echo "{$color}{$status}{$reset} {$key}: ";
    
    if ($value) {
        // Masquer les secrets
        if (strpos($key, 'SECRET') !== false) {
            echo str_repeat('*', 20) . "\n";
        } else {
            echo $value . "\n";
        }
    } else {
        echo "NON CONFIGURÉ\n";
        $hasErrors = true;
    }
}

echo "\n=== Vérification des URLs ===\n\n";

// Vérifier le format des URLs
$frontendUrl = env('FRONTEND_URL');
if ($frontendUrl) {
    if (substr($frontendUrl, -1) === '/') {
        echo "\033[33m⚠\033[0m  FRONTEND_URL se termine par un slash (/) - Cela peut causer des problèmes\n";
        echo "   Valeur actuelle: {$frontendUrl}\n";
        echo "   Valeur recommandée: " . rtrim($frontendUrl, '/') . "\n";
        $hasErrors = true;
    } else {
        echo "\033[32m✓\033[0m FRONTEND_URL correctement formatée\n";
    }
}

$redirectUri = env('GOOGLE_REDIRECT_URI');
if ($redirectUri) {
    if (strpos($redirectUri, '/api/auth/google/callback') !== false) {
        echo "\033[32m✓\033[0m GOOGLE_REDIRECT_URI correctement formatée\n";
    } else {
        echo "\033[33m⚠\033[0m  GOOGLE_REDIRECT_URI ne contient pas le bon chemin\n";
        echo "   Attendu: .../api/auth/google/callback\n";
        echo "   Actuel: {$redirectUri}\n";
    }
}

echo "\n=== URLs de callback générées ===\n\n";

$frontendBase = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
$exampleCallbackUrl = "{$frontendBase}/auth/google/callback";

echo "URL de callback frontend: {$exampleCallbackUrl}\n";

// Vérifier les doubles slashes
if (strpos($exampleCallbackUrl, '//auth') !== false) {
    echo "\033[31m✗\033[0m ERREUR: Double slash détecté dans l'URL!\n";
    $hasErrors = true;
} else {
    echo "\033[32m✓\033[0m Pas de double slash détecté\n";
}

echo "\n=== Résumé ===\n\n";

if ($hasErrors) {
    echo "\033[31m✗ Des problèmes ont été détectés dans la configuration\033[0m\n";
    echo "Veuillez corriger les erreurs ci-dessus avant de tester l'authentification Google.\n";
    exit(1);
} else {
    echo "\033[32m✓ Configuration Google OAuth correcte!\033[0m\n";
    echo "Vous pouvez maintenant tester l'authentification Google.\n";
    exit(0);
}
