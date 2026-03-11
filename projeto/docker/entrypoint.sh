#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

if grep -q "^APP_KEY=$" .env; then
    php artisan key:generate --no-interaction
fi

echo "Aguardando MySQL..."
until php -r "new PDO('mysql:host=db;port=3306;dbname=painel_db', 'painel', 'secret');" 2>/dev/null; do
    sleep 2
done
echo "MySQL pronto."

php artisan migrate --force --no-interaction

if [ ! -f /var/www/storage/.seeded ]; then
    php artisan db:seed --force --no-interaction
    touch /var/www/storage/.seeded
fi

php-fpm
