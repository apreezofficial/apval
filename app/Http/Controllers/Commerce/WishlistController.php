<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\WishlistItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist products.
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        $items = WishlistItem::where('user_id', $userId)
            ->with(['product'])
            ->latest()
            ->get();

        $products = $items->filter(fn($item) => $item->product !== null)->map(function ($item) {
            $p = $item->product;
            return [
                'id'          => $p->id,
                'name'        => $p->name,
                'price'       => (float) $p->price,
                'description' => $p->description,
                'category_id' => $p->category_id,
                'image'       => $p->image_url ?? ($p->images[0] ?? null),
                'images'      => $p->images ?? [],
                'unit'        => $p->unit ?? 'kg',
                'stock'       => $p->stock_quantity ?? 0,
                'vendor_id'   => $p->vendor_id,
                'added_at'    => $item->created_at->toIso8601String(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => $products,
        ]);
    }

    /**
     * Add a product to the wishlist.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $userId = Auth::id();
        $productId = $validated['product_id'];

        $wishlistItem = WishlistItem::firstOrCreate([
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);

        $product = Product::find($productId);

        return response()->json([
            'status'  => 'success',
            'message' => 'Product added to wishlist',
            'data'    => [
                'id'       => $product->id,
                'name'     => $product->name,
                'price'    => (float) $product->price,
                'item_id'  => $wishlistItem->id,
            ],
        ], 201);
    }

    /**
     * Remove a product from the wishlist by product ID.
     */
    public function destroy($productId): JsonResponse
    {
        $userId = Auth::id();

        WishlistItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Product removed from wishlist',
        ]);
    }
}
