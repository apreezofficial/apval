<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_date'                    => ['required', 'date', 'before_or_equal:today'],
            'territory'                      => ['nullable', 'string', 'max:255'],

            'calls_count'                    => ['nullable', 'integer', 'min:0'],
            'market_visits_count'            => ['nullable', 'integer', 'min:0'],
            'new_prospects'                  => ['nullable', 'integer', 'min:0'],
            'qualified_prospects'            => ['nullable', 'integer', 'min:0'],

            'registrations'                  => ['nullable', 'integer', 'min:0'],
            'completed_registrations'        => ['nullable', 'integer', 'min:0'],
            'pending_registrations'          => ['nullable', 'integer', 'min:0'],
            'activated_users'                => ['nullable', 'integer', 'min:0'],

            'vendors_with_products'          => ['nullable', 'integer', 'min:0'],
            'products_uploaded_count'        => ['nullable', 'integer', 'min:0'],

            'first_time_transactions'        => ['nullable', 'integer', 'min:0'],
            'repeat_transactions'            => ['nullable', 'integer', 'min:0'],

            'customers_followed_up'          => ['nullable', 'integer', 'min:0'],
            'customers_requiring_followup'   => ['nullable', 'integer', 'min:0'],

            'challenges' => ['nullable', 'string'],
            'remarks'    => ['nullable', 'string'],
        ];
    }
}