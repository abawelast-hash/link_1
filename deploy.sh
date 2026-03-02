#!/bin/bash
###############################################################
# ØµØ±Ø­ Ø§Ù„Ø¥ØªÙ‚Ø§Ù† v3.0 â€” One-Command Deploy Script
# Usage: bash deploy.sh          (first time + updates)
# Repo:  https://github.com/abawelast-hash/link_1.git
# Target: Hostinger VPS â€” Ubuntu 24.04 LTS
# Path:   /var/www/sarh
# Domain: sarh.shop
###############################################################

set -e

PROJECT_DIR="/var/www/sarh"
REPO_URL="https://github.com/abawelast-hash/link_1.git"

echo ""
echo "â•”â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•—"
echo "â•‘   ØµØ±Ø­ Ø§Ù„Ø¥ØªÙ‚Ø§Ù† v3.0 â€” Production Deploy  â•‘"
echo "â•‘   sarh.shop                             â•‘"
echo "â•šâ•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•"
echo ""

# â”€â”€ Step 0: Clone or Pull from GitHub â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
if [ ! -d "$PROJECT_DIR/.git" ]; then
    echo "â–¸ [0/7] First deploy â€” cloning from GitHub..."
    mkdir -p /var/www
    git clone "$REPO_URL" "$PROJECT_DIR"
    echo "  âœ“ Repository cloned"
else
    echo "â–¸ [0/7] Pulling latest from GitHub..."
    cd "$PROJECT_DIR"
    git fetch origin
    git reset --hard origin/main
    echo "  âœ“ Code updated to latest main"
fi
echo ""

cd "$PROJECT_DIR"

# â”€â”€ Step 1: Composer Install â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "â–¸ [1/7] Installing PHP dependencies..."
COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-interaction
echo "  âœ“ Composer dependencies installed"
echo ""

# â”€â”€ Step 2: Setup .env â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
if [ ! -f .env ]; then
    echo "â–¸ [2/7] Setting up environment file..."
    cp .env.production .env
    php artisan key:generate --force
    echo "  âœ“ Environment configured & APP_KEY generated"
else
    echo "â–¸ [2/7] .env exists â€” ensuring APP_KEY is set..."
    if ! grep -q "^APP_KEY=base64:" .env; then
        php artisan key:generate --force
        echo "  âœ“ APP_KEY generated"
    else
        echo "  âœ“ APP_KEY already set"
    fi
fi

# Enforce session settings
sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
sed -i 's/^SESSION_ENCRYPT=.*/SESSION_ENCRYPT=false/' .env
sed -i 's/^SESSION_DOMAIN=.*/SESSION_DOMAIN=null/' .env
grep -q "^SESSION_DRIVER="     .env || echo "SESSION_DRIVER=file"         >> .env
grep -q "^SESSION_SECURE_COOKIE=" .env || echo "SESSION_SECURE_COOKIE=true" >> .env
echo ""

# â”€â”€ Step 3: Migrate â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "â–¸ [3/7] Running migrations..."
php artisan migrate --force
echo "  âœ“ Database schema up to date"
echo ""

# â”€â”€ Step 4: Build Frontend Assets (Vite) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "â–¸ [4/7] Building frontend assets..."
if command -v npm &> /dev/null; then
    npm install --no-audit --no-fund
    npm run build
    echo "  âœ“ Vite assets compiled to public/build/"
else
    echo "  âš  npm not found â€” skipping"
fi
echo ""

# â”€â”€ Step 5: Storage Symlink â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "â–¸ [5/7] Creating storage symlink..."
mkdir -p "$PROJECT_DIR/storage/app/public"
php artisan storage:link --force
echo "  âœ“ storage symlink created"
echo ""

# â”€â”€ Step 6: Optimize for Production â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "â–¸ [6/7] Optimizing for production..."
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan filament:cache-components
echo "  âœ“ Config, events, Filament components cached"
echo ""

# â”€â”€ Step 7: Permissions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "â–¸ [7/7] Fixing permissions..."
chown -R www-data:www-data "$PROJECT_DIR" 2>/dev/null || true
chmod -R 755 "$PROJECT_DIR"
chmod -R 775 "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache"
echo "  âœ“ Permissions set"
echo ""

# â”€â”€ Restart Queue Worker â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
php artisan queue:restart 2>/dev/null || true
systemctl restart sarh-queue 2>/dev/null || true

# â”€â”€ Done â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
echo "â•”â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•—"
echo "â•‘   âœ… DEPLOYMENT COMPLETE                  â•‘"
echo "â•‘                                           â•‘"
echo "â•‘   URL:   https://sarh.shop                â•‘"
echo "â•‘   Admin: https://sarh.shop/admin          â•‘"
echo "â•‘                                           â•‘"
echo "â•‘   Next: Run 'php artisan sarh:install'    â•‘"
echo "â•‘   to create the Super Admin (Level 10)    â•‘"
echo "â•šâ•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•"
echo ""
