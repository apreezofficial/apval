<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;

class LogFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel'               => ['required', 'in:call,visit,sms,whatsapp,in_app,other'],
            'outcome'               => ['required', 'in:no_answer,interested,not_interested,registered,activated,other'],
            'note'                  => ['nullable', 'string'],
            'classification_after'  => ['nullable', 'in:new,hot,warm,cold,activated'],
        ];
    }
}