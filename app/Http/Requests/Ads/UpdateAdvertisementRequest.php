<?php

declare(strict_types=1);

namespace App\Http\Requests\Ads;

use App\Enums\Ads\AdvertisementType;
use App\Enums\Ads\DestinationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::enum(AdvertisementType::class)],
            'vendor_id' => ['nullable', 'uuid', 'exists:vendor_profiles,id'],
            'product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'title' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'string', 'max:2000'],
            'cta' => ['sometimes', 'string', 'exists:ad_cta_options,label'],
            'destination_type' => ['sometimes', Rule::enum(DestinationType::class)],
            'destination_url' => ['nullable', 'string', 'max:500'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'product_price' => ['nullable', 'numeric', 'min:0'],
            'offer_start_date' => ['nullable', 'date'],
            'offer_end_date' => ['nullable', 'date', 'after_or_equal:offer_start_date'],
            'contact_information' => ['nullable', 'string', 'max:255'],
        ];
    }
}