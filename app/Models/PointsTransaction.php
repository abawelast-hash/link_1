<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointsTransaction extends Model
{
    protected $fillable = [
        'user_id', 'points', 'type', 'reason',
        'transactionable_type', 'transactionable_id', 'adjusted_by',
    ];

    protected $casts = ['points' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }
}
