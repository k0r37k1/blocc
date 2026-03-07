#!/usr/bin/env bash
set -euo pipefail

# blocc - Deployment Script
# Usage: ./deploy.sh [--fresh]
# --fresh: Runs fresh migration + seed (DESTROYS DATA)

FRESH=false
if [[ "${1:-}" == "--fresh" ]]; then
    FRESH=true
fi

echo "==> blocc deploy starting..."

# Pull latest code
echo "==> Pulling latest changes..."
git pull origin main

# Install/update PHP dependencies
echo "==> Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Run migrations
if [[ "$FRESH" == true ]]; then
    echo "==> Running FRESH migrations + seed..."
    php artisan migrate:fresh --seed --force
else
    echo "==> Running migrations..."
    php artisan migrate --force
fi

# Clear and rebuild caches
echo "==> Optimizing application..."
php artisan optimize:clear
php artisan optimize
php artisan filament:optimize
php artisan view:cache
php artisan event:cache

# Link storage
php artisan storage:link 2>/dev/null || true

# Set permissions
echo "==> Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "==> Deploy complete!"
