<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Exceptions\Domain\InsufficientStockException;
use App\Exceptions\Domain\InvalidRequestException;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function adjustStock(Product $product, string $type, float $quantity, ?string $refType = null, ?string $refId = null): Product
    {
        if ($quantity <= 0) {
            throw new InvalidRequestException('Quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($product, $type, $quantity, $refType, $refId) {
            $product->refresh();
            $currentStock = (float) $product->stock_quantity;
            $newStock = $currentStock;

            if (in_array($type, ['addition', 'release'], true)) {
                $newStock = $currentStock + $quantity;
            } elseif (in_array($type, ['subtraction', 'reservation', 'fulfillment'], true)) {
                if ($currentStock < $quantity) {
                    throw new InsufficientStockException("Insufficient stock available for {$type}.");
                }
                $newStock = $currentStock - $quantity;
            } else {
                throw new InvalidRequestException('Invalid inventory movement type.');
            }

            // Record the movement
            InventoryMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $quantity,
                'balance_after' => $newStock,
                'reference_type' => $refType,
                'reference_id' => $refId,
            ]);

            // Update product stock safely
            $product->update(['stock_quantity' => $newStock]);

            return $product;
        });
    }
}
