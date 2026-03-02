<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnomalyLog extends Model
{
    protected $fillable = [
        'user_id', 'type', 'description', 'metadata',
        'severity', 'is_reviewed', 'reviewed_by',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'is_reviewed' => 'boolean',
    ];

    public function user(): BelongsTo       { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo   { return $this->belongsTo(User::class, 'reviewed_by'); }
}
