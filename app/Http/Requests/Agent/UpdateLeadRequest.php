<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'      => ['sometimes', 'string', 'max:255'],
            'phone'          => ['sometimes', 'nullable', 'string', 'max:30'],
            'business_name'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'category'       => ['sometimes', 'nullable', 'in:farmer,vendor,agro_business'],
            'sub_category'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'location'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'state'          => ['sometimes', 'nullable', 'string', 'max:100'],
            'source'         => ['sometimes', 'nullable', 'in:market_visit,call,referral,walk_in,other'],
            'needs_discovery'=> ['sometimes', 'nullable', 'array'],
            'notes'          => ['sometimes', 'nullable', 'string'],
        ];
    }
}