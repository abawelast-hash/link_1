<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'city', 'address',
        'latitude', 'longitude', 'geofence_radius',
        'is_active', 'manager_id', 'total_points', 'level',
    ];

    protected $casts = [
        'latitude'         => 'decimal:8',
        'longitude'        => 'decimal:8',
        'geofence_radius'  => 'integer',
        'total_points'     => 'integer',
        'is_active'        => 'boolean',
    ];

    // ──────────────────────────────────────────
    // العلاقات
    // ──────────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    // ──────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────

    /** تحديث مستوى الفرع بناء على النقاط */
    public function recalculateLevel(): void
    {
        $points = $this->fresh()->total_points;

        $level = match (true) {
            $points >= 150 => 'أسطوري',
            $points >= 120 => 'ألماسي',
            $points >= 100 => 'ذهبي',
            $points >= 80  => 'فضي',
            $points >= 60  => 'برونزي',
            default        => 'مبتدئ',
        };

        $this->update(['level' => $level]);
    }

    public function getLevelEmojiAttribute(): string
    {
        return match ($this->level) {
            'أسطوري' => '🏆',
            'ألماسي' => '💎',
            'ذهبي'   => '🥇',
            'فضي'    => '🥈',
            'برونزي' => '🥉',
            default  => '🐢',
        };
    }
}
