<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendAccountStateChangedNotification extends Notification
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
        $state = __('notifications.deactivated');
        if($notifiable->is_active) $state = __('notifications.activated');

        return (new MailMessage)
            ->subject(__('notifications.your_account_has_been', ['state' => $state]))
            ->greeting(__('notifications.greetings_with_name',['name' => $notifiable->name]). ',')
            ->line(__('notifications.your_account_has_been', ['state' => $state]).'.')
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
