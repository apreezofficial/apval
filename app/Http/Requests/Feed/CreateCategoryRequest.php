<?php

declare(strict_types=1);

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class CreateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:feed_categories,name',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7|regex:/^#[A-Fa-f0-9]{6}$/',
            'parent_id' => 'nullable|uuid|exists:feed_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive,archived',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category name already exists.',
            'color.regex' => 'Color must be a valid hex code (e.g., #FF5733).',
            'parent_id.exists' => 'The parent category does not exist.',
        ];
    }
}