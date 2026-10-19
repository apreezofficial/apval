<?php

namespace App\Http\Controllers\Commerce;

use App\Models\User;
use App\Services\Commerce\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController
{
    public function __construct(private CartService $cartService) {}

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $cartItems = $this->cartService->getCart($user);
        return response()->json(['status' => 'success', 'data' => $cartItems]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $cartItem = $this->cartService->addItem(
            $user,
            $request->product_id,
            (float) $request->quantity
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Item added to cart',
            'data' => $cartItem
        ], 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if ($request->quantity == 0) {
            $this->cartService->removeItem($user, $request->product_id);
            return response()->json(['status' => 'success', 'message' => 'Item removed from cart']);
        }

        $cartItem = $this->cartService->updateItem(
            $user,
            $request->product_id,
            (float) $request->quantity
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated',
            'data' => $cartItem
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $this->cartService->removeItem($user, $request->product_id);

        return response()->json(['status' => 'success', 'message' => 'Item removed from cart']);
    }

    public function clear()
    {
        /** @var User $user */
        $user = Auth::user();
        $this->cartService->clearCart($user);
        return response()->json(['status' => 'success', 'message' => 'Cart cleared']);
    }
}
