<?php

declare(strict_types=1);

namespace App\Http\Requests\Ads;

use Illuminate\Foundation\Http\FormRequest;

class ApplyAdvertiserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:150'],
            'industry' => ['nullable', 'string', 'max:100'],
        ];
    }
}