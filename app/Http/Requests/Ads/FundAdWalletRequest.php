<?php

declare(strict_types=1);

namespace App\Http\Requests\Ads;

use Illuminate\Foundation\Http\FormRequest;

class UploadAdMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:51200'],
        ];
    }
}