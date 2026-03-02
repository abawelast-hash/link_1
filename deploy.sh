#!/usr/bin/env bash
# ==========================================================================
#  deploy.sh — نشر نظام صرح الإتقان v3.0 على Hostinger
# ==========================================================================
#  الاستخدام المباشر:
#    bash deploy.sh
#
#  الاستخدام عبر SSH:
#    ssh -p 65002 u307296675@194.164.74.250 "bash /home/u307296675/sarh/deploy.sh"
# ==========================================================================

set -euo pipefail

SARH_PATH="/home/u307296675/sarh"
PUBLIC_HTML="/home/u307296675/domains/sarh.io/public_html"
PHP="php8.3"
ARTISAN="${PHP} artisan"

echo ""
echo "══════════════════════════════════════════════"
echo "  🏛  صرح الإتقان v3.0 — بدء النشر $(date '+%Y-%m-%d %H:%M:%S')"
echo "══════════════════════════════════════════════"

cd "${SARH_PATH}"

# ── 1. وضع الموقع في وضع الصيانة ─────────────────────────────────────────
echo "⏸  تفعيل وضع الصيانة..."
${ARTISAN} down --retry=60 --render="errors::503" || true

# ── 2. جلب آخر التحديثات من GitHub ───────────────────────────────────────
echo "⬇️  جلب الكود من GitHub..."
git fetch origin main
git reset --hard origin/main

# ── 3. تثبيت التبعيات PHP ────────────────────────────────────────────────
echo "📦  تثبيت Composer..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# ── 4. تنفيذ ترحيلات قاعدة البيانات ──────────────────────────────────────
echo "🗃  تنفيذ الترحيلات..."
${ARTISAN} migrate --force

# ── 5. تحسين الأداء ──────────────────────────────────────────────────────
echo "⚡  تحسين الأداء..."
${ARTISAN} config:cache
${ARTISAN} route:cache
${ARTISAN} view:cache
${ARTISAN} event:cache
${ARTISAN} filament:cache-components

# ── 6. رابط التخزين ──────────────────────────────────────────────────────
echo "🔗  إنشاء رابط storage..."
${ARTISAN} storage:link --force

# ── 7. ربط المجلد العام بـ public_html ───────────────────────────────────
echo "🔗  ربط public_html..."
if [ -L "${PUBLIC_HTML}" ]; then
    rm "${PUBLIC_HTML}"
fi
ln -s "${SARH_PATH}/public" "${PUBLIC_HTML}"

# ── 8. إعادة تشغيل قوائم الانتظار ───────────────────────────────────────
echo "🔄  إعادة تشغيل Queue Workers..."
${ARTISAN} queue:restart || true

# ── 9. تنظيف السجل ───────────────────────────────────────────────────────
echo "🧹  مسح ملف السجل القديم..."
> "${SARH_PATH}/storage/logs/laravel.log"

# ── 10. إنهاء وضع الصيانة ────────────────────────────────────────────────
echo "▶️  استئناف الموقع..."
${ARTISAN} up

echo ""
echo "══════════════════════════════════════════════"
echo "  ✅ النشر اكتمل في $(date '+%Y-%m-%d %H:%M:%S')"
echo "══════════════════════════════════════════════"
echo ""
