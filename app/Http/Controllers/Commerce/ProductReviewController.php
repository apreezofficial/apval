<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Services\Commerce\ProductReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewController
{
    public function __construct(private ProductReviewService $reviewService) {}

    /**
     * Submit a review for a delivered order item.
     * POST /api/commerce/orders/items/{itemId}/reviews
     */
    public function store(Request $request, string $itemId): JsonResponse
    {
        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $review = $this->reviewService->createReview(
                $request->user(),
                $itemId,
                (int) $validated['rating'],
                $validated['comment'] ?? null
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Review submitted successfully.',
                'data'    => $review->load(['product:id,name', 'user:id,first_name,last_name']),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * View public reviews and rating summary for a product.
     * GET /api/commerce/marketplace/products/{id}/reviews
     */
    public function index(Request $request, string $id): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $result = $this->reviewService->getProductReviews($id, $perPage);

        return response()->json([
            'status' => 'success',
            'data'   => $result,
        ]);
    }
}
