# صرح الإتقان v3.0 — دليل النشر على Hostinger (sarh.online)

> **التاريخ:** 2026-03-02 | **حالة قاعدة البيانات:** فارغة — تثبيت جديد

---

## مرجع سريع

| العنصر | القيمة |
|--------|--------|
| النطاق | `sarh.online` |
| SSH | `ssh -p 65002 u850419603@145.223.119.139` |
| مسار المشروع | `/home/u850419603/sarh` |
| جذر الويب | `/home/u850419603/domains/sarh.online/public_html` |
| قاعدة البيانات | `u850419603_sarh` @ `127.0.0.1:3306` |
| مستخدم قاعدة البيانات | `u850419603_sarh` |
| لوحة الإدارة | `https://sarh.online/admin` |
| بوابة الموظف | `https://sarh.online/app` |

---

## خطوات النشر التفصيلية

### 1. الاتصال بالخادم عبر SSH

```bash
ssh -p 65002 u850419603@145.223.119.139
```

### 2. رفع المشروع

**الخيار أ — استنساخ Git:**
```bash
cd /home/u850419603
git clone https://github.com/abawelast-hash/link_1.git sarh
cd sarh
```

**الخيار ب — الرفع عبر مدير الملفات / SFTP:**
ارفع كامل مجلد `sarh/` إلى `/home/u850419603/sarh`.

> **أمان:** المشروع يقع **فوق** `domains/` — فقط مجلد `public/` متاح عبر الويب.

### 3. تشغيل سكربت النشر

```bash
cd /home/u850419603/sarh
chmod +x deploy.sh
bash deploy.sh
```

هذا الأمر الواحد يقوم بـ:
1. ✅ `COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader`
2. ✅ نسخ `.env.production` → `.env` وتوليد `APP_KEY`
3. ✅ تطبيق Hardened Session Protocol (file driver, no encrypt, SameSite=lax)
4. ✅ `php artisan migrate --force --seed`
5. ✅ بناء أصول Vite (إذا كان npm متاحاً)
6. ✅ نسخ الأصول إلى `domains/sarh.online/public_html/` مع bridge index.php
7. ✅ `php artisan optimize:clear` + مسح الجلسات القديمة
8. ✅ `php artisan config:cache && event:cache && filament:cache-components`
9. ✅ إصلاح صلاحيات `storage/` و `bootstrap/cache/`

### 4. إنشاء المدير العام

```bash
php artisan sarh:install
```

أدخل بيانات حساب المدير (المستوى 10) عند الطلب.

### 5. التحقق

افتح `https://sarh.online/admin` وسجل الدخول ببيانات المدير العام.

---

## إضافة Cron Job في لوحة Hostinger

في **Hosting → Advanced → Cron Jobs** أضف:
```
* * * * * /usr/local/bin/php /home/u850419603/sarh/artisan schedule:run >> /dev/null 2>&1
```

---

## إذا لم يكن npm متاحاً على Hostinger

1. ابنِ محلياً على جهازك:
```bash
cd c:\Users\it\VS_proj\link
npm install
npm run build
```

2. ارفع مجلد `public/build/` عبر SFTP إلى `/home/u850419603/sarh/public/build/`

---

## مرجع الأوامر اليدوية

```bash
# الانتقال للمشروع
cd /home/u850419603/sarh

# نسخ ملف البيئة
cp .env.production .env
php artisan key:generate --force

# مكتبات PHP
COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader --no-interaction

# قاعدة البيانات
php artisan migrate --force --seed

# الواجهة الأمامية
npm install --no-audit --no-fund && npm run build

# نسخ الأصول لـ public_html
mkdir -p /home/u850419603/domains/sarh.online/public_html
cp -r public/* /home/u850419603/domains/sarh.online/public_html/

# bridge index.php (CRITICAL)
cat > /home/u850419603/domains/sarh.online/public_html/index.php << 'EOF'
<?php
use Illuminate\Http\Request;
define('LARAVEL_START', microtime(true));
if (file_exists($m = '/home/u850419603/sarh/storage/framework/maintenance.php')) require $m;
require '/home/u850419603/sarh/vendor/autoload.php';
(require_once '/home/u850419603/sarh/bootstrap/app.php')->handleRequest(Request::capture());
EOF

# كاش الإنتاج
php artisan config:cache
php artisan event:cache
php artisan filament:cache-components

# الصلاحيات
chmod -R 775 storage bootstrap/cache

# إنشاء المدير العام
php artisan sarh:install
```

---

## إصلاح الصلاحيات (عند ظهور خطأ 500)

```bash
cd /home/u850419603/sarh
chmod -R 775 storage bootstrap/cache
chmod -R 644 storage/logs/*.log 2>/dev/null || true
```

---

## استكشاف الأخطاء وإصلاحها

| العَرَض | الحل |
|---------|------|
| خطأ 500 | `chmod -R 775 storage bootstrap/cache` |
| "Vite manifest not found" | ارفع `public/build/` عبر SFTP |
| "SQLSTATE Connection refused" | DB_HOST يجب أن يكون `127.0.0.1` وليس `localhost` |
| CSS/JS لا يُحمّل | تحقق من وجود ملفات في `domains/sarh.online/public_html/build/` |
| "No application encryption key" | `php artisan key:generate --force` |
| 404 على كل المسارات | تحقق من وجود `.htaccess` في `public_html/` |
| صفحة تسجيل الدخول فارغة | `php artisan view:clear && php artisan filament:cache-components` |
| مشكلة الجلسة (تسجل خروج تلقائي) | تأكد من `SESSION_DRIVER=file` في `.env` |

---

## قائمة التحقق بعد النشر

- [ ] `https://sarh.online` يُحمّل بدون أخطاء
- [ ] `https://sarh.online/admin` يُظهر صفحة تسجيل دخول Filament
- [ ] المدير العام يستطيع تسجيل الدخول (المستوى 10)
- [ ] التنسيق العربي RTL يظهر بشكل صحيح
- [ ] `APP_DEBUG=false` مؤكد في `.env`
- [ ] Cron Job مضاف للـ scheduler
