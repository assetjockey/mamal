<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonitorStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The monitor object.
     */
    public object $monitor;

    /**
     * The email copy.
     */
    public array $copy;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(object $monitor, array $copy)
    {
        $this->monitor = $monitor;
        $this->copy = $copy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: formatTitle([$this->copy['subject'], config('settings.title')]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.monitor-status',
            with: [
                'color' => ($this->monitor->alert->error ? 'error' : 'success'),
                'greeting' => $this->copy['greeting'],
                'introLines' => [$this->copy['message']],
                'actionText' => __('View monitor'),
                'actionUrl' => route('monitors.overview', ['id' => $this->monitor->id, 'token' => $this->monitor->token])
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
