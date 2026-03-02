<?php

namespace App\Console\Commands;

use App\Jobs\RecalculateMonthlyReportsJob;
use Illuminate\Console\Command;

/**
 * أمر إعادة احتساب التقارير الشهرية.
 *
 * الاستخدام: php artisan sarh:monthly-reports [--year=2026] [--month=3]
 */
class RecalculateMonthlyCommand extends Command
{
    protected $signature   = 'sarh:monthly-reports {--year= : السنة} {--month= : الشهر}';
    protected $description = 'إعادة احتساب التقارير المالية الشهرية لجميع الموظفين';

    public function handle(): int
    {
        $year  = (int) ($this->option('year')  ?? now()->year);
        $month = (int) ($this->option('month') ?? now()->month);

        $this->info("📊 جدولة إعادة احتساب تقارير {$year}/{$month}...");

        RecalculateMonthlyReportsJob::dispatch($year, $month);

        $this->info('✅ تم إضافة المهمة إلى قائمة الانتظار.');

        return self::SUCCESS;
    }
}
