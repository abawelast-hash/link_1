<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class AttendanceLog extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'shift_id', 'attendance_date',
        'check_in_at', 'check_out_at',
        'check_in_lat', 'check_in_lng',
        'check_out_lat', 'check_out_lng',
        'check_in_distance', 'status',
        'delay_minutes', 'financial_deduction',
        'overtime_hours', 'points_earned',
        'is_manual', 'notes',
    ];

    protected $casts = [
        'attendance_date'     => 'date',
        'check_in_at'         => 'datetime',
        'check_out_at'        => 'datetime',
        'check_in_lat'        => 'decimal:8',
        'check_in_lng'        => 'decimal:8',
        'check_out_lat'       => 'decimal:8',
        'check_out_lng'       => 'decimal:8',
        'check_in_distance'   => 'decimal:2',
        'delay_minutes'       => 'integer',
        'financial_deduction' => 'decimal:2',
        'overtime_hours'      => 'decimal:2',
        'points_earned'       => 'integer',
        'is_manual'           => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'on_time' => '✅ في الموعد',
            'late'    => '⚠️ متأخر',
            'absent'  => '❌ غائب',
            'excused' => '📋 معذور',
            default   => $this->status,
        ];
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'on_time' => 'success',
            'late'    => 'warning',
            'absent'  => 'danger',
            'excused' => 'info',
            default   => 'gray',
        };
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('attendance_date', $year)
                     ->whereMonth('attendance_date', $month);
    }
}
