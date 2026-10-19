<?php

declare(strict_types=1);

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendTextMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:5000',
            'reply_to_message_id' => 'nullable|uuid|exists:chat_messages,id',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Message content is required',
            'content.max' => 'Message cannot exceed 5000 characters',
            'reply_to_message_id.exists' => 'The message you are replying to does not exist',
        ];
    }
}