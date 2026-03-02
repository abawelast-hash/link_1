<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $auth): bool
    {
        return $auth->security_level >= 5;
    }

    public function view(User $auth, User $user): bool
    {
        // المدير العام يرى الجميع، المديرون يرون فروعهم فقط
        if ($auth->isGodMode()) return true;
        if ($auth->security_level >= 5) {
            return $auth->branch_id === $user->branch_id;
        }
        return $auth->id === $user->id;
    }

    public function create(User $auth): bool
    {
        return $auth->security_level >= 5;
    }

    public function update(User $auth, User $user): bool
    {
        if ($auth->isGodMode()) return true;
        if ($auth->security_level >= 5) {
            return $auth->branch_id === $user->branch_id;
        }
        return $auth->id === $user->id;
    }

    public function delete(User $auth, User $user): bool
    {
        return $auth->isGodMode() || ($auth->security_level >= 8 && $auth->id !== $user->id);
    }

    public function adjustPoints(User $auth, User $user): bool
    {
        return $auth->isGodMode();
    }
}
