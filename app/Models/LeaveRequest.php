<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'start_date', 'end_date', 'days_count',
        'type', 'reason', 'status',
        'reviewed_by', 'review_notes', 'reviewed_at', 'attachment',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'reviewed_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $model) {
            if ($model->start_date && $model->end_date) {
                $model->days_count = $model->start_date->diffInDays($model->end_date) + 1;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => '⏳ قيد المراجعة',
            'approved' => '✅ مقبول',
            'rejected' => '❌ مرفوض',
            default    => $this->status,
        ];
    }
}
