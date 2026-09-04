<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketCreatedNotification extends Notification
{
    use Queueable;
    private $details;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('user.tickets.view', $this->details['ticketNumber']);
        $subcopy = mailTemplates('If you are having trouble clicking the button, just copy and paste the URL below into your web browser', 'new ticket created notification');

        return (new MailMessage)
            ->subject(str_replace('{ticket_number}', '[#' . $this->details['ticketNumber'] . ']', mailTemplates('New Ticket Created {ticket_number}', 'new ticket created notification')))
            ->greeting(mailTemplates('Hello!', 'new ticket created notification'))
            ->line(mailTemplates('You are receiving this message because you had a new ticket opened by our support team you can click here to view it directly.', 'new ticket created notification'))
            ->action(mailTemplates('View Ticket', 'new ticket created notification'), $url)
            ->line(mailTemplates('You can reply directly on the ticket by going to your account then my tickets.', 'new ticket created notification'))
            ->salutation(mailTemplates('Regards', 'new ticket created notification'))
            ->markdown('vendor.notifications.email', [
                'subcopy' => $subcopy,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
