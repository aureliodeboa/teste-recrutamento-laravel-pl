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

ADMIN_COUNT=$(php -r "try { \$pdo = new PDO('mysql:host=db;port=3306;dbname=painel_db', 'painel', 'secret'); echo \$pdo->query('SELECT COUNT(*) FROM administradores')->fetchColumn(); } catch(Exception \$e) { echo '0'; }")
if [ "$ADMIN_COUNT" = "0" ]; then
    echo "Banco vazio — executando seeder..."
    php artisan db:seed --force --no-interaction
else
    echo "Banco ja possui dados — seeder ignorado."
fi

php-fpm
