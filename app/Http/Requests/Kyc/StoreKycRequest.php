<?php

declare(strict_types=1);

namespace App\Http\Requests\Kyc;

use Illuminate\Foundation\Http\FormRequest;

class StoreKycRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bvn' => ['required', 'string', 'size:11', 'regex:/^\d{11}$/'],
            'dob' => ['required', 'date_format:Y-m-d'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bvn.size'   => 'BVN must be exactly 11 digits.',
            'bvn.regex'  => 'BVN must contain only digits.',
            'dob.date_format' => 'Date of birth must be in YYYY-MM-DD format.',
        ];
    }
}
