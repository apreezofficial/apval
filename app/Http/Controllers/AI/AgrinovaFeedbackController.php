<?php
declare(strict_types=1);
namespace App\Http\Controllers\AI;

use App\Models\AiConversation;
use App\Models\AiFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AgrinovaFeedbackController {
    public function submit(Request $request): JsonResponse {
        $request->validate([
            'conversation_id' => 'required|uuid',
            'message_id' => 'nullable|uuid',
            'rating' => 'required|in:helpful,not_helpful,incorrect,safety_concern,request_expert',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $conversation = AiConversation::where('id', $request->conversation_id)
            ->where('user_id', $user->id)->firstOrFail();

        $feedback = AiFeedback::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'message_id' => $request->message_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'intent_category' => $conversation->intent_category,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback. It helps us improve Agrinova.',
            'feedback_id' => $feedback->id,
        ]);
    }
}