<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class CreateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'             => ['required', 'string', 'in:direct,group,community,support'],
            'name'             => ['required_if:type,group,community', 'nullable', 'string', 'max:120'],
            'description'      => ['nullable', 'string', 'max:500'],
            'participant_ids'  => ['required_if:type,group,community', 'nullable', 'array', 'min:1'],
            'participant_ids.*'=> ['uuid'],
            // For direct chat: single target user
            'user_id'          => ['required_if:type,direct,support', 'nullable', 'uuid'],
        ];
    }
}
