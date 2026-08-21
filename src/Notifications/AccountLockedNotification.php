<?php

namespace MoRan1412\FilamentAntiBruteForce\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountLockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $ip,
        public readonly string $email,
        public readonly int $minutes,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('filament-anti-brute-force::anti-brute-force.notifications.lockout.subject'))
            ->greeting(__('filament-anti-brute-force::anti-brute-force.notifications.lockout.greeting'))
            ->line(__('filament-anti-brute-force::anti-brute-force.notifications.lockout.ip', ['ip' => $this->ip]))
            ->line(__('filament-anti-brute-force::anti-brute-force.notifications.lockout.email', ['email' => $this->email]))
            ->line(__('filament-anti-brute-force::anti-brute-force.notifications.lockout.duration', ['minutes' => $this->minutes]));
    }
}
