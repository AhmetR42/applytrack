FROM php:8.3-cli-alpine

# Composer binary from the official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install PHP dependencies first so this layer is cached between code changes.
# Dev packages are kept because the demo seeder uses Faker.
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize \
    && chmod +x docker/entrypoint.sh

ENV PORT=8000
EXPOSE 8000

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["sh", "-c", "exec php artisan serve --host=0.0.0.0 --port=${PORT}"]
