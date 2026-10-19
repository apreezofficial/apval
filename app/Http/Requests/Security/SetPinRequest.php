<?php

declare(strict_types=1);

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;

class SetPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'regex:/^\d{4,6}$/'],
            'pin_confirmation' => ['required', 'same:pin'],
            'password' => ['nullable', 'string'],
            'otp' => ['nullable', 'string', 'max:10'],
        ];
    }
}
