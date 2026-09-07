<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * The password reset link, in ZIN-WORKS clothing.
 *
 * Extends the framework notification rather than replacing it so the token
 * handling, the signed reset URL and any ResetPassword::createUrlUsing hook
 * still apply — only the presentation is ours. The stock toMail() renders
 * through the markdown mailer, which ships Laravel's own logo and signs off
 * with config('app.name'); this swaps in the same hand-built template the
 * verification code email uses.
 */
class ResetPasswordLink extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage())
            ->subject('Reset your ZIN-WORKS password')
            ->view(
                ['emails.reset-password', 'emails.reset-password-text'],
                [
                    'url' => $url,
                    'expireMinutes' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
                    'email' => $notifiable->getEmailForPasswordReset(),
                    'firstName' => $notifiable->first_name ?? null,
                ]
            );
    }
}
