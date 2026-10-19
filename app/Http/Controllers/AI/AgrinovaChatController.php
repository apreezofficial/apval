<?php

declare(strict_types=1);

namespace App\Http\Controllers\AI;

use App\Services\AI\AgrinovaOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgrinovaChatController
{
    public function __construct(private AgrinovaOrchestrator $orchestrator) {}

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|uuid',
            'context' => 'nullable|array', 
        ]);

        $user = Auth::user();
        
        $response = $this->orchestrator->processMessage(
            $user->id,
            $request->message,
            $request->conversation_id,
            $request->context ?? []
        );

        return response()->json($response, $response['success'] ? 200 : 503);
    }
}