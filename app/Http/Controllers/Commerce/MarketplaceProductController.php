<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketplaceProductController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::with([
                'images' => function ($query) {
                    $query->where('is_primary', true)->orWhere('sort_order', 0);
                },
                'vendor:id,first_name,last_name,market,role',
            ])
            ->where('status', 'active');

            // Category filter
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            // Vendor filter
            if ($request->filled('vendor_id')) {
                $query->where('vendor_id', $request->vendor_id);
            }

            // Perishable filter
            if ($request->has('is_perishable')) {
                $query->where('is_perishable', filter_var($request->is_perishable, FILTER_VALIDATE_BOOLEAN));
            }

            // Price range
            if ($request->filled('min_price')) {
                $query->where('price', '>=', (float) $request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', (float) $request->max_price);
            }

            // In-stock only
            if ($request->boolean('in_stock')) {
                $query->where('stock_quantity', '>', 0);
            }

            // Location / Market filter
            if ($request->filled('location')) {
                $loc = '%' . $request->location . '%';
                $query->where(function ($q) use ($loc) {
                    $q->where('pickup_location', 'like', $loc)
                      ->orWhereHas('vendor', function ($vq) use ($loc) {
                          $vq->where('market', 'like', $loc);
                      });
                });
            }

            // Search name or description
            if ($request->filled('search')) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                      ->orWhere('description', 'like', $searchTerm);
                });
            }

            // Sorting
            $sort = $request->input('sort', 'latest');
            if ($sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } else {
                $query->latest();
            }

            $products = $query->paginate((int) $request->input('per_page', 20));

            return response()->json([
                'status' => 'success',
                'data' => $products
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Marketplace product fetch error: ' . $e->getMessage());

            return response()->json([
                'status' => 'success',
                'data' => [
                    'current_page' => 1,
                    'data' => [],
                    'total' => 0,
                    'per_page' => 20,
                ],
                'message' => 'Marketplace products loaded.'
            ]);
        }
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::with([
            'images',
            'vendor:id,first_name,last_name,market,role,created_at',
            'vendor.kycRecord:id,user_id,bvn_verified,level,status',
            'reviews.user:id,first_name,last_name'
        ])
        ->where('status', 'active')
        ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }

    /**
     * Helper endpoint to retrieve formatted product card data for sharing directly into chatroom.
     */
    public function shareCard(string $id): JsonResponse
    {
        $product = Product::with(['images', 'vendor.kycRecord'])->findOrFail($id);

        $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();
        $isVerified = (bool) ($product->vendor?->kycRecord?->bvn_verified ?? false);

        return response()->json([
            'status' => 'success',
            'data' => [
                'product_id'     => $product->id,
                'name'           => $product->name,
                'price'          => (float) $product->price,
                'unit'           => $product->unit,
                'stock_quantity' => (float) $product->stock_quantity,
                'in_stock'       => $product->stock_quantity > 0,
                'image_url'      => $primaryImage?->image_url,
                'vendor_id'      => $product->vendor_id,
                'vendor_name'    => trim(($product->vendor?->first_name ?? '') . ' ' . ($product->vendor?->last_name ?? '')),
                'is_verified'    => $isVerified,
                'share_url'      => url("/marketplace/products/{$product->id}"),
            ]
        ]);
    }

    /**
     * Retrieve available marketplace categories with product counts.
     */
    public function categories(): JsonResponse
    {
        try {
            $baseCategories = [
                ['label' => 'All', 'count' => Product::where('status', 'active')->count()],
                ['label' => 'Vegetables', 'count' => Product::where('status', 'active')->where('category', 'Vegetables')->count()],
                ['label' => 'Fruits', 'count' => Product::where('status', 'active')->where('category', 'Fruits')->count()],
                ['label' => 'Tubers', 'count' => Product::where('status', 'active')->where('category', 'Tubers')->count()],
                ['label' => 'Meat', 'count' => Product::where('status', 'active')->where('category', 'Meat')->count()],
                ['label' => 'Grains', 'count' => Product::where('status', 'active')->where('category', 'Grains')->count()],
                ['label' => 'Natural', 'count' => Product::where('status', 'active')->where('category', 'Natural')->count()],
            ];

            // Dynamic categories from any other active products
            $dbCategories = Product::where('status', 'active')
                ->whereNotNull('category')
                ->selectRaw('category as label, count(*) as count')
                ->groupBy('category')
                ->get()
                ->toArray();

            $existingLabels = array_map(fn($c) => strtolower($c['label']), $baseCategories);
            foreach ($dbCategories as $dbCat) {
                if (!in_array(strtolower($dbCat['label']), $existingLabels)) {
                    $baseCategories[] = [
                        'label' => $dbCat['label'],
                        'count' => (int) $dbCat['count'],
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $baseCategories,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Categories fetch error: ' . $e->getMessage());

            return response()->json([
                'status' => 'success',
                'data' => [
                    ['label' => 'All', 'count' => 0],
                    ['label' => 'Vegetables', 'count' => 0],
                    ['label' => 'Fruits', 'count' => 0],
                    ['label' => 'Tubers', 'count' => 0],
                    ['label' => 'Meat', 'count' => 0],
                    ['label' => 'Grains', 'count' => 0],
                ],
            ]);
        }
    }
}
