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

echo "Backend deployment completed successfully. 🎉🎉🎉"
