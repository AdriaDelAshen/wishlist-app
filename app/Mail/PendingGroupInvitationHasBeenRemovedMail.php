<?php

namespace App\Mail;

use App\Models\GroupInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendingGroupInvitationHasBeenRemovedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected GroupInvitation $groupInvitation;

    public function __construct(GroupInvitation $groupInvitation)
    {
        $this->groupInvitation = $groupInvitation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            subject: __('group.your_invitation_has_been_removed'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.group_invitations.removed',
            with: [
                'groupInvitation' => $this->groupInvitation
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
