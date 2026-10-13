#!/bin/sh
set -e

# First start: create the environment file, app key and SQLite database.
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

mkdir -p database/data
export DB_DATABASE="${DB_DATABASE:-/app/database/data/database.sqlite}"

if [ ! -f "$DB_DATABASE" ]; then
    touch "$DB_DATABASE"
    php artisan migrate --seed --force
else
    php artisan migrate --force
fi

exec "$@"
