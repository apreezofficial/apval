<?php

declare(strict_types=1);

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'privacy' => 'nullable|in:public,private,invite_only',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Community name is required',
            'name.max' => 'Community name cannot exceed 255 characters',
            'description.max' => 'Description cannot exceed 2000 characters',
            'privacy.in' => 'Privacy must be: public, private, or invite_only',
        ];
    }
}