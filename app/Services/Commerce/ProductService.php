<?php

namespace App\Services\Commerce;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function createProduct(string $vendorId, array $data): Product
    {
        return DB::transaction(function () use ($vendorId, $data) {
            $product = Product::create([
                'vendor_id' => $vendorId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'price' => $data['price'],
                'unit' => $data['unit'],
                'stock_quantity' => $data['stock_quantity'] ?? 0,
                'min_order_quantity' => $data['min_order_quantity'] ?? 1,
                'pickup_location' => $data['pickup_location'] ?? null,
                'is_perishable' => $data['is_perishable'] ?? false,
                'logistics_preference' => $data['logistics_preference'] ?? null,
                'status' => 'active',
            ]);

            if (($data['stock_quantity'] ?? 0) > 0) {
                $product->inventoryMovements()->create([
                    'type' => 'addition',
                    'quantity' => $data['stock_quantity'],
                    'balance_after' => $data['stock_quantity'],
                    'reference_type' => 'initial_stock',
                ]);
            }

            if (!empty($data['images'])) {
                foreach ($data['images'] as $index => $image) {
                    $product->images()->create([
                        'image_url' => $image['url'],
                        'is_primary' => $image['is_primary'] ?? ($index === 0),
                        'sort_order' => $index,
                    ]);
                }
            }

            return $product->load('images');
        });
    }

    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            // Only safely updatable fields. Stock goes through InventoryService.
            $product->update(array_intersect_key($data, array_flip([
                'name', 'description', 'category', 'price', 'unit', 
                'min_order_quantity', 'pickup_location', 'is_perishable', 'logistics_preference',
                'status'
            ])));

            if (isset($data['images'])) {
                // For simplicity, we replace existing images if the array is provided
                $product->images()->delete();
                
                foreach ($data['images'] as $index => $image) {
                    $product->images()->create([
                        'image_url' => $image['url'],
                        'is_primary' => $image['is_primary'] ?? ($index === 0),
                        'sort_order' => $index,
                    ]);
                }
            }

            return $product->load('images');
        });
    }

    public function deleteProduct(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            // Delete related images first
            $product->images()->delete();
            
            // Delete product
            return $product->delete();
        });
    }
}
