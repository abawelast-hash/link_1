<?php

namespace App\Events;

use App\Models\{User, Badge};
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadgeAwarded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User  $user,
        public readonly Badge $badge
    ) {}
}
