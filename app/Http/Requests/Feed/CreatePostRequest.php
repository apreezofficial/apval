<?php

declare(strict_types=1);

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'nullable|string|max:5000',
            'category_id' => 'nullable|uuid|exists:feed_categories,id',
            'post_type' => 'nullable|in:normal,image,video,product,event,announcement',
            'media' => 'nullable|array|max:10',
            'media.*' => 'file|max:51200', // 50MB max
            'hashtags' => 'nullable|array|max:20',
            'hashtags.*' => 'string|max:50',
            'mentions' => 'nullable|array|max:20',
            'mentions.*' => 'uuid|exists:users,id',
            'product_id' => 'nullable|uuid|exists:products,id',
            'event_id' => 'nullable|uuid|exists:events,id',
            'location' => 'nullable|array',
            'location.latitude' => 'required_with:location|numeric|between:-90,90',
            'location.longitude' => 'required_with:location|numeric|between:-180,180',
            'location.name' => 'nullable|string|max:255',
            'location.address' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'content.max' => 'Post content cannot exceed 5000 characters.',
            'media.max' => 'You can upload a maximum of 10 media files.',
            'media.*.max' => 'Each media file cannot exceed 50MB.',
            'hashtags.max' => 'You can use a maximum of 20 hashtags.',
            'mentions.max' => 'You can mention a maximum of 20 users.',
            'category_id.exists' => 'The selected category does not exist.',
            'product_id.exists' => 'The selected product does not exist.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Must have either content or media
            if (empty($this->content) && empty($this->file('media'))) {
                $validator->errors()->add('content', 'A post must have either content or media.');
            }

            // Product posts must have product_id
            if ($this->post_type === 'product' && empty($this->product_id)) {
                $validator->errors()->add('product_id', 'Product posts must be linked to a product.');
            }

            // Event posts must have event_id
            if ($this->post_type === 'event' && empty($this->event_id)) {
                $validator->errors()->add('event_id', 'Event posts must be linked to an event.');
            }
        });
    }
}