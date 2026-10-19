<?php

declare(strict_types=1);

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class ReportFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_type' => 'required|in:post,comment,user',
            'target_id' => 'required|uuid',
            'reason' => 'required|in:spam,scam_fraud,harassment,hate_abuse,sexual_inappropriate,false_information,illegal_content,other',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'target_type.required' => 'Target type is required.',
            'target_id.required' => 'Target ID is required.',
            'reason.required' => 'Report reason is required.',
            'reason.in' => 'Invalid report reason.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ];
    }
}