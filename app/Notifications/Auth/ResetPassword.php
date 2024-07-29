<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class ResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a notification instance.
     */
    public function __construct(public string $token) {}

    /**
     * Get the notification's channels.
     *
     * @param  \App\Models\User  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  \App\Models\User  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $resetUrl = $this->resetUrl($notifiable);

        /** @var int */
        $expiresIn = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Reset Password Notification')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $resetUrl)
            ->line("This password reset link will expire in $expiresIn minutes.")
            ->line('If you did not request a password reset, no further action is required.');
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  \App\Models\User  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        return Str::of(route('auth.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false))->prepend(Config::get('invoicing.frontend_url'))->toString(); // @phpstan-ignore-line
    }
}
