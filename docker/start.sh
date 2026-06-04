#!/bin/sh
set -e

echo "=== Setting up storage ==="
mkdir -p /app/storage/framework/{sessions,views,cache,testing}
mkdir -p /app/storage/app/public
mkdir -p /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

echo "=== Caching config ==="
php /app/artisan config:cache --no-interaction --quiet 2>/dev/null || true
php /app/artisan route:cache --no-interaction --quiet 2>/dev/null || true
php /app/artisan view:cache --no-interaction --quiet 2>/dev/null || true
php /app/artisan event:cache --no-interaction --quiet 2>/dev/null || true

echo "=== Running migrations ==="
php /app/artisan migrate --force --no-interaction --quiet 2>/dev/null || \
    echo "WARNING: Migration failed — will retry on next deploy"

echo "=== Starting supervisor ==="
exec /usr/bin/supervisord -c /etc/supervisord.conf
