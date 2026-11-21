<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** A short message shown on the student's Notifications page (and e-mailed when enabled). */
class ReservationNotice extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return config('reservation.notify_by_email') ? ['database', 'mail'] : ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->message);

        if ($this->url) {
            $mail->action('View details', $this->url);
        }

        return $mail->line('CPDO Venue Reservation System');
    }
}
