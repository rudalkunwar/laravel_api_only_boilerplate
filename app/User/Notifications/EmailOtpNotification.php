<?php

declare(strict_types=1);

namespace App\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Delivers a one-time verification code to an email address.
 *
 * Queued so the API response is not blocked on the mail transport.
 */
final class EmailOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $otp,
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
            ->subject('Your verification code')
            ->greeting('Verify your email')
            ->line('Use the following one-time code to verify your email address:')
            ->line('**'.$this->otp.'**')
            ->line('This code expires in 10 minutes.')
            ->line('If you did not request this, no action is needed.');
    }
}
