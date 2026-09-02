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
        // A custom view rather than the markdown builder: the code is the whole
        // message, and the stock template can only render it as one more line
        // of body text under Laravel's own logo and footer.
        return (new MailMessage())
            ->subject('Your ZIN-WORKS verification code: ' . $this->code)
            ->view(
                ['emails.verify-code', 'emails.verify-code-text'],
                [
                    'code' => $this->code,
                    'ttlMinutes' => EmailVerificationCode::TTL_MINUTES,
                    'email' => $notifiable->email,
                ]
            );
    }
}
