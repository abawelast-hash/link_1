<?php

namespace App\Policies;

use App\Models\{User, AttendanceLog};
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendanceLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $auth): bool
    {
        return $auth->security_level >= 4;
    }

    public function view(User $auth, AttendanceLog $log): bool
    {
        if ($auth->isGodMode()) return true;
        if ($auth->security_level >= 5) return $auth->branch_id === $log->branch_id;
        return $auth->id === $log->user_id;
    }

    public function create(User $auth): bool
    {
        return $auth->security_level >= 4; // قائد فريق+
    }

    public function update(User $auth, AttendanceLog $log): bool
    {
        if ($auth->isGodMode()) return true;
        return $auth->security_level >= 5 && $auth->branch_id === $log->branch_id;
    }

    public function delete(User $auth, AttendanceLog $log): bool
    {
        return $auth->isGodMode();
    }
}
