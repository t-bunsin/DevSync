<?php

namespace App\Notifications;

use App\Models\EmailVerificationCode;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Carries the six-digit registration code to the address being proved.
 *
 * Mail only, and deliberately no link: the point of the code is that it is
 * read out of the inbox and typed back into the session that asked for it, so
 * a forwarded email is not a working login.
 */
class VerifyEmailCode extends Notification
{
    public function __construct(private string $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Your ZIN-WORKS verification code: ' . $this->code)
            ->greeting('Welcome to ZIN-WORKS')
            ->line('Enter this code to finish creating your account:')
            ->line('**' . $this->code . '**')
            ->line(sprintf(
                'The code expires in %d minutes. If you did not sign up, you can ignore this email.',
                EmailVerificationCode::TTL_MINUTES
            ))
            ->salutation('— The ZIN-WORKS team');
    }
}
