#!/usr/bin/env bash
set -e

echo "=================================================="
echo "  POSTERIT OFFICE - SAFE PRODUCTION DEPLOYMENT"
echo "=================================================="

# 1. Run Tests & Lint
echo "1. Running Tests & Linters..."
vendor/bin/pint --format agent
php artisan test --compact

# 2. Build Frontend Assets
echo "2. Compiling Vite Production Assets..."
npm run build

# 3. Create Immediate Live Database Backup Before Sync
echo "3. Creating Pre-Deploy Live Database Backup..."
ssh bhaissh@213.218.240.121 "cd /home/bhai/htdocs/srv1070026.hstgr.cloud && php artisan db:backup"

# 4. Sync Code to Live Server (Safeguarding live database)
echo "4. Syncing Code to Production Server..."
rsync -rlz \
  --exclude='.git' \
  --exclude='database/*.sqlite*' \
  --exclude='node_modules' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  ./ bhaissh@213.218.240.121:/home/bhai/htdocs/srv1070026.hstgr.cloud/

# 5. Run Migrations and Optimize
echo "5. Applying Migrations and Clearing Caches..."
ssh bhaissh@213.218.240.121 "cd /home/bhai/htdocs/srv1070026.hstgr.cloud && php artisan migrate --force && php artisan optimize:clear"

echo "=================================================="
echo "  ✓ DEPLOYMENT COMPLETED SAFELY & SUCCESSFULLY! 🚀"
echo "=================================================="
