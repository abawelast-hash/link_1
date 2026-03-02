<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsToMany};

class Badge extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon',
        'points_reward', 'condition_type', 'condition_params', 'is_active',
    ];

    protected $casts = [
        'condition_params' => 'array',
        'points_reward'    => 'integer',
        'is_active'        => 'boolean',
    ];

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('awarded_at', 'period')
            ->withTimestamps();
    }
}
