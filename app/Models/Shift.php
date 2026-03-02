<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Shift extends Model
{
    protected $fillable = [
        'name', 'branch_id', 'start_time', 'end_time',
        'grace_minutes', 'is_active',
    ];

    protected $casts = [
        'grace_minutes' => 'integer',
        'is_active'     => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function userShifts(): HasMany
    {
        return $this->hasMany(UserShift::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
