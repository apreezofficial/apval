<?php

declare(strict_types=1);

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class DispatchOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pickup_address'   => ['required', 'string', 'max:500'],
            'delivery_address' => ['required', 'string', 'max:500'],
        ];
    }
}
