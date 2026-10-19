<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    /**
     * Get all payment methods for authenticated user.
     */
    public function index(): JsonResponse
    {
        $methods = PaymentMethod::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $methods,
        ]);
    }

    /**
     * Add a payment method (card or bank account).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'           => 'required|in:card,bank',
            'card_number'    => 'nullable|string',
            'expiry'         => 'nullable|string',
            'cvv'            => 'nullable|string',
            'bank_name'      => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:20',
            'account_name'   => 'nullable|string|max:100',
            'is_default'     => 'nullable|boolean',
        ]);

        $userId = Auth::id();
        $type = $validated['type'];

        $last4 = null;
        $cardBrand = null;
        $cardToken = null;

        if ($type === 'card' && !empty($validated['card_number'])) {
            $cleaned = preg_replace('/\D/', '', $validated['card_number']);
            $last4 = substr($cleaned, -4);
            $cardBrand = str_starts_with($cleaned, '4') ? 'visa' : (str_starts_with($cleaned, '5') ? 'mastercard' : 'verve');
            $cardToken = 'tok_' . bin2hex(random_bytes(10));
        }

        // If this is set as default or first method, make others not default
        $isDefault = (bool) ($validated['is_default'] ?? false);
        if ($isDefault || PaymentMethod::where('user_id', $userId)->count() === 0) {
            $isDefault = true;
            PaymentMethod::where('user_id', $userId)->update(['is_default' => false]);
        }

        $method = PaymentMethod::create([
            'user_id'        => $userId,
            'type'           => $type,
            'last4'          => $last4 ?? '4242',
            'expiry'         => $validated['expiry'] ?? null,
            'card_brand'     => $cardBrand ?? 'card',
            'card_token'     => $cardToken,
            'bank_name'      => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'account_name'   => $validated['account_name'] ?? null,
            'is_default'     => $isDefault,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment method added successfully',
            'data'    => $method,
        ], 201);
    }

    /**
     * Update an existing payment method.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $method = PaymentMethod::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'type'           => 'nullable|in:card,bank',
            'card_number'    => 'nullable|string',
            'expiry'         => 'nullable|string',
            'cvv'            => 'nullable|string',
            'bank_name'      => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:20',
            'account_name'   => 'nullable|string|max:100',
            'is_default'     => 'nullable|boolean',
        ]);

        if (isset($validated['is_default']) && $validated['is_default']) {
            PaymentMethod::where('user_id', Auth::id())->where('id', '!=', $method->id)->update(['is_default' => false]);
            $method->is_default = true;
        }

        if (isset($validated['type'])) {
            $method->type = $validated['type'];
        }

        if (!empty($validated['card_number'])) {
            $cleaned = preg_replace('/\D/', '', $validated['card_number']);
            $method->last4 = substr($cleaned, -4);
            $method->card_brand = str_starts_with($cleaned, '4') ? 'visa' : (str_starts_with($cleaned, '5') ? 'mastercard' : 'verve');
        }

        if (isset($validated['expiry'])) {
            $method->expiry = $validated['expiry'];
        }

        if (isset($validated['bank_name'])) {
            $method->bank_name = $validated['bank_name'];
        }

        if (isset($validated['account_number'])) {
            $method->account_number = $validated['account_number'];
        }

        if (isset($validated['account_name'])) {
            $method->account_name = $validated['account_name'];
        }

        $method->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment method updated successfully',
            'data'    => $method,
        ]);
    }

    /**
     * Delete a payment method.
     */
    public function destroy($id): JsonResponse
    {
        $method = PaymentMethod::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $method->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment method deleted successfully',
        ]);
    }
}
