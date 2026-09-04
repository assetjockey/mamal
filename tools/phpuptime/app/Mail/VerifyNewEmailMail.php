<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

class VerifyNewEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     */
    protected PendingUserEmail $user;

    /**
     * Create a new message instance.
     */
    public function __construct(PendingUserEmail $user)
    {
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: formatTitle([__('Verify new email address'), config('settings.title')]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'vendor.notifications.email',
            with: [
                'introLines' => [__('Please click the button below to verify your email address.')],
                'actionText' => __('Verify new email address'),
                'actionUrl' => $this->user->verificationUrl(),
                'outroLines' => [__('If you did not update your email address, no further action is required.')]
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
