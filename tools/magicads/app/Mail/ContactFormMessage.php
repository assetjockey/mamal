<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Message produced by the public "Contact us" form.
 *
 * The transport is the admin-configured SMTP account: AppServiceProvider::
 * configureMailFromSettings() pushes the `email_settings` row into the runtime
 * mail config on every boot, so this Mailable sends through exactly the same
 * connection the "Send test email" button in Admin → Backend → SMTP uses.
 *
 * The "from" identity stays the configured SMTP sender (many providers reject
 * or spam-bin mail that spoofs an arbitrary From). The visitor's address is set
 * as Reply-To so the recipient can just hit reply to answer them.
 */
class ContactFormMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string       $firstName  Visitor's first name.
     * @param  string       $lastName   Visitor's last name.
     * @param  string       $email      Visitor's email (used as Reply-To).
     * @param  string|null  $company    Optional company name.
     * @param  string       $subjectKey Selected subject option value.
     * @param  string       $messageBody Free-text message.
     */
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $company,
        public string $subjectKey,
        public string $messageBody,
    ) {
    }

    public function envelope(): Envelope
    {
        $name = trim($this->firstName . ' ' . $this->lastName);

        return new Envelope(
            subject: __('New contact enquiry') . ': ' . $this->subjectLabel(),
            replyTo: [new Address($this->email, $name !== '' ? $name : $this->email)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
            with: [
                'fullName'     => trim($this->firstName . ' ' . $this->lastName),
                'email'        => $this->email,
                'company'      => $this->company,
                'subjectLabel' => $this->subjectLabel(),
                'messageBody'  => $this->messageBody,
            ],
        );
    }

    /**
     * Human-readable label for the selected subject option. Falls back to the
     * raw value so an unexpected option still renders something meaningful.
     */
    protected function subjectLabel(): string
    {
        return match ($this->subjectKey) {
            'general'     => __('General inquiry'),
            'demo'        => __('Request a demo'),
            'enterprise'  => __('Enterprise pricing'),
            'support'     => __('Technical support'),
            'partnership' => __('Partnership'),
            default       => $this->subjectKey,
        };
    }
}
