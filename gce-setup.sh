#!/bin/bash
# ==============================================================================
# 🚀 1-Click Server Setup Script for Google Compute Engine (Ubuntu 22.04/24.04)
# Posterit Office - Work Management System
# ==============================================================================

set -e

echo ">>> [1/7] Updating Ubuntu packages..."
sudo apt update && sudo apt upgrade -y

echo ">>> [2/7] Installing Git, Curl, Unzip, Nginx, SQLite, and Certbot..."
sudo apt install -y curl git unzip zip nginx sqlite3 certbot python3-certbot-nginx software-properties-common

echo ">>> [3/7] Adding Ondrej PHP PPA and installing PHP 8.3 + extensions..."
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-common php8.3-sqlite3 php8.3-mysql \
                    php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath \
                    php8.3-intl php8.3-gd php8.3-opcache

echo ">>> [4/7] Installing Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

echo ">>> [5/7] Setting directory permissions..."
PROJECT_DIR=$(pwd)
sudo chown -R www-data:www-data "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache"
sudo chmod -R 775 "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache"

# Create database if not exists
mkdir -p "$PROJECT_DIR/database"
touch "$PROJECT_DIR/database/database.sqlite"
sudo chown -R www-data:www-data "$PROJECT_DIR/database"
sudo chmod -R 775 "$PROJECT_DIR/database"

echo ">>> [6/7] Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

php artisan storage:link || true
php artisan migrate --force
php artisan db:seed --force
php artisan optimize

echo ">>> [7/7] Configuring Nginx..."
SERVER_IP=$(curl -s ifconfig.me || echo "_")
NGINX_CONF="/etc/nginx/sites-available/posterit"

sudo bash -c "cat > $NGINX_CONF" <<EOL
server {
    listen 80;
    server_name $SERVER_IP;
    root $PROJECT_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;
    charset utf-8;

    client_max_body_size 64M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOL

sudo ln -sf "$NGINX_CONF" /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm

echo ""
echo "=============================================================================="
echo "🎉 DEPLOYMENT COMPLETE! Your site is live at: http://$SERVER_IP"
echo "=============================================================================="
