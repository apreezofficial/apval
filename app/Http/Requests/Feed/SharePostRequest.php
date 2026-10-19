<?php

declare(strict_types=1);

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class SharePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'share_type' => 'required|in:internal,whatsapp,twitter,facebook,telegram,link_copy,other',
            'caption' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'share_type.required' => 'Share type is required.',
            'share_type.in' => 'Invalid share type.',
            'caption.max' => 'Caption cannot exceed 1000 characters.',
        ];
    }
}