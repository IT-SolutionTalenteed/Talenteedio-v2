#!/bin/bash
set -euo pipefail

echo "🚀 Starting backend deployment..."

git fetch origin
git reset --hard origin/main
git pull origin main

# Après git pull, le fichier deploy_prod.sh est à jour sur le disque,
# mais le processus bash en cours exécute encore l'ancienne version en mémoire.
# On relance une fois pour appliquer le script fraîchement récupéré.
if [[ "${DEPLOY_BOOTSTRAPPED:-}" != "1" ]]; then
  export DEPLOY_BOOTSTRAPPED=1
  exec env DEPLOY_BOOTSTRAPPED=1 bash "$0"
fi

echo "   Commit déployé : $(git rev-parse --short HEAD)"

composer install --no-dev --optimize-autoloader

php artisan migrate --force

echo "🔄 Déduplication des secteurs d'activité (doublons)..."
php artisan activity-sectors:deduplicate

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "📁 Checking storage permissions..."
chmod -R 775 storage/app/public
mkdir -p storage/app/public/evenements
chmod 775 storage/app/public/evenements

echo "🔍 Checking PHP configuration..."
php scripts/check_php_config.php

echo ""
echo "Backend deployment completed successfully. 🎉🎉🎉"
echo ""
echo "⚠️  Important: If you have upload issues, check:"
echo "   - https://talenteed.io/api/diagnostic/config"
echo "   - Restart PHP-FPM: sudo systemctl restart php8.2-fpm"
echo "   - See FIX_UPLOAD_PRODUCTION.md for details"
