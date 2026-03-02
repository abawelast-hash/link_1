<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyReport extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'year', 'month',
        'working_days', 'present_days', 'late_days', 'absent_days', 'excused_days',
        'total_overtime_hours', 'total_deduction', 'base_salary', 'net_salary',
        'total_points', 'generated_at',
    ];

    protected $casts = [
        'year'                 => 'integer',
        'month'                => 'integer',
        'working_days'         => 'integer',
        'present_days'         => 'integer',
        'late_days'            => 'integer',
        'absent_days'          => 'integer',
        'excused_days'         => 'integer',
        'total_overtime_hours' => 'decimal:2',
        'total_deduction'      => 'decimal:2',
        'base_salary'          => 'decimal:2',
        'net_salary'           => 'decimal:2',
        'total_points'         => 'integer',
        'generated_at'         => 'datetime',
    ];

    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }

    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو',  6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        return ($months[$this->month] ?? $this->month) . ' ' . $this->year;
    }
}
