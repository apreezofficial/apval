<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket,
        public string $replyMessage
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Reply on Ticket #{$this->ticket->ticket_number}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("An agent has replied to your support ticket #{$this->ticket->ticket_number}:")
            ->line("\"{$this->replyMessage}\"")
            ->line("You can log in to your account to respond or view full details.");
    }
}
