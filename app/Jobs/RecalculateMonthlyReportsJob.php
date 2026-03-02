<?php

namespace App\Jobs;

use App\Models\{Branch, User};
use App\Services\FinancialReportingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

/**
 * وظيفة: إعادة احتساب التقارير الشهرية لجميع الموظفين.
 */
class RecalculateMonthlyReportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 2;

    public function __construct(
        public readonly int $year,
        public readonly int $month
    ) {}

    public function handle(FinancialReportingService $service): void
    {
        Branch::where('is_active', true)->each(function (Branch $branch) use ($service) {
            $service->generateBranchMonthlyReports($branch, $this->year, $this->month);
        });
    }
}
