<?php

namespace App\Mail;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GroupInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected Group $group;
    protected string $token;
    protected bool $existingUser;

    public function __construct(Group $group, string $token, bool $existingUser)
    {
        $this->group = $group;
        $this->token = $token;
        $this->existingUser = $existingUser;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            subject: __('group.invited_to_join_a_new_group'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $url = $this->existingUser
            ? route('groups.accept_invite.existing', $this->token)
            : route('groups.accept_invite.new', $this->token);
        return new Content(
            markdown: 'mail.groups.invited',
            with: [
                'existingUser' => $this->existingUser,
                'url' => $url,
                'group' => $this->group
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
