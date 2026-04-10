#!/bin/bash
set -e

# Ensure .env exists (copy from .env.docker if needed)
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.docker /var/www/html/.env 2>/dev/null || true
fi

# Laravel bootstrap expects .env to exist. If copy was not possible, create an empty file.
if [ ! -f /var/www/html/.env ]; then
    touch /var/www/html/.env
fi

# Ensure bootstrap/cache exists
mkdir -p /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
