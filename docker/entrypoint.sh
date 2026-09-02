#!/bin/sh
set -e

# Replace port in nginx config with Cloud Run $PORT if provided
if [ -n "$PORT" ]; then
    sed -i "s/8080/$PORT/g" /etc/nginx/sites-available/default
fi

# Ensure storage directories and permissions
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public/settings \
         /var/www/html/storage/app/public/employees \
         /var/www/html/database

touch /var/www/html/database/database.sqlite
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Run database migrations and setup
php artisan package:discover --ansi
php artisan storage:link || true
php artisan migrate --force
php artisan db:seed --force || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM in background and Nginx in foreground
php-fpm -D
nginx -g "daemon off;"
