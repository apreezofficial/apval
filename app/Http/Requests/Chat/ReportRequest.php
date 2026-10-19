<?php

declare(strict_types=1);

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|in:spam,scam,fraud,harassment,fake_product,offensive_content,illegal_activity,other',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Report reason is required',
            'reason.in' => 'Invalid report reason',
            'description.max' => 'Description cannot exceed 1000 characters',
        ];
    }
}