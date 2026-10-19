<?php

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authed via sanctum middleware on route
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['sometimes', 'string', 'max:100'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:50'],
            'stock_quantity' => ['sometimes', 'numeric', 'min:0'],
            'min_order_quantity' => ['nullable', 'numeric', 'min:0.01'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'is_perishable' => ['nullable', 'boolean'],
            'logistics_preference' => ['nullable', 'string', 'max:100'],
            'weight_kg' => ['nullable', 'numeric', 'min:0.01'],
            'volume_m3' => ['nullable', 'numeric', 'min:0.0001'],
            'status' => ['sometimes', 'string', 'in:active,inactive,pending_approval'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*.url' => ['required_with:images', 'url', 'max:255'],
            'images.*.is_primary' => ['nullable', 'boolean'],
        ];
    }
}
