#!/bin/sh
set -eu

cd /var/www/html

echo "==> Ensuring .env exists"
if [ ! -f .env ]; then
  cp .env.example .env
  echo ".env created from .env.example"
else
  echo ".env already exists"
fi

echo "==> Ensuring APP_URL is set for local nginx"
if grep -q '^APP_URL=' .env; then
  sed -i 's|^APP_URL=.*|APP_URL=http://localhost:8782|' .env
else
  echo "APP_URL=http://localhost:8782" >> .env
fi

echo "==> Installing Composer dependencies"
composer install --no-interaction --prefer-dist

echo "==> Installing frontend dependencies"
npm ci

echo "==> Building frontend assets"
npm run build

echo "==> Waiting for MySQL"
ATTEMPTS=0
MAX_ATTEMPTS=8
until mysql --skip-ssl -hmysql -uapp -papp -e "SELECT 1" app >/dev/null 2>&1; do
  ATTEMPTS=$((ATTEMPTS + 1))
  if [ "$ATTEMPTS" -ge "$MAX_ATTEMPTS" ]; then
    echo "MySQL is not ready after $MAX_ATTEMPTS attempts"
    exit 1
  fi
  echo "Waiting for database... ($ATTEMPTS/$MAX_ATTEMPTS)"
  sleep 2
done

echo "==> Ensuring APP_KEY exists"
if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
  echo "APP_KEY generated"
else
  echo "APP_KEY already present"
fi

echo "==> Running migrations"
php artisan migrate --force

echo "==> Running seeders"
php artisan db:seed --force

echo "==> Syncing articles"
php artisan articles:sync || true

echo "==> Clearing caches"
php artisan config:clear
php artisan cache:clear

echo "==> Setup completed"
