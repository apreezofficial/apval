<?php

declare(strict_types=1);

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;

class ChangePinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_pin' => ['required', 'regex:/^\d{4,6}$/'],
            'new_pin' => ['required', 'regex:/^\d{4,6}$/', 'different:current_pin'],
            'new_pin_confirmation' => ['required', 'same:new_pin'],
            'otp' => ['nullable', 'string', 'max:10'],
        ];
    }
}
