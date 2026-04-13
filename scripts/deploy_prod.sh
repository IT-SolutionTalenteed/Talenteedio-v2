#!/bin/bash
set -euo pipefail

DEPLOY_DIR="/home/talenteedio/website/backend"
BACKUP_BRANCH="deploy-backup-$(date +%Y%m%d%H%M%S)"

cd "$DEPLOY_DIR"

echo "🚀 Starting backend deployment..."
echo "   Dir  : $DEPLOY_DIR"
echo "   Date : $(date)"
echo "   Commit before: $(git rev-parse --short HEAD)"

# ── Sauvegarde du commit actuel pour rollback ──────────────────────────────
PREVIOUS_COMMIT=$(git rev-parse HEAD)
echo "$PREVIOUS_COMMIT" > /tmp/talenteed_backend_previous_commit
echo "   Rollback target saved: $PREVIOUS_COMMIT"

# ── Mise à jour du code ────────────────────────────────────────────────────
echo ""
echo "📥 Pulling latest code..."
git fetch origin || { echo "❌ Git fetch failed"; exit 1; }
git reset --hard origin/main || { echo "❌ Git reset failed"; exit 1; }
echo "   Commit after : $(git rev-parse --short HEAD)"

# ── Dépendances ────────────────────────────────────────────────────────────
echo ""
echo "📦 Installing composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction \
  || { echo "❌ Composer install failed"; exit 1; }

# ── Migrations ────────────────────────────────────────────────────────────
echo ""
echo "🗄️  Running migrations..."
php artisan migrate --force \
  || { echo "❌ Migration failed — rollback may be needed"; exit 1; }

# ── Cache ─────────────────────────────────────────────────────────────────
echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

# ── Permissions storage ────────────────────────────────────────────────────
echo ""
echo "📁 Checking storage permissions..."
chmod -R 775 storage/app/public
mkdir -p storage/app/public/evenements
chmod 775 storage/app/public/evenements

# ── PHP config check ──────────────────────────────────────────────────────
echo ""
echo "🔍 Checking PHP configuration..."
php scripts/check_php_config.php

echo ""
echo "✅ Backend deployment completed successfully. 🎉"
echo "   Deployed commit: $(git rev-parse --short HEAD)"
echo ""
echo "⚠️  Important: If you have upload issues, check:"
echo "   - https://talenteed.io/api/diagnostic/config"
echo "   - Restart PHP-FPM: sudo systemctl restart php8.2-fpm"
echo "   - See FIX_UPLOAD_PRODUCTION.md for details"
