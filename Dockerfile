FROM php:8.3-fpm-alpine

# Install system dependencies and Nginx
RUN apk add --no-cache \
    nginx \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite-dev \
    icu-dev \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_sqlite \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy Nginx configuration and entrypoint
RUN mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled && \
    cp docker/nginx.conf /etc/nginx/sites-available/default && \
    ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default && \
    chmod +x docker/entrypoint.sh

# Install Composer dependencies (production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Cloud Run dynamic port (default 8080)
EXPOSE 8080

ENTRYPOINT ["docker/entrypoint.sh"]
