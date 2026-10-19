<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\SupportTicket;
use App\Services\Support\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketAdminController
{
    public function __construct(
        private SupportTicketService $ticketService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::with(['user:id,first_name,last_name,email', 'messages'])
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->when($request->category, fn ($query) => $query->where('category', $request->category))
            ->latest('updated_at')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $tickets,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $ticket = SupportTicket::with(['user', 'messages.sender', 'order', 'transaction'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $ticket,
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:open,in_progress,resolved,closed'],
        ]);

        $ticket = $this->ticketService->updateStatus($id, $request->status);

        return response()->json([
            'status' => 'success',
            'message' => "Ticket status updated to '{$request->status}'.",
            'data' => $ticket,
        ]);
    }

    public function reply(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachment_url' => ['nullable', 'url', 'max:500'],
        ]);

        $reply = $this->ticketService->addReply(
            $request->user(),
            $id,
            $request->message,
            true,
            $request->attachment_url
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Admin reply sent.',
            'data' => $reply,
        ], 201);
    }
}
