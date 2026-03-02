<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WhistleblowerReport extends Model
{
    protected $fillable = [
        'token', 'user_id', 'subject', 'body', 'category',
        'severity', 'status', 'attachment', 'is_anonymous',
        'assigned_to', 'internal_notes', 'resolved_at',
    ];

    protected $casts = [
        'is_anonymous'  => 'boolean',
        'resolved_at'   => 'datetime',
    ];

    /** إنشاء رمز سري تلقائياً */
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (empty($model->token)) {
                $model->token = Str::random(64);
            }
        });
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'low'      => '🟢 منخفض',
            'medium'   => '🟡 متوسط',
            'high'     => '🟠 عالي',
            'critical' => '🔴 حرج',
            default    => $this->severity,
        ];
    }
}
