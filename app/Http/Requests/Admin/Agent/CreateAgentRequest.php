<?php

namespace App\Http\Requests\Admin\Agent;

use Illuminate\Foundation\Http\FormRequest;

class CreateAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by route middleware (role:admin)
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'phone'      => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password'   => ['required', 'string', 'min:8'],
            'territory'  => ['nullable', 'string', 'max:255'],
            'assigned_manager_id' => ['nullable', 'uuid', 'exists:users,id'],
        ];
    }
}