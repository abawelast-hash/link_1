<?php

namespace App\Console\Commands;

use App\Models\{User, Branch};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * أمر تثبيت النظام وإنشاء المدير العام.
 *
 * الاستخدام: php artisan sarh:install
 */
class InstallSarh extends Command
{
    protected $signature   = 'sarh:install';
    protected $description = 'تثبيت نظام صرح الإتقان وإنشاء حساب المدير العام';

    public function handle(): int
    {
        $this->info('🚀 بدء تثبيت نظام صرح الإتقان v3.0...');

        // ── 1. إنشاء فرع رئيسي افتراضي ──────────────────────
        $branch = Branch::firstOrCreate(
            ['code' => 'HQ'],
            [
                'name'             => 'المقر الرئيسي',
                'city'             => 'الرياض',
                'latitude'         => 24.7136,
                'longitude'        => 46.6753,
                'geofence_radius'  => 17,
                'is_active'        => true,
            ]
        );

        $this->line("  ✅ الفرع الرئيسي: {$branch->name}");

        // ── 2. إنشاء حساب المدير العام ────────────────────────
        $email    = $this->ask('البريد الإلكتروني للمدير العام', 'abdullah@sarh.app');
        $password = $this->secret('كلمة المرور (مخفية)') ?? 'Goolbx512@@';
        $name     = $this->ask('الاسم الكامل', 'المدير العام');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name'            => $name,
                'password'        => Hash::make($password),
                'security_level'  => 10,
                'is_super_admin'  => true,
                'branch_id'       => $branch->id,
                'is_active'       => true,
                'employee_id'     => 'GOD-001',
            ]
        );

        $this->line("  ✅ المدير العام: {$admin->email} (Level 10 — God Mode)");

        // ── 3. بذر الشارات الافتراضية ─────────────────────────
        $this->seedDefaultBadges();

        // ── 4. بذر وإعدادات المنافسة ───────────────────────────
        $this->seedCompetitionSettings();

        $this->newLine();
        $this->info('✅ تم التثبيت بنجاح!');
        $this->table(
            ['العنصر', 'القيمة'],
            [
                ['لوحة الإدارة', url('/admin')],
                ['بوابة الموظف', url('/app')],
                ['البريد الإلكتروني', $email],
                ['المستوى الأمني', '10 (God Mode)'],
            ]
        );

        return self::SUCCESS;
    }

    private function seedDefaultBadges(): void
    {
        $badges = [
            [
                'name'            => 'سيد الانضباط',
                'slug'            => 'discipline-master',
                'icon'            => '🏆',
                'points_reward'   => 200,
                'condition_type'  => 'monthly_no_late',
                'description'     => 'شهر كامل بدون تأخير',
            ],
            [
                'name'            => 'السلسلة الحديدية',
                'slug'            => 'iron-streak',
                'icon'            => '⛓️',
                'points_reward'   => 70,
                'condition_type'  => 'streak_days',
                'condition_params' => ['days' => 7],
                'description'     => '7 أيام متتالية في الموعد',
            ],
            [
                'name'            => 'السلسلة الذهبية',
                'slug'            => 'gold-streak',
                'icon'            => '🔗',
                'points_reward'   => 500,
                'condition_type'  => 'streak_days',
                'condition_params' => ['days' => 30],
                'description'     => '30 يوماً متتالياً في الموعد',
            ],
            [
                'name'            => 'صفر خسائر',
                'slug'            => 'zero-losses',
                'icon'            => '💎',
                'points_reward'   => 300,
                'condition_type'  => 'monthly_no_absent',
                'description'     => 'شهر كامل بدون غياب',
            ],
            [
                'name'            => 'موفر التكاليف',
                'slug'            => 'cost-saver',
                'icon'            => '💰',
                'points_reward'   => 150,
                'condition_type'  => 'monthly_overtime',
                'condition_params' => ['hours' => 50],
                'description'     => '50+ ساعة إضافية في الشهر',
            ],
        ];

        foreach ($badges as $badge) {
            \App\Models\Badge::firstOrCreate(['slug' => $badge['slug']], $badge);
        }

        $this->line('  ✅ الشارات الافتراضية: ' . count($badges) . ' شارة');
    }

    private function seedCompetitionSettings(): void
    {
        $settings = [
            ['key' => 'points_on_time', 'value' => '10',  'description' => 'نقاط الحضور في الموعد'],
            ['key' => 'points_late',    'value' => '0',   'description' => 'نقاط الحضور المتأخر'],
            ['key' => 'points_absent',  'value' => '-5',  'description' => 'نقاط الغياب (سالبة)'],
        ];

        foreach ($settings as $s) {
            \App\Models\CompetitionSetting::firstOrCreate(['key' => $s['key']], $s);
        }

        $this->line('  ✅ إعدادات المنافسة الافتراضية');
    }
}
