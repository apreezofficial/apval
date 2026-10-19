<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductReviewService
{
    /**
     * Create a new review for a delivered order item.
     */
    public function createReview(User $buyer, string $orderItemId, int $rating, ?string $comment = null): ProductReview
    {
        $orderItem = OrderItem::whereHas('order', function ($query) use ($buyer) {
            $query->where('user_id', $buyer->id);
        })->with('product')->findOrFail($orderItemId);

        if ($orderItem->vendor_status !== 'delivered') {
            throw new \InvalidArgumentException('You can only review items that have been delivered.');
        }

        if (ProductReview::where('order_item_id', $orderItemId)->exists()) {
            throw new \InvalidArgumentException('You have already submitted a review for this item.');
        }

        return DB::transaction(function () use ($buyer, $orderItem, $rating, $comment) {
            return ProductReview::create([
                'order_item_id' => $orderItem->id,
                'product_id'    => $orderItem->product_id,
                'user_id'       => $buyer->id,
                'rating'        => $rating,
                'comment'       => $comment,
            ]);
        });
    }

    /**
     * Get paginated reviews and summary rating stats for a product.
     */
    public function getProductReviews(string $productId, int $perPage = 15): array
    {
        $product = Product::findOrFail($productId);

        $reviewsPaginator = ProductReview::where('product_id', $productId)
            ->with(['user:id,first_name,last_name,avatar_url'])
            ->latest()
            ->paginate($perPage);

        $avgRating = ProductReview::where('product_id', $productId)->avg('rating') ?? 0.0;
        $totalReviews = ProductReview::where('product_id', $productId)->count();

        $ratingCounts = ProductReview::where('product_id', $productId)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $breakdown = [
            '5_star' => $ratingCounts[5] ?? 0,
            '4_star' => $ratingCounts[4] ?? 0,
            '3_star' => $ratingCounts[3] ?? 0,
            '2_star' => $ratingCounts[2] ?? 0,
            '1_star' => $ratingCounts[1] ?? 0,
        ];

        return [
            'product_id'     => $product->id,
            'average_rating' => round((float) $avgRating, 1),
            'total_reviews'  => $totalReviews,
            'rating_breakdown' => $breakdown,
            'reviews'        => $reviewsPaginator,
        ];
    }
}
