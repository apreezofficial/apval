<?php

declare(strict_types=1);

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'nullable|string|max:5000',
            'category_id' => 'nullable|uuid|exists:feed_categories,id',
            'hashtags' => 'nullable|array|max:20',
            'hashtags.*' => 'string|max:50',
            'location' => 'nullable|array',
            'location.latitude' => 'numeric|between:-90,90',
            'location.longitude' => 'numeric|between:-180,180',
            'location.name' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'content.max' => 'Post content cannot exceed 5000 characters.',
            'hashtags.max' => 'You can use a maximum of 20 hashtags.',
        ];
    }
}