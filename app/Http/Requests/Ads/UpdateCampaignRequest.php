<?php

declare(strict_types=1);

namespace App\Http\Requests\Ads;

use App\Enums\Ads\CampaignObjective;
use App\Enums\Ads\PricingModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'objective' => ['required', Rule::enum(CampaignObjective::class)],
            'pricing_model' => ['required', Rule::enum(PricingModel::class)],
            'package_id' => ['required_if:pricing_model,fixed_package', 'nullable', 'uuid', 'exists:ad_packages,id'],
            'daily_budget' => ['required_unless:pricing_model,fixed_package', 'nullable', 'numeric', 'min:1'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['required', 'date_format:H:i'],
        ];
    }
}