<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketReplyNotification extends Notification
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
        $subcopy = mailTemplates('If you are having trouble clicking the button, just copy and paste the URL below into your web browser', 'ticket reply notification');
        return (new MailMessage)
            ->subject(str_replace('{ticket_number}', '[#' . $this->details['ticketNumber'] . ']', mailTemplates('Ticket {ticket_number} New Reply', 'ticket reply notification')))
            ->greeting(mailTemplates('Hello!', 'ticket reply notification'))
            ->line(mailTemplates('You are receiving this message because you had a ticket open and there is a new reply on it you can click here to view it directly.', 'ticket reply notification'))
            ->action(mailTemplates('View Ticket', 'ticket reply notification'), $url)
            ->line(mailTemplates('You can reply directly on the ticket by going to your account then my tickets.', 'ticket reply notification'))
            ->salutation(mailTemplates('Regards', 'ticket reply notification'))
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
