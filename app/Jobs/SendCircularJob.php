<?php

namespace App\Jobs;

use App\Models\{Circular, User};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

/**
 * وظيفة: إرسال تعميم للمستخدمين المستهدفين.
 */
class SendCircularJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly Circular $circular) {}

    public function handle(): void
    {
        $query = User::where('is_active', true);

        if ($this->circular->target_branches) {
            $query->whereIn('branch_id', $this->circular->target_branches);
        }

        if ($this->circular->target_levels) {
            $query->whereIn('security_level', $this->circular->target_levels);
        }

        // إرسال إشعار داخل النظام (قابل التوسع للبريد أو Push)
        $query->each(function (User $user) {
            $user->notify(new \App\Notifications\CircularPublishedNotification($this->circular));
        });
    }
}
