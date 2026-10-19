<?php

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authed via sanctum middleware on route
    }

    public function rules(): array
    {
        return [
            'store_name' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'store_address' => ['nullable', 'string', 'max:255'],
            'return_policy' => ['nullable', 'string', 'max:2000'],
            'delivery_information' => ['nullable', 'string', 'max:2000'],
            'store_logo_url' => ['nullable', 'string', 'max:2048'],
            'is_open' => ['nullable', 'boolean'],
        ];
    }
}
