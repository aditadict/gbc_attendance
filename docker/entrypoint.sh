#!/bin/bash
set -e

echo "[entrypoint] Fixing storage permissions..."
mkdir -p storage/app/public \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "[entrypoint] Running migrations..."
php artisan migrate --force

echo "[entrypoint] Linking public storage..."
php artisan storage:link 2>/dev/null || true

echo "[entrypoint] Caching config & routes..."
php artisan config:cache
php artisan route:cache
# NOTE: view:cache dihapus — tidak kompatibel dengan Filament component registry

# Gunakan Octane (Swoole) jika laravel/octane terinstall, fallback ke artisan serve
if php artisan help octane:start > /dev/null 2>&1; then
    echo "[entrypoint] Publishing Octane config..."
    php artisan vendor:publish --tag=octane-config --no-interaction 2>/dev/null || true

    echo "[entrypoint] Starting Laravel Octane (Swoole)..."
    exec su-exec www-data php artisan octane:start \
        --server=swoole \
        --host=0.0.0.0 \
        --port=8000 \
        --workers=4 \
        --task-workers=2
else
    echo "[entrypoint] Octane not installed — starting php artisan serve..."
    exec su-exec www-data php artisan serve \
        --host=0.0.0.0 \
        --port=8000
fi
