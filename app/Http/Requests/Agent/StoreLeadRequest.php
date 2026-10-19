<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by route middleware (role:agent,admin)
    }

    public function rules(): array
    {
        return [
            'full_name'      => ['required', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'business_name'  => ['nullable', 'string', 'max:255'],
            'category'       => ['nullable', 'in:farmer,vendor,agro_business'],
            'sub_category'   => ['nullable', 'string', 'max:255'],
            'location'       => ['nullable', 'string', 'max:255'],
            'state'          => ['nullable', 'string', 'max:100'],
            'source'         => ['nullable', 'in:market_visit,call,referral,walk_in,other'],
            'needs_discovery'=> ['nullable', 'array'],
            'notes'          => ['nullable', 'string'],
        ];
    }
}