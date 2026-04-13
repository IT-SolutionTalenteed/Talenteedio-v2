#!/bin/bash
set -euo pipefail

DEPLOY_DIR="/home/talenteedio/website/backend"
PREVIOUS_COMMIT_FILE="/tmp/talenteed_backend_previous_commit"

cd "$DEPLOY_DIR"

echo "⏪ Starting backend rollback..."
echo "   Date : $(date)"
echo "   Current commit: $(git rev-parse --short HEAD)"

# ── Récupérer le commit précédent ─────────────────────────────────────────
if [ ! -f "$PREVIOUS_COMMIT_FILE" ]; then
  echo "❌ No rollback target found at $PREVIOUS_COMMIT_FILE"
  echo "   Falling back to HEAD~1"
  PREVIOUS_COMMIT=$(git rev-parse HEAD~1)
else
  PREVIOUS_COMMIT=$(cat "$PREVIOUS_COMMIT_FILE")
fi

echo "   Rolling back to: $PREVIOUS_COMMIT"

# ── Rollback code ─────────────────────────────────────────────────────────
git reset --hard "$PREVIOUS_COMMIT" \
  || { echo "❌ Git reset to $PREVIOUS_COMMIT failed"; exit 1; }

echo "   Code rolled back to: $(git rev-parse --short HEAD)"

# ── Rétablir les dépendances ──────────────────────────────────────────────
echo ""
echo "📦 Restoring composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction \
  || { echo "❌ Composer install failed during rollback"; exit 1; }

# ── Caches ────────────────────────────────────────────────────────────────
echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

echo ""
echo "✅ Rollback completed. Running on commit: $(git rev-parse --short HEAD)"
