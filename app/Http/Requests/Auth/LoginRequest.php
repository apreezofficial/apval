<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $login = $this->input('login') ?? $this->input('email') ?? $this->input('phone') ?? $this->input('username');
        if (is_string($login)) {
            $login = trim($login);
        }

        if ($login !== null) {
            $this->merge(['login' => $login]);
        }
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'email' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
