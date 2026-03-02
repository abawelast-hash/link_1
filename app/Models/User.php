<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo, BelongsToMany};
use Filament\Models\Contracts\{FilamentUser, HasAvatar};
use Filament\Panel;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password',
        'employee_id', 'phone', 'avatar',
        'security_level', 'is_super_admin',
        'branch_id', 'position', 'department',
        'hourly_rate', 'total_points', 'is_active',
        'fcm_token',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'security_level'    => 'integer',
        'is_super_admin'    => 'boolean',
        'hourly_rate'       => 'decimal:2',
        'total_points'      => 'integer',
        'is_active'         => 'boolean',
    ];

    // ──────────────────────────────────────────
    // Filament
    // ──────────────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->security_level >= 4 && $this->is_active;
        }

        return $this->security_level >= 1 && $this->is_active;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    // ──────────────────────────────────────────
    // العلاقات
    // ──────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function userShifts(): HasMany
    {
        return $this->hasMany(UserShift::class);
    }

    public function currentShift()
    {
        return $this->userShifts()
            ->where('is_current', true)
            ->with('shift')
            ->latest()
            ->first()
            ?->shift;
    }

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('awarded_at', 'period')
            ->withTimestamps();
    }

    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function monthlyReports(): HasMany
    {
        return $this->hasMany(MonthlyReport::class);
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────

    /** هل لديه صلاحية god mode */
    public function isGodMode(): bool
    {
        return $this->security_level >= 10 || $this->is_super_admin;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->security_level) {
            1  => 'متدرب',
            2  => 'موظف',
            3  => 'موظف أول',
            4  => 'قائد فريق',
            5  => 'مدير فرع',
            6  => 'مدير إقليمي',
            7  => 'مدير عمليات',
            8  => 'نائب المدير العام',
            9  => 'مدير تنفيذي',
            10 => 'المدير العام',
            default => 'غير محدد',
        };
    }

    /** إضافة/طرح نقاط وحفظ المعاملة */
    public function adjustPoints(int $points, string $type, string $reason, $transactionable = null, ?User $adjustedBy = null): void
    {
        $this->increment('total_points', $points);

        $transaction = new PointsTransaction([
            'user_id'       => $this->id,
            'points'        => $points,
            'type'          => $type,
            'reason'        => $reason,
            'adjusted_by'   => $adjustedBy?->id,
        ]);

        if ($transactionable) {
            $transaction->transactionable()->associate($transactionable);
        }

        $transaction->save();

        // تحديث نقاط الفرع
        if ($this->branch_id) {
            $this->branch->increment('total_points', $points);
            $this->branch->recalculateLevel();
        }
    }
}
