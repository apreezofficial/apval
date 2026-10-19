<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Support Ticket Opened #{$this->ticket->ticket_number}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Your support ticket #{$this->ticket->ticket_number} ('{$this->ticket->subject}') has been opened.")
            ->line("Our support team has received your request and will respond shortly.")
            ->line("Thank you for contacting AgreonPay support.");
    }
}
