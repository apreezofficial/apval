<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class MarketplaceSearchTool
{
    /**
     * Search for agricultural or food products in the AgreonPay marketplace.
     */
    public function search(string $query, ?float $maxPrice = null, int $limit = 5): array
    {
        $products = Product::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('category', 'LIKE', "%{$query}%");
            })
            ->when($maxPrice, function ($q) use ($maxPrice) {
                $q->where('price', '<=', $maxPrice);
            })
            ->with(['vendor:id,first_name,last_name'])
            ->limit($limit)
            ->get(['id', 'name', 'category', 'price', 'unit', 'stock_quantity', 'vendor_id']);

        if ($products->isEmpty()) {
            return [
                'found' => false,
                'message' => "No products matching '{$query}' were found in the marketplace right now.",
                'products' => []
            ];
        }

        $formattedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'price' => (float) $product->price,
                'unit' => $product->unit,
                'stock_available' => (float) $product->stock_quantity,
                'vendor' => trim("{$product->vendor->first_name} {$product->vendor->last_name}"),
                'is_perishable' => (bool) $product->is_perishable,
            ];
        })->toArray();

        return [
            'found' => true,
            'message' => "Found " . count($formattedProducts) . " matching products.",
            'products' => $formattedProducts
        ];
    }
}