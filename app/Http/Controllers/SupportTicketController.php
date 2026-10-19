<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Services\Support\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController
{
    public function __construct(
        private SupportTicketService $ticketService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tickets = $this->ticketService->getUserTickets($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $tickets,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:transaction,order,kyc,account,general'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string', 'max:5000'],
            'order_id' => ['nullable', 'string', 'exists:orders,id'],
            'transaction_id' => ['nullable', 'string', 'exists:transactions,id'],
            'attachment_url' => ['nullable', 'url', 'max:500'],
        ]);

        $ticket = $this->ticketService->createTicket(
            $request->user(),
            $request->subject,
            $request->category,
            $request->priority ?? 'medium',
            $request->message,
            $request->order_id,
            $request->transaction_id,
            $request->attachment_url
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Support ticket created successfully.',
            'data' => $ticket,
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $ticket = SupportTicket::with(['user', 'messages.sender', 'order', 'transaction'])
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $ticket,
        ]);
    }

    public function reply(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachment_url' => ['nullable', 'url', 'max:500'],
        ]);

        $ticket = SupportTicket::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $reply = $this->ticketService->addReply(
            $request->user(),
            $ticket->id,
            $request->message,
            false,
            $request->attachment_url
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Reply added.',
            'data' => $reply,
        ], 201);
    }
}
