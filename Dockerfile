FROM alpine:3.20

# Install PHP 8.3 and all required extensions directly via apk (fast, binary packages)
RUN apk add --no-cache \
    php83 \
    php83-cli \
    php83-common \
    php83-curl \
    php83-mbstring \
    php83-openssl \
    php83-pdo \
    php83-pdo_sqlite \
    php83-sqlite3 \
    php83-gd \
    php83-zip \
    php83-bcmath \
    php83-xml \
    php83-dom \
    php83-xmlwriter \
    php83-tokenizer \
    php83-session \
    php83-fileinfo \
    php83-phar \
    php83-iconv \
    php83-intl \
    composer \
    git \
    curl \
    sqlite

# Symlink php83 to php
RUN ln -sf /usr/bin/php83 /usr/bin/php

WORKDIR /app

# Copy application files
COPY . /app

# Install Composer dependencies
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
