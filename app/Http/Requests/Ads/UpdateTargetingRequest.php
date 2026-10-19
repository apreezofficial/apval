<?php

declare(strict_types=1);

namespace App\Http\Requests\Ads;

use App\Enums\Ads\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTargetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string'],
            'states' => ['nullable', 'array'],
            'states.*' => ['string'],
            'cities' => ['nullable', 'array'],
            'cities.*' => ['string'],
            'delivery_areas' => ['nullable', 'array'],
            'delivery_areas.*' => ['string'],
            'min_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'max_age' => ['nullable', 'integer', 'min:0', 'max:120', 'gte:min_age'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string'],
            'marketplace_categories' => ['nullable', 'array'],
            'marketplace_categories.*' => ['string'],
        ];
    }
}