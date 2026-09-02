FROM serversideup/php:8.3-fpm-nginx

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV AUTORUN_LARAVEL_MIGRATION=true
ENV AUTORUN_LARAVEL_STORAGE_LINK=true
ENV AUTORUN_LARAVEL_CONFIG_CACHE=true
ENV AUTORUN_LARAVEL_ROUTE_CACHE=true
ENV AUTORUN_LARAVEL_VIEW_CACHE=true

WORKDIR /var/www/html

# Copy application files
COPY --chown=webuser:webgroup . /var/www/html

# Run composer as webuser
USER webuser
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

USER root

EXPOSE 8080
