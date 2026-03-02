# 🏛 صرح الإتقان — نظام إدارة الموارد البشرية والذكاء المالي
**الإصدار:** v3.0.0 | **آخر تحديث:** 2 مارس 2026

> "صفر تأخير. صفر خسائر. صفر تلاعب."

---

## 📋 نبذة عن المشروع

صرح الإتقان هو نظام مؤسسي متكامل لإدارة الموارد البشرية، مبني على **Laravel 11 + Filament 3 + Livewire 3**.  
يحوّل بيانات الحضور والانصراف إلى مؤشرات مالية دقيقة تُمكّن المدير من اتخاذ قرارات فورية ومبنية على بيانات حقيقية.

---

## ✨ الميزات الرئيسية

| الميزة | الوصف |
|--------|-------|
| 🛰️ **الحضور الجغرافي** | تسجيل حضور بالـ GPS مع سياج جغرافي بنصف قطر **17 متراً** (Haversine) |
| 💰 **الذكاء المالي** | تحويل كل دقيقة تأخير إلى تكلفة مالية محسوبة رياضياً |
| 🏆 **محرك المنافسة** | لوحة مستويات بين الفروع مع نظام نقاط وشارات |
| 🔐 **10 مستويات أمنية** | نظام صلاحيات هرمي من المتدرب (1) حتى المدير العام (10) |
| 🕵️ **كشف الشواذ** | رصد تلقائي للتنقل المستحيل (>2km في 30 دقيقة) |
| 📢 **البلاغات السرية** | قناة مشفرة بـ token لـ 64 حرف للإبلاغ عن المخالفات بشكل مجهول |
| 💬 **التعاميم الرسمية** | تعاميم مع إلزامية الاطلاع وتتبع من قرأ |
| 📊 **غرفة القيادة** | لوحة تحكم لحظية بإحصائيات الفروع والموظفين |
| ⚡ **طابور المهام** | معالجة الحضور والتعاميم بشكل غير متزامن |
| 📅 **التقارير المالية** | تقارير شهرية تلقائية بالاستقطاعات والمكافآت |

---

## 🏗️ البنية التقنية

| العنصر | التقنية |
|--------|---------|
| إطار العمل | Laravel 11 |
| لوحة الإدارة | Filament 3 |
| التفاعلية | Livewire 3 + Alpine.js |
| التصميم | Tailwind CSS + ثيم Navy+Gold مخصص |
| قاعدة البيانات | MySQL 8.0 — 24 جدول |
| PHP | 8.3+ |
| الاستضافة | Hostinger Shared Hosting |
| الخط | Cairo (عربي — RTL) |

---

## 🗃️ قاعدة البيانات (24 جدول)

### الجداول الأساسية

| الجدول | الوصف |
|--------|-------|
| `branches` | الفروع مع إحداثيات GPS ومستوى المنافسة |
| `users` | الموظفون مع مستوى الأمان (1-10) والنقاط |
| `shifts` | الوردیات (وقت الحضور، الانصراف، فترة السماح) |
| `user_shifts` | ربط الموظفين بالوردیات |
| `attendance_logs` | سجلات الحضور والانصراف بالإحداثيات |
| `badges` | الشارات وشروط منحها |
| `user_badges` | الشارات الممنوحة للموظفين |
| `points_transactions` | سجل النقاط المكتسبة والمخصومة |
| `leave_requests` | طلبات الإجازات |
| `circulars` | التعاميم الرسمية |
| `circular_reads` | تتبع اطلاع الموظفين على التعاميم |
| `whistleblower_reports` | البلاغات السرية المجهولة |
| `messages` | الرسائل الداخلية |
| `notifications` | إشعارات النظام |
| `monthly_reports` | التقارير المالية الشهرية |
| `anomaly_logs` | سجلات الشواذ المكتشفة |
| `competition_settings` | إعدادات نظام المنافسة |
| `jobs` / `failed_jobs` | طابور المهام |
| `cache` / `cache_locks` | ذاكرة التخزين المؤقت |

---

## 📁 هيكل المشروع

```
app/
├── Console/Commands/
│   ├── InstallSarh.php              ← php artisan sarh:install
│   └── RecalculateMonthlyCommand.php ← php artisan sarh:monthly-reports
│
├── Events/
│   ├── AttendanceRecorded.php       ← يُطلق عند تسجيل الحضور
│   ├── AnomalyDetected.php          ← يُطلق عند كشف شاذة
│   └── BadgeAwarded.php             ← يُطلق عند منح شارة
│
├── Filament/
│   ├── Admin/                       ← لوحة الإدارة (/admin)
│   │   ├── Resources/               ← 9 موارد إدارية
│   │   └── Widgets/                 ← 3 ويدجت
│   └── App/                         ← بوابة الموظف (/app)
│       ├── Pages/                   ← 8 صفحات
│       └── Widgets/                 ← 3 ويدجت
│
├── Http/Middleware/
│   └── SarhAuthenticate.php
│
├── Jobs/
│   ├── RecalculateMonthlyReportsJob.php
│   └── SendCircularJob.php
│
├── Listeners/
│   └── EvaluateBadgesOnAttendance.php ← Queue: badges
│
├── Models/                           ← 16 نموذج
├── Notifications/
│   └── CircularPublishedNotification.php
│
├── Policies/
│   ├── UserPolicy.php
│   └── AttendanceLogPolicy.php
│
├── Providers/
│   ├── AdminPanelProvider.php
│   ├── AppPanelProvider.php
│   ├── AuthServiceProvider.php      ← Gate::before (God Mode)
│   └── EventServiceProvider.php
│
└── Services/                        ← 5 خدمات
    ├── GeofencingService.php
    ├── AttendanceService.php
    ├── BadgeService.php
    ├── AnalyticsService.php
    └── FinancialReportingService.php
```

---

## 🔧 الخدمات (Services)

### 1. `GeofencingService`
التحقق من موقع الموظف عند تسجيل الحضور.
- `validatePosition(lat, lng, branch)` — يحسب المسافة بـ Haversine ويتحقق من ≤ 17m
- `detectImpossibleTravel(user)` — يكشف إذا تنقل الموظف >2km في 30 دقيقة → ينشئ `AnomalyLog`

### 2. `AttendanceService`
إدارة دورة حياة الحضور الكاملة.
- `checkIn(user, lat, lng)` — يتحقق من السياج + يحسب الحالة (في_الوقت / متأخر / غياب) + يمنح نقاطاً
- `checkOut(user, lat, lng)` — يحسب ساعات العمل الفعلية والعمل الإضافي
- `calculatePoints(status, minutesLate)` — نقاط حسب درجة الالتزام

### 3. `BadgeService`
تقييم ومنح الشارات تلقائياً.
- `evaluateBadges(user)` — يفحص أهلية جميع الشارات المتاحة
- `awardBadge(user, badge)` — يمنح الشارة ويضيف النقاط
- شروط قابلة للضبط: streak متتالي، بدون تأخير شهرياً، عمل إضافي

### 4. `AnalyticsService`
إحصائيات وتحليلات لحظية.
- `dailyBranchStats(date)` — حضور وغياب وتأخير لكل فرع
- `companyLeaderboard()` — ترتيب الفروع حسب النقاط
- `detectMissingCheckIns(date)` — كشف من لم يسجل حضوراً

### 5. `FinancialReportingService`
التقارير المالية الشهرية.
- `generateMonthlyReport(user, year, month)` — تقرير فردي بالاستقطاعات والمكافآت
- `branchMonthlyDeductions(branch, year, month)` — إجمالي تكلفة التأخير للفرع
- أيام العمل: السبت–الأربعاء (الجمعة والخميس إجازة)

---

## 🖥️ لوحة الإدارة — `/admin`

**الوصول:** المستوى الأمني 4 فما فوق

### الموارد الإدارية (9 Resources)

| المورد | الوصف |
|--------|-------|
| `UserResource` | إدارة الموظفين — مستوى الأمان، النقاط، الفرع |
| `BranchResource` | إدارة الفروع — إحداثيات GPS، مستوى المنافسة |
| `AttendanceLogResource` | سجلات الحضور مع الألوان والفلاتر |
| `LeaveRequestResource` | طلبات الإجازات مع موافقة/رفض مباشرة |
| `CircularResource` | إنشاء التعاميم وإرسالها للجميع |
| `WhistleblowerReportResource` | بلاغات سرية — محمية بـ Gate |
| `BadgeResource` | إدارة الشارات وشروطها |
| `MonthlyReportResource` | التقارير المالية الشهرية بالريال السعودي |
| `AnomalyLogResource` | سجل الشواذ المكتشفة مع المراجعة |

### الويدجات (3 Widgets)

| الويدجت | الوصف |
|---------|-------|
| `StatsOverviewWidget` | إجمالي الموظفين، الحضور اليوم، التأخير، البلاغات |
| `AttendanceTodayWidget` | جدول حضور اليوم بالوقت والحالة |
| `CompanyLeaderboardWidget` | ترتيب الفروع بالنقاط والمستوى |

---

## 👤 بوابة الموظف — `/app`

**الوصول:** جميع المستويات

### الصفحات (8 Pages)

| الصفحة | الوصف |
|--------|-------|
| `AttendancePage` | تسجيل حضور/انصراف بالـ GPS عبر Alpine.js |
| `MyProfilePage` | الملف الشخصي والبيانات |
| `MyBadgesPage` | الشارات المكتسبة |
| `MyReportsPage` | التقارير الشهرية الخاصة |
| `LeaveRequestPage` | تقديم طلب إجازة |
| `InboxPage` | صندوق الرسائل |
| `CircularsPage` | التعاميم مع تسجيل الاطلاع |
| `WhistleblowerPage` | إبلاغ مجهول مع إرجاع token |

### الويدجات (3 Widgets)

| الويدجت | الوصف |
|---------|-------|
| `MyAttendanceTodayWidget` | حالة حضور اليوم + إحصاء شهري |
| `MyPointsWidget` | النقاط الإجمالية + الشهرية + عدد الشارات |
| `MyStreakWidget` | عداد الأيام المتتالية في الوقت + ترتيب في الفرع |

---

## 🔐 نظام الصلاحيات

### المستويات الأمنية

| المستوى | الدور |
|---------|-------|
| 1 | متدرب |
| 2 | موظف |
| 3 | موظف أول |
| 4 | مشرف |
| 5 | مدير فرع |
| 6 | مدير منطقة |
| 7 | مدير قسم |
| 8 | مدير تنفيذي |
| 9 | نائب المدير العام |
| **10** | **المدير العام (God Mode)** |

### البوابات المخصصة (Custom Gates)

```php
access-whistleblower-vault   // عرض البلاغات السرية
bypass-geofence              // تجاوز التحقق الجغرافي
manage-competition           // ضبط إعدادات المنافسة
adjust-points                // تعديل نقاط الموظفين يدوياً
send-company-circulars       // إرسال تعاميم لجميع الفروع
review-leaves                // مراجعة طلبات الإجازات
```

**God Mode:** المستوى 10 يتجاوز جميع الصلاحيات تلقائياً عبر `Gate::before()`.

---

## ⚙️ الأوامر (Artisan Commands)

```bash
# تثبيت النظام لأول مرة (فرع + مدير + شارات + إعدادات)
php artisan sarh:install

# إعادة احتساب التقارير المالية
php artisan sarh:monthly-reports --year=2026 --month=3
```

---

## 📅 المهام المجدولة (Scheduler)

```bash
# يجب إضافة هذا في Cron Jobs على Hostinger
* * * * * /usr/local/bin/php /home/u850419603/sarh/artisan schedule:run >> /dev/null 2>&1
```

| المهمة | الجدول |
|--------|--------|
| `RecalculateMonthlyReportsJob` | أول كل شهر الساعة 00:30 |
| `detectMissingCheckIns` | كل يوم عمل الساعة 10:00 |

---

## 🚀 النشر على Hostinger

### معلومات السيرفر

| العنصر | القيمة |
|--------|--------|
| النطاق | `sarh.online` |
| SSH | `ssh -p 65002 u850419603@145.223.119.139` |
| مسار المشروع | `/home/u850419603/sarh` |
| جذر الويب | `/home/u850419603/domains/sarh.online/public_html` |
| قاعدة البيانات | `u850419603_sarh` @ `127.0.0.1:3306` |

### خطوات النشر الأولي

```bash
# 1. ارفع .env.production عبر SFTP إلى /home/u850419603/sarh/

# 2. اتصل بالسيرفر
ssh -p 65002 u850419603@145.223.119.139

# 3. شغّل النشر الكامل
git clone https://github.com/abawelast-hash/link_1.git /home/u850419603/sarh
bash /home/u850419603/sarh/deploy.sh

# 4. أنشئ المدير العام
php artisan sarh:install
```

### النشر السريع (تحديث)

```bash
# من جهازك المحلي مباشرة
bash deploy-quick.sh
```

### ما يفعله `deploy.sh` تلقائياً

1. ✅ Clone أو Pull من GitHub
2. ✅ `composer install` بدون حد ذاكرة
3. ✅ نسخ `.env.production` → `.env` + توليد `APP_KEY`
4. ✅ تطبيق Hardened Session Protocol
5. ✅ `php artisan migrate --force --seed`
6. ✅ بناء أصول Vite (أو تخطي إذا لم يكن npm متاحاً)
7. ✅ نسخ الأصول إلى `public_html` مع Bridge `index.php`
8. ✅ `optimize:clear` + مسح الجلسات القديمة
9. ✅ `config:cache + event:cache + filament:cache-components`
10. ✅ إصلاح الصلاحيات `chmod 775`

> **ملاحظة:** `route:cache` و `view:cache` مُعطَّلان عمداً — يتعارضان مع Filament v3 على الاستضافة المشتركة.

---

## 🎨 الواجهة

- **الألوان:** Navy `#0F172A` + Gold `#D4A841`
- **الوضع:** داكن (Dark Mode) افتراضي
- **الاتجاه:** RTL (عربي)
- **الخط:** Cairo
- **الملفات:**
  - `resources/css/filament/admin/theme.css`
  - `resources/css/filament/app/theme.css`

---

## 🔄 تدفق الحضور (Flow)

```
الموظف يضغط "تسجيل حضور"
        ↓
Alpine.js يجلب إحداثيات GPS
        ↓
AttendanceService::checkIn()
        ↓
GeofencingService::validatePosition()  ← هل ≤ 17m من الفرع؟
        ↓
حساب الحالة: في_الوقت / متأخر / غياب
        ↓
حفظ AttendanceLog + إضافة النقاط
        ↓
إطلاق AttendanceRecorded Event
        ↓
EvaluateBadgesOnAttendance (Queue: badges)
        ↓
BadgeService::evaluateBadges()  ← هل يستحق شارة جديدة؟
```

---

## 🔔 نظام الأحداث (Events & Listeners)

| الحدث | المستمع | الطابور |
|-------|---------|---------|
| `AttendanceRecorded` | `EvaluateBadgesOnAttendance` | `badges` |
| `AnomalyDetected` | — (قابل للتوسعة) | — |
| `BadgeAwarded` | — (قابل للتوسعة) | — |

---

## 📦 التبعيات الرئيسية

```json
{
  "laravel/framework": "^11.0",
  "filament/filament": "^3.0",
  "livewire/livewire": "^3.0"
}
```

---

## 🛠️ استكشاف الأخطاء

| العَرَض | الحل |
|---------|------|
| خطأ 500 | `chmod -R 775 storage bootstrap/cache` |
| "Vite manifest not found" | ارفع `public/build/` عبر SFTP |
| "SQLSTATE Connection refused" | `DB_HOST=127.0.0.1` وليس `localhost` |
| تسجيل خروج تلقائي | `SESSION_DRIVER=file` في `.env` |
| 404 على المسارات | تحقق من `.htaccess` في `public_html/` |
| صفحة تسجيل دخول فارغة | `php artisan view:clear && php artisan filament:cache-components` |

---

## 📄 الترخيص

هذا النظام مملوك ومحمي. جميع الحقوق محفوظة.  
**صرح الإتقان** — نظام إدارة الموارد البشرية والذكاء المالي المؤسسي.
