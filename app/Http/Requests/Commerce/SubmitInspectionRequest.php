<?php

declare(strict_types=1);

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class SubmitInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'evidence_urls' => ['nullable', 'array'],
            'evidence_urls.*' => ['url'],
            'notes' => ['nullable', 'string'],
            'passed' => ['required', 'boolean'],
        ];
    }
}
