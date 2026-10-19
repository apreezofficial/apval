<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiateWithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
   public function rules(): array
    {
        return [
            'currency' => ['required', 'in:NGN'],
            'amount' => ['required','numeric','gt:0'],
            'bank_code' => ['required','string','max:20'],
            'account_number' => ['required','string','min:10','max:10'],
            'account_name' => ['required','string','max:100'],
            'narration' => ['nullable','string','max:50'],
            'idempotency_key' => ['required','string','max:80'],
        ];
    }
}
