<?php

declare(strict_types=1);

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMediaMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type', 'image');
        
        $maxSize = match ($type) {
            'image' => 10240, // 10MB
            'video' => 51200, // 50MB
            'document' => 20480, // 20MB
            'voice' => 10240, // 10MB
            default => 10240,
        };

        return [
            'file' => 'required|file|max:' . $maxSize,
            'type' => 'required|in:image,video,voice,document',
            'content' => 'nullable|string|max:5000',
            'reply_to_message_id' => 'nullable|uuid|exists:chat_messages,id',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File is required',
            'file.max' => 'File size exceeds the maximum limit',
            'type.required' => 'Media type is required',
            'type.in' => 'Invalid media type. Must be: image, video, voice, or document',
        ];
    }
}