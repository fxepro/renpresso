#!/bin/bash
set -e

PORT="${PORT:-80}"
echo "==> Starting entrypoint on PORT=${PORT}"

if [ -z "${APP_KEY:-}" ]; then
  echo "ERROR: APP_KEY is not set. Generate one locally: php artisan key:generate --show"
  exit 1
fi

# Without a Redis service, redis session/cache drivers cause 500 on every web route (/up still works).
if [ -z "${REDIS_URL:-}" ]; then
  echo "==> No REDIS_URL — forcing file cache/session and sync queue"
  export CACHE_STORE=file
  export SESSION_DRIVER=file
  export QUEUE_CONNECTION=sync
fi

echo "==> Running php artisan optimize (CACHE=${CACHE_STORE:-?} SESSION=${SESSION_DRIVER:-?})"
timeout 30 php artisan optimize || echo "WARN: optimize failed or timed out"

echo "==> Running migrations"
if ! timeout 60 php artisan migrate --force; then
  echo "ERROR: migrations failed — verify DATABASE_PRIVATE_URL or DB_* references on the web service"
  exit 1
fi

echo "==> Storage link"
timeout 10 php artisan storage:link || echo "WARN: storage:link failed"

echo "==> Setting up nginx config"
if [ ! -f /etc/nginx/sites-available/default.template ]; then
  echo "ERROR: nginx template not found at /etc/nginx/sites-available/default.template"
  exit 1
fi

sed "s/PORT_PLACEHOLDER/${PORT}/g" /etc/nginx/sites-available/default.template > /etc/nginx/sites-available/default
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

echo "==> Starting supervisord on PORT ${PORT}"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
