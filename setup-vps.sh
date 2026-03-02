#!/bin/bash
###############################################################
# setup-vps.sh — تهيئة Ubuntu 24.04 LTS لنظام صرح الإتقان
#
# يُشغَّل مرة واحدة فقط على VPS جديد:
#   bash setup-vps.sh
#
# يقوم بـ:
#   1. تثبيت Nginx + PHP 8.3 + MySQL 8 + Composer + Node 20
#   2. إنشاء قاعدة البيانات والمستخدم
#   3. إعداد Nginx لـ sarh.shop
#   4. إنشاء Queue Worker كـ systemd service
#   5. استنساخ المشروع وتشغيل deploy.sh
###############################################################

set -e

DOMAIN="sarh.shop"
PROJECT_DIR="/var/www/sarh"
DB_NAME="sarh_db"
DB_USER="sarh_user"
DB_PASS="Goolbx512!!"
REPO_URL="https://github.com/abawelast-hash/link_1.git"

echo ""
echo "╔══════════════════════════════════════════════╗"
echo "║  صرح الإتقان v3.0 — VPS Setup (Ubuntu 24)  ║"
echo "║  $DOMAIN                              ║"
echo "╚══════════════════════════════════════════════╝"
echo ""

# ── 1. تحديث النظام ──────────────────────────────────────────────────────
echo "▸ [1/8] Updating system..."
apt-get update -qq && apt-get upgrade -y -qq
echo "  ✓ System updated"

# ── 2. تثبيت PHP 8.3 + الإضافات ─────────────────────────────────────────
echo "▸ [2/8] Installing PHP 8.3..."
apt-get install -y -qq software-properties-common
add-apt-repository -y ppa:ondrej/php
apt-get update -qq
apt-get install -y -qq \
    php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
    php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-intl \
    php8.3-redis php8.3-tokenizer php8.3-fileinfo \
    php8.3-pdo php8.3-pdo-mysql
echo "  ✓ PHP 8.3 installed"

# ── 3. تثبيت Nginx ───────────────────────────────────────────────────────
echo "▸ [3/8] Installing Nginx..."
apt-get install -y -qq nginx
systemctl enable nginx
echo "  ✓ Nginx installed"

# ── 4. تثبيت MySQL 8 ─────────────────────────────────────────────────────
echo "▸ [4/8] Installing MySQL 8..."
apt-get install -y -qq mysql-server
systemctl enable mysql
systemctl start mysql

# إنشاء قاعدة البيانات والمستخدم
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
echo "  ✓ MySQL installed + database '${DB_NAME}' + user '${DB_USER}' created"

# ── 5. تثبيت Composer ────────────────────────────────────────────────────
echo "▸ [5/8] Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
echo "  ✓ Composer $(composer --version 2>/dev/null | head -1)"

# ── 6. تثبيت Node.js 20 ──────────────────────────────────────────────────
echo "▸ [6/8] Installing Node.js 20..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y -qq nodejs
echo "  ✓ Node $(node --version) / npm $(npm --version)"

# ── 7. إعداد Nginx لـ sarh.shop ──────────────────────────────────────────
echo "▸ [7/8] Configuring Nginx..."

cat > /etc/nginx/sites-available/sarh << NGINX
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${PROJECT_DIR}/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 50M;
}
NGINX

ln -sf /etc/nginx/sites-available/sarh /etc/nginx/sites-enabled/sarh
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
echo "  ✓ Nginx configured for $DOMAIN"

# ── 8. استنساخ المشروع وتشغيل deploy.sh ─────────────────────────────────
echo "▸ [8/8] Cloning project and deploying..."
mkdir -p /var/www
if [ -d "$PROJECT_DIR/.git" ]; then
    echo "  ✓ Repo already cloned — pulling latest..."
    cd "$PROJECT_DIR" && git fetch origin && git reset --hard origin/main
elif [ -d "$PROJECT_DIR" ]; then
    echo "  ✓ Directory exists but no git — removing and cloning fresh..."
    rm -rf "$PROJECT_DIR"
    git clone "$REPO_URL" "$PROJECT_DIR"
else
    git clone "$REPO_URL" "$PROJECT_DIR"
fi
cd "$PROJECT_DIR"
# Strip Windows CR from all shell scripts (safety)
find "$PROJECT_DIR" -name "*.sh" -exec sed -i 's/\r//' {} \;
chmod +x deploy.sh
bash deploy.sh
echo "  ✓ Project deployed"

# ── Queue Worker كـ systemd service ──────────────────────────────────────
echo "▸ Setting up Queue Worker service..."
cat > /etc/systemd/system/sarh-queue.service << UNIT
[Unit]
Description=صرح الإتقان — Queue Worker
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
WorkingDirectory=${PROJECT_DIR}
ExecStart=/usr/bin/php artisan queue:work --queue=badges,notifications,default --tries=3 --timeout=90 --sleep=3
Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
UNIT

systemctl daemon-reload
systemctl enable sarh-queue
systemctl start sarh-queue
echo "  ✓ Queue Worker started"

# ── إضافة Cron Job للـ Scheduler ─────────────────────────────────────────
(crontab -l 2>/dev/null; echo "* * * * * /usr/bin/php ${PROJECT_DIR}/artisan schedule:run >> /dev/null 2>&1") | crontab -
echo "  ✓ Cron job added for scheduler"

# ── ضبط صلاحيات المجلد ───────────────────────────────────────────────────
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 755 "$PROJECT_DIR"
chmod -R 775 "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache"

# ── SSL مع Certbot ────────────────────────────────────────────────────────
echo ""
echo "▸ Installing SSL (Certbot)..."
apt-get install -y -qq certbot python3-certbot-nginx
certbot --nginx -d "$DOMAIN" -d "www.$DOMAIN" --non-interactive --agree-tos -m "admin@$DOMAIN" || \
    echo "  ⚠ SSL failed — يمكنك تشغيله يدوياً: certbot --nginx -d $DOMAIN"

# ── إنشاء المدير العام ───────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════╗"
echo "║   ✅ VPS SETUP COMPLETE                     ║"
echo "║                                              ║"
echo "║   URL:   https://$DOMAIN               ║"
echo "║   Admin: https://$DOMAIN/admin         ║"
echo "║                                              ║"
echo "║   الخطوة الأخيرة — إنشاء المدير العام:     ║"
echo "║   cd /var/www/sarh                           ║"
echo "║   php artisan sarh:install                   ║"
echo "╚══════════════════════════════════════════════╝"
echo ""
