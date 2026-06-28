#!/bin/bash
set -e

PORT="${PORT:-80}"
echo "==> Starting entrypoint on PORT=${PORT}"

echo "==> Running php artisan optimize"
timeout 30 php artisan optimize || echo "WARN: optimize failed or timed out"

echo "==> Running migrations"
timeout 60 php artisan migrate --force || echo "WARN: migrate failed or timed out"

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
