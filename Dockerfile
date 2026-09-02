FROM serversideup/php:8.3-fpm-nginx

ENV AUTORUN_LARAVEL_MIGRATION=true
ENV AUTORUN_LARAVEL_STORAGE_LINK=true
ENV AUTORUN_LARAVEL_CONFIG_CACHE=true
ENV AUTORUN_LARAVEL_ROUTE_CACHE=true
ENV AUTORUN_LARAVEL_VIEW_CACHE=true

WORKDIR /var/www/html

# Copy application files
COPY --chown=webuser:webgroup . /var/www/html

# Install dependencies without running boot scripts
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

EXPOSE 8080
