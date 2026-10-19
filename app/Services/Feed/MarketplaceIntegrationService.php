<?php

declare(strict_types=1);

namespace App\Services\Feed;

use App\Models\FeedPost;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class MarketplaceIntegrationService
{
    /**
     * Create a post linked to a marketplace product
     */
    public function createProductPost(string $userId, string $productId, ?string $caption = null): FeedPost
    {
        $product = Product::findOrFail($productId);

        // Verify user owns the product
        if ($product->vendor_id !== $userId && $product->user_id !== $userId) {
            throw new \Exception('You can only create posts for your own products');
        }

        // Check product availability
        if ($product->status !== 'active') {
            throw new \Exception('Cannot create post for unavailable product');
        }

        $postService = app(PostService::class);

        return $postService->createPost(
            userId: $userId,
            content: $caption ?? "Check out my product: {$product->name}",
            categoryId: null,
            postType: 'product',
            productId: $productId,
        );
    }

    /**
     * Get product data for a post
     */
    public function getProductDataForPost(FeedPost $post): ?array
    {
        if (!$post->product_id) {
            return null;
        }

        $product = Product::find($post->product_id);

        if (!$product) {
            return null;
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'currency' => $product->currency ?? 'NGN',
            'image' => $product->images[0] ?? null,
            'availability' => $product->status === 'active' ? 'available' : 'unavailable',
            'vendor_name' => $product->vendor?->first_name ?? 'Unknown',
            'url' => "/marketplace/products/{$product->id}",
        ];
    }
}