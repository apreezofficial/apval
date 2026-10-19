<?php

declare(strict_types=1);

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class AddCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|min:1|max:2000',
            'parent_id' => 'nullable|uuid|exists:feed_comments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Comment content is required.',
            'content.min' => 'Comment cannot be empty.',
            'content.max' => 'Comment cannot exceed 2000 characters.',
            'parent_id.exists' => 'The parent comment does not exist.',
        ];
    }
}