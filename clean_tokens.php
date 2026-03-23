<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Laravel\Sanctum\PersonalAccessToken;

echo "Nettoyage des tokens Sanctum...\n";

$count = PersonalAccessToken::count();
echo "Tokens avant nettoyage: $count\n";

PersonalAccessToken::truncate();

echo "Tous les tokens ont été supprimés.\n";
echo "Vous devez vous reconnecter.\n";