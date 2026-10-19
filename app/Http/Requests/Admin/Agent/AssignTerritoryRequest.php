<?php

namespace App\Http\Requests\Admin\Agent;

use Illuminate\Foundation\Http\FormRequest;

class AssignTerritoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'territory' => ['required', 'string', 'max:255'],
            'assigned_manager_id' => ['nullable', 'uuid', 'exists:users,id'],
        ];
    }
}