<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;

class LinkLeadCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // The customer must already have self-registered (§7 IMPORTANT).
            // The agent only links an existing account, never creates one.
            'user_id' => ['required', 'uuid', 'exists:users,id'],
        ];
    }
}