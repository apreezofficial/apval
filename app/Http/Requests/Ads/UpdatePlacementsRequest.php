<?php

declare(strict_types=1);

namespace App\Http\Requests\Ads;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlacementsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'placement_ids' => ['required', 'array', 'min:1'],
            'placement_ids.*' => ['uuid', 'exists:ad_placements,id'],
        ];
    }
}