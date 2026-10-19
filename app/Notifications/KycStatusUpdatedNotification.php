<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KycStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $status,
        public int $level,
        public ?string $message = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->status === 'verified'
            ? 'KYC Verification Approved - Tier ' . $this->level
            : 'KYC Verification Failed';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->first_name},");

        if ($this->status === 'verified') {
            $mail->line("Congratulations! Your identity verification was successful.")
                ->line("Your account is now upgraded to KYC Tier {$this->level}, unlocking higher transaction and wallet limits.");
        } else {
            $mail->line("Your KYC verification attempt was not successful.")
                ->lineIf($this->message !== null, "Reason: {$this->message}")
                ->line("Please ensure your BVN, Date of Birth, and Phone Number match your official records and try again.");
        }

        return $mail;
    }
}
