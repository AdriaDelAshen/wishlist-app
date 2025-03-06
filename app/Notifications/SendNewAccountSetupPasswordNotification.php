<?php

namespace App\Notifications;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendNewAccountSetupPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $token = app(PasswordBroker::class)->createToken($notifiable);

        return (new MailMessage)
            ->subject('New account for Wishlist app')
            ->greeting('Hello '.$notifiable->name. ',')
            ->line('An administrator created your account without a password and it\'s time to set it up.')
            ->action('Setup Password', route('password.reset', [
                'token' => $token,
                'email' => $notifiable->email,
            ]))
            ->from('yzi_ger@hotmail.com');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
