FROM php:8.3-cli-alpine

# Install system libraries
RUN apk add --no-cache \
    curl \
    git \
    zip \
    unzip \
    sqlite-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev

# Install PHP extensions for Laravel + SQLite + GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) pdo pdo_sqlite gd zip pcntl bcmath

# Install Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application files
COPY . /app

# Install production dependencies
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

# Initialize storage and database
RUN mkdir -p storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache \
             storage/app/public/settings \
             storage/app/public/employees \
             storage/logs \
             database && \
    touch database/database.sqlite

ENV PORT=10000

EXPOSE 10000

CMD sh -c "php artisan storage:link || true && php artisan migrate --force && php artisan db:seed --force || true && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"
