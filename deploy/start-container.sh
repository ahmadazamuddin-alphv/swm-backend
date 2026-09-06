#!/usr/bin/env sh

set -eu

find /app/bootstrap/cache -type f -name '*.php' -delete
cp /app/database/demo.sqlite /tmp/siaga-demo.sqlite
cp /app/.env.example /app/.env

export APP_ENV=production
export APP_DEBUG=false
export APP_URL="${APP_URL:-http://localhost}"
export DB_CONNECTION=sqlite
export DB_DATABASE=/tmp/siaga-demo.sqlite
export CACHE_STORE=file
export SESSION_DRIVER=file
export QUEUE_CONNECTION=sync
export LOG_CHANNEL=stderr

php artisan key:generate --force --no-interaction
php artisan package:discover --ansi
php artisan filament:assets --no-interaction --quiet
php artisan optimize:clear

exec php -S 0.0.0.0:8080 -t public deploy/router.php
