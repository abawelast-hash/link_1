<?php

namespace App\Notifications;

use App\Models\Circular;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CircularPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Circular $circular,
        protected User     $sender
    ) {
        $this->queue = 'notifications';
    }

    /**
     * قنوات التبليغ المفعّلة.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * تمثيل التبليغ داخل قاعدة البيانات.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'circular_published',
            'circular_id'  => $this->circular->id,
            'title'        => $this->circular->title,
            'sender_name'  => $this->sender->name,
            'priority'     => $this->circular->priority,
            'published_at' => $this->circular->published_at?->toISOString(),
        ];
    }

    /**
     * تمثيل التبليغ عبر البريد الإلكتروني.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $panelUrl = url('/app/circulars');

        return (new MailMessage)
            ->subject('تعميم جديد: ' . $this->circular->title)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('تم نشر تعميم جديد من قِبَل: ' . $this->sender->name)
            ->line('**' . $this->circular->title . '**')
            ->when($this->circular->body, fn ($m) => $m->line(strip_tags($this->circular->body)))
            ->action('عرض التعميم', $panelUrl)
            ->salutation('مع تحيات نظام صرح الإتقان');
    }
}
