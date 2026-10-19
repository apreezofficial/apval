<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatBlockedUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class MessageService
{
    // File size limits (in bytes)
    const MAX_IMAGE_SIZE = 10 * 1024 * 1024; // 10MB
    const MAX_VIDEO_SIZE = 50 * 1024 * 1024; // 50MB
    const MAX_DOCUMENT_SIZE = 20 * 1024 * 1024; // 20MB
    const MAX_TEXT_LENGTH = 5000;

    /**
     * Send a text message
     */
    public function sendTextMessage(
        string $conversationId,
        string $senderId,
        string $content,
        ?string $replyToMessageId = null
    ): ChatMessage {
        $this->validateMessageLength($content);
        $this->validateSenderInConversation($conversationId, $senderId);
        $this->checkIfBlocked($conversationId, $senderId);

        $message = ChatMessage::create([
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'type' => ChatMessage::TYPE_TEXT,
            'content' => $content,
            'reply_to_message_id' => $replyToMessageId,
        ]);

        $this->updateConversationLastMessage($conversationId, $content);
        $this->checkForScamKeywords($message);

        return $message;
    }

    /**
     * Send a media message (image, video, voice, document)
     */
    public function sendMediaMessage(
        string $conversationId,
        string $senderId,
        UploadedFile $file,
        string $type,
        ?string $content = null,
        ?string $replyToMessageId = null
    ): ChatMessage {
        $this->validateSenderInConversation($conversationId, $senderId);
        $this->checkIfBlocked($conversationId, $senderId);
        $this->validateMediaFile($file, $type);

        // Store the file
        $path = $this->storeMediaFile($file, $type);
        $mimeType = $file->getMimeType();

        $message = ChatMessage::create([
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'type' => $type,
            'content' => $content,
            'media_url' => $path,
            'media_type' => $mimeType,
            'reply_to_message_id' => $replyToMessageId,
            'media_metadata' => [
                'size' => $file->getSize(),
                'original_name' => $file->getClientOriginalName(),
            ],
        ]);

        $preview = match ($type) {
            ChatMessage::TYPE_IMAGE => '📷 Photo',
            ChatMessage::TYPE_VIDEO => '🎥 Video',
            ChatMessage::TYPE_VOICE => '🎤 Voice message',
            ChatMessage::TYPE_DOCUMENT => '📄 Document',
            default => 'Media',
        };

        $this->updateConversationLastMessage($conversationId, $preview);

        return $message;
    }

    /**
     * Share a product card in conversation
     */
    public function shareProductCard(
        string $conversationId,
        string $senderId,
        string $productId,
        ?string $content = null,
        ?string $replyToMessageId = null
    ): ChatMessage {
        $this->validateSenderInConversation($conversationId, $senderId);
        $this->checkIfBlocked($conversationId, $senderId);

        $message = ChatMessage::create([
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'type' => ChatMessage::TYPE_PRODUCT_CARD,
            'content' => $content,
            'product_id' => $productId,
            'reply_to_message_id' => $replyToMessageId,
        ]);

        $this->updateConversationLastMessage($conversationId, '🛍️ Shared a product');

        return $message;
    }

    /**
     * Share an order card in conversation
     */
    public function shareOrderCard(
        string $conversationId,
        string $senderId,
        string $orderId,
        ?string $content = null,
        ?string $replyToMessageId = null
    ): ChatMessage {
        $this->validateSenderInConversation($conversationId, $senderId);
        $this->checkIfBlocked($conversationId, $senderId);

        $message = ChatMessage::create([
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'type' => ChatMessage::TYPE_ORDER_CARD,
            'content' => $content,
            'order_id' => $orderId,
            'reply_to_message_id' => $replyToMessageId,
        ]);

        $this->updateConversationLastMessage($conversationId, '📦 Shared an order');

        return $message;
    }

    /**
     * Send a location message
     */
    public function sendLocationMessage(
        string $conversationId,
        string $senderId,
        float $latitude,
        float $longitude,
        ?string $locationName = null,
        ?string $replyToMessageId = null
    ): ChatMessage {
        $this->validateSenderInConversation($conversationId, $senderId);
        $this->checkIfBlocked($conversationId, $senderId);

        $message = ChatMessage::create([
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'type' => ChatMessage::TYPE_LOCATION,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location_name' => $locationName,
            'reply_to_message_id' => $replyToMessageId,
        ]);

        $this->updateConversationLastMessage($conversationId, '📍 Shared location');

        return $message;
    }

    /**
     * Get messages for a conversation with pagination
     */
    public function getConversationMessages(
        string $conversationId,
        string $userId,
        int $perPage = 50,
        ?string $beforeMessageId = null
    ) {
        $conversation = ChatConversation::findOrFail($conversationId);

        if (!$conversation->hasParticipant($userId)) {
            throw new \Exception('You are not a participant of this conversation');
        }

        $query = ChatMessage::with(['sender', 'replyTo.sender', 'product', 'order'])
            ->where('conversation_id', $conversationId)
            ->notDeleted()
            ->orderBy('created_at', 'desc');

        if ($beforeMessageId) {
            $beforeMessage = ChatMessage::findOrFail($beforeMessageId);
            $query->where('created_at', '<', $beforeMessage->created_at);
        }

        return $query->paginate($perPage);
    }

    /**
     * Mark messages as read
     */
    public function markMessagesAsRead(string $conversationId, string $userId): void
    {
        $conversation = ChatConversation::findOrFail($conversationId);

        if (!$conversation->hasParticipant($userId)) {
            throw new \Exception('You are not a participant of this conversation');
        }

        // Update participant's last read timestamp
        $participant = $conversation->participants()
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->first();

        if ($participant) {
            $participant->updateLastRead();
        }

        // Mark all unread messages as read
        ChatMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Add reaction to a message
     */
    public function addReaction(string $messageId, string $userId, string $emoji): void
    {
        $message = ChatMessage::findOrFail($messageId);
        $conversation = $message->conversation;

        if (!$conversation->hasParticipant($userId)) {
            throw new \Exception('You are not a participant of this conversation');
        }

        $message->addReaction($userId, $emoji);
    }

    /**
     * Remove reaction from a message
     */
    public function removeReaction(string $messageId, string $userId): void
    {
        $message = ChatMessage::findOrFail($messageId);
        $conversation = $message->conversation;

        if (!$conversation->hasParticipant($userId)) {
            throw new \Exception('You are not a participant of this conversation');
        }

        $message->removeReaction($userId);
    }

    /**
     * Edit a message
     */
    public function editMessage(string $messageId, string $userId, string $newContent): ChatMessage
    {
        $message = ChatMessage::findOrFail($messageId);

        if ($message->sender_id !== $userId) {
            throw new \Exception('You can only edit your own messages');
        }

        if ($message->is_deleted) {
            throw new \Exception('Cannot edit a deleted message');
        }

        $this->validateMessageLength($newContent);

        $message->update([
            'content' => $newContent,
            'is_edited' => true,
        ]);

        return $message->fresh();
    }

    /**
     * Delete a message (soft delete)
     */
    public function deleteMessage(string $messageId, string $userId): void
    {
        $message = ChatMessage::findOrFail($messageId);

        if ($message->sender_id !== $userId) {
            throw new \Exception('You can only delete your own messages');
        }

        $message->softDelete();
    }

    /**
     * Forward a message to another conversation
     */
    public function forwardMessage(
        string $messageId,
        string $userId,
        string $targetConversationId
    ): ChatMessage {
        $originalMessage = ChatMessage::findOrFail($messageId);
        $conversation = $originalMessage->conversation;

        if (!$conversation->hasParticipant($userId)) {
            throw new \Exception('You are not a participant of the source conversation');
        }

        $targetConversation = ChatConversation::findOrFail($targetConversationId);

        if (!$targetConversation->hasParticipant($userId)) {
            throw new \Exception('You are not a participant of the target conversation');
        }

        // Create a copy of the message
        return ChatMessage::create([
            'id' => (string) Str::uuid(),
            'conversation_id' => $targetConversationId,
            'sender_id' => $userId,
            'type' => $originalMessage->type,
            'content' => $originalMessage->content,
            'media_url' => $originalMessage->media_url,
            'media_type' => $originalMessage->media_type,
            'product_id' => $originalMessage->product_id,
            'order_id' => $originalMessage->order_id,
            'latitude' => $originalMessage->latitude,
            'longitude' => $originalMessage->longitude,
            'location_name' => $originalMessage->location_name,
        ]);
    }

    // Private helper methods

    private function validateMessageLength(string $content): void
    {
        if (strlen($content) > self::MAX_TEXT_LENGTH) {
            throw new \Exception("Message exceeds maximum length of " . self::MAX_TEXT_LENGTH . " characters");
        }
    }

    private function validateSenderInConversation(string $conversationId, string $senderId): void
    {
        $conversation = ChatConversation::findOrFail($conversationId);

        if (!$conversation->hasParticipant($senderId)) {
            throw new \Exception('You are not a participant of this conversation');
        }

        if ($conversation->is_locked) {
            throw new \Exception('This conversation is locked');
        }
    }

    private function checkIfBlocked(string $conversationId, string $senderId): void
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $otherParticipants = $conversation->participants()
            ->where('user_id', '!=', $senderId)
            ->whereNull('left_at')
            ->pluck('user_id');

        foreach ($otherParticipants as $participantId) {
            if (ChatBlockedUser::isBlocked($participantId, $senderId)) {
                throw new \Exception('You are blocked by one of the participants');
            }
        }
    }

    private function validateMediaFile(UploadedFile $file, string $type): void
    {
        $maxSize = match ($type) {
            ChatMessage::TYPE_IMAGE => self::MAX_IMAGE_SIZE,
            ChatMessage::TYPE_VIDEO => self::MAX_VIDEO_SIZE,
            ChatMessage::TYPE_DOCUMENT => self::MAX_DOCUMENT_SIZE,
            ChatMessage::TYPE_VOICE => self::MAX_IMAGE_SIZE, // Same as image
            default => self::MAX_IMAGE_SIZE,
        };

        if ($file->getSize() > $maxSize) {
            $maxSizeMB = $maxSize / (1024 * 1024);
            throw new \Exception("File size exceeds maximum limit of {$maxSizeMB}MB");
        }
    }

    private function storeMediaFile(UploadedFile $file, string $type): string
    {
        $folder = match ($type) {
            ChatMessage::TYPE_IMAGE => 'chat/images',
            ChatMessage::TYPE_VIDEO => 'chat/videos',
            ChatMessage::TYPE_VOICE => 'chat/voice',
            ChatMessage::TYPE_DOCUMENT => 'chat/documents',
            default => 'chat/media',
        };

        return $file->store($folder, 'public');
    }

    private function updateConversationLastMessage(string $conversationId, string $preview): void
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $conversation->updateLastMessage($preview);
    }

    private function checkForScamKeywords(ChatMessage $message): void
    {
        if ($message->type !== ChatMessage::TYPE_TEXT || !$message->content) {
            return;
        }

        $scamKeywords = [
            'pay outside app',
            'send money to bank',
            'transfer directly',
            'western union',
            'bitcoin',
            'crypto payment',
        ];

        $content = strtolower($message->content);

        foreach ($scamKeywords as $keyword) {
            if (str_contains($content, $keyword)) {
                $message->flagAsSuspicious("Contains potential scam keyword: {$keyword}");
                break;
            }
        }
    }
}