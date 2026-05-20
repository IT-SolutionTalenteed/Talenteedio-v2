#!/bin/bash

echo "🚀 Starting backend deployment..."

git fetch origin
git reset --hard origin/main
git pull origin main

composer install --no-dev --optimize-autoloader

php artisan migrate --force
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
