<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Circular extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'body', 'priority', 'created_by',
        'target_branches', 'target_levels', 'attachment',
        'published_at', 'expires_at',
    ];

    protected $casts = [
        'target_branches' => 'array',
        'target_levels'   => 'array',
        'published_at'    => 'datetime',
        'expires_at'      => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(CircularRead::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now())
                     ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }
}
