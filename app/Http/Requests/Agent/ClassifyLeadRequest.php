<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;

class ClassifyLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classification' => ['required', 'in:new,hot,warm,cold,activated'],
            // Optional manual override; if omitted, controller applies §14 default rules
            'next_follow_up_at' => ['nullable', 'date', 'after_or_equal:now'],
        ];
    }
}