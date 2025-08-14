<?php

namespace App\Notifications;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupMemberLeftNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Group $group,
        public User $newMember
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Member left your group")
            ->line("{$this->newMember->name} has left the group \"{$this->group->name}\".")
            ->action('View Group', url("/groups/{$this->group->id}"));
    }
}
