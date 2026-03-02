#!/bin/bash
# ================================
# نشر سريع — صرح الإتقان v3.0
# sarh.online
# ================================

set -e

SERVER="u850419603@145.223.119.139"
PORT="65002"
PROJECT_PATH="/home/u850419603/sarh"
DOMAIN_PUBLIC="/home/u850419603/domains/sarh.online/public_html"

echo "🚀 بدء النشر السريع..."

# 1. Commit وPush محلياً
echo ""
echo "📦 Commit & Push..."
git add -A
git commit -m "deploy: Quick deployment $(date +%Y-%m-%d_%H:%M:%S)" || echo "لا توجد تغييرات للـ commit"
git push origin main

# 2. تحديث السيرفر
echo ""
echo "🌐 تحديث السيرفر..."
ssh -p $PORT $SERVER "cd $PROJECT_PATH && \
    git fetch origin main && \
    git reset --hard origin/main && \
    php artisan migrate --force && \
    php artisan optimize:clear && \
    php artisan config:cache && \
    php artisan event:cache && \
    php artisan filament:cache-components && \
    echo '📁 Syncing public assets to domain public_html...' && \
    cp -r $PROJECT_PATH/public/build $DOMAIN_PUBLIC/ && \
    cp -r $PROJECT_PATH/public/css $DOMAIN_PUBLIC/ 2>/dev/null; \
    cp -r $PROJECT_PATH/public/js $DOMAIN_PUBLIC/ 2>/dev/null; \
    cp $PROJECT_PATH/public/.htaccess $DOMAIN_PUBLIC/ 2>/dev/null; \
    chmod -R 775 $PROJECT_PATH/storage $PROJECT_PATH/bootstrap/cache && \
    echo '✅ Assets synced to public_html'"

echo ""
echo "✅ النشر مكتمل!"
echo "   🔗 https://sarh.online"
echo "   🔗 https://sarh.online/admin"
