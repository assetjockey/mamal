<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The payment instance.
     */
    public Payment $payment;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: formatTitle([$this->getTitle(), config('settings.title')])
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'vendor.notifications.email',
            with: $this->getMessageData()
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get the email title.
     */
    protected function getTitle(): string
    {
        return $this->payment->status == 'completed'
            ? __('Payment completed')
            : __('Payment cancelled');
    }

    /**
     * Get the email content data.
     */
    protected function getMessageData(): array
    {
        return $this->payment->status == 'completed'
            ? [
                'introLines' => [__('The payment was successful.') . ' ' . __('Thank you!')],
                'actionText' => __('Invoice'),
                'actionUrl' => route('account.invoices.show', [$this->payment->id]),
            ]
            : [
                'introLines' => [__('The payment was cancelled.')],
            ];
    }
}
