<?php

declare(strict_types=1);

namespace App\Services\Support;

use App\Exceptions\Domain\InvalidRequestException;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketReplyNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupportTicketService
{
    /**
     * Create a new support ticket with an initial message.
     */
    public function createTicket(
        User $user,
        string $subject,
        string $category,
        string $priority,
        string $message,
        ?string $orderId = null,
        ?string $transactionId = null,
        ?string $attachmentUrl = null
    ): SupportTicket {
        return DB::transaction(function () use ($user, $subject, $category, $priority, $message, $orderId, $transactionId, $attachmentUrl) {
            $ticketNumber = 'TKT-' . strtoupper(Str::random(8));

            $ticket = SupportTicket::create([
                'id' => (string) Str::uuid(),
                'ticket_number' => $ticketNumber,
                'user_id' => $user->id,
                'subject' => $subject,
                'category' => $category,
                'priority' => $priority,
                'status' => 'open',
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
            ]);

            SupportTicketMessage::create([
                'id' => (string) Str::uuid(),
                'ticket_id' => $ticket->id,
                'sender_id' => $user->id,
                'is_admin_reply' => false,
                'message' => $message,
                'attachment_url' => $attachmentUrl,
            ]);

            // Dispatch Email Notification to User
            $user->notify(new TicketCreatedNotification($ticket));

            return $ticket->load(['user', 'messages.sender']);
        });
    }

    /**
     * Add a message reply to a support ticket thread.
     */
    public function addReply(
        User $sender,
        string $ticketId,
        string $message,
        bool $isAdmin = false,
        ?string $attachmentUrl = null
    ): SupportTicketMessage {
        return DB::transaction(function () use ($sender, $ticketId, $message, $isAdmin, $attachmentUrl) {
            $ticket = SupportTicket::with('user')->findOrFail($ticketId);

            if ($ticket->status === 'closed') {
                throw new InvalidRequestException('This ticket has been closed and cannot receive new replies.');
            }

            $ticketMessage = SupportTicketMessage::create([
                'id' => (string) Str::uuid(),
                'ticket_id' => $ticket->id,
                'sender_id' => $sender->id,
                'is_admin_reply' => $isAdmin,
                'message' => $message,
                'attachment_url' => $attachmentUrl,
            ]);

            // If admin replies, change ticket status to in_progress & notify user
            if ($isAdmin) {
                $ticket->update(['status' => 'in_progress']);

                if ($ticket->user) {
                    $ticket->user->notify(new TicketReplyNotification($ticket, $message));
                }
            } else {
                // If user replies, re-open if previously resolved
                if ($ticket->status === 'resolved') {
                    $ticket->update(['status' => 'open']);
                }
            }

            return $ticketMessage->load('sender');
        });
    }

    /**
     * Update ticket status (Admin).
     */
    public function updateStatus(string $ticketId, string $status): SupportTicket
    {
        $ticket = SupportTicket::findOrFail($ticketId);
        $ticket->update(['status' => $status]);

        return $ticket->fresh();
    }

    /**
     * Get paginated support tickets for a user.
     */
    public function getUserTickets(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return SupportTicket::with('messages')
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->paginate($perPage);
    }
}
