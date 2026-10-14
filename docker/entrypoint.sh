#!/bin/sh
set -e

# First start: create the environment file, app key and SQLite database.
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

# Settings passed in by the host (for example Render) win over the defaults in .env.
for key in APP_ENV APP_DEBUG APP_URL; do
    eval "value=\${$key:-}"
    if [ -n "$value" ]; then
        if grep -q "^$key=" .env; then
            sed -i "s|^$key=.*|$key=$value|" .env
        else
            echo "$key=$value" >> .env
        fi
    fi
done

mkdir -p database/data
export DB_DATABASE="${DB_DATABASE:-/app/database/data/database.sqlite}"

# "artisan serve" does not pass every environment variable to the web server,
# so the database path must also be written to .env.
if grep -q '^DB_DATABASE=' .env; then
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" .env
else
    echo "DB_DATABASE=$DB_DATABASE" >> .env
fi

if [ ! -f "$DB_DATABASE" ]; then
    touch "$DB_DATABASE"
    php artisan migrate --seed --force
else
    php artisan migrate --force
fi

exec "$@"
