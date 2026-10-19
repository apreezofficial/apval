<?php

namespace App\Services\Commerce;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    public function getCart(User $user): Collection
    {
        return $user->cartItems()->with('product.images')->get();
    }

    public function addItem(User $user, string $productId, float $quantity): CartItem
    {
        /** @var CartItem|null $cartItem */
        $cartItem = $user->cartItems()->where('product_id', '=', $productId)->first();

        if ($cartItem) {
            $cartItem->quantity = (float) $cartItem->quantity + $quantity;
            $cartItem->save();
        } else {
            $cartItem = $user->cartItems()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return $cartItem->load('product');
    }

    public function updateItem(User $user, string $productId, float $quantity): CartItem
    {
        /** @var CartItem $cartItem */
        $cartItem = $user->cartItems()->where('product_id', '=', $productId)->firstOrFail();
        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->load('product');
    }

    public function removeItem(User $user, string $productId): void
    {
        $user->cartItems()->where('product_id', '=', $productId)->delete();
    }

    public function clearCart(User $user): void
    {
        $user->cartItems()->delete();
    }
}
