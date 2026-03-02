<?php

namespace App\Events;

use App\Models\AttendanceLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly AttendanceLog $log) {}
}
