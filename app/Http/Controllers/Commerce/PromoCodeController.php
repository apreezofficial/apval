<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromoCodeController extends Controller
{
    /**
     * Get active promos available for the user.
     */
    public function index(): JsonResponse
    {
        $now = now();
        $promos = PromoCode::where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->latest()
            ->get()
            ->map(function ($promo) {
                return [
                    'id'                     => $promo->id,
                    'code'                   => $promo->code,
                    'description'            => $promo->description,
                    'discount_type'          => $promo->discount_type,
                    'discount_value'         => (float) $promo->discount_value,
                    'minimum_order_amount'   => (float) $promo->minimum_order_amount,
                    'maximum_discount_amount'=> $promo->maximum_discount_amount ? (float) $promo->maximum_discount_amount : null,
                    'expiry_date'            => $promo->expires_at ? $promo->expires_at->toIso8601String() : null,
                    'expires_at'             => $promo->expires_at ? $promo->expires_at->toIso8601String() : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $promos,
        ]);
    }

    /**
     * Validate and calculate discount for a promo code.
     */
    public function apply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'         => 'required|string',
            'order_amount' => 'nullable|numeric|min:0',
        ]);

        $code = strtoupper(trim($validated['code']));
        $promo = PromoCode::where('code', $code)->first();

        if (!$promo || !$promo->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired promo code.',
            ], 200);
        }

        $userId = Auth::id();
        $userUsageCount = PromoCodeUsage::where('promo_code_id', $promo->id)
            ->where('user_id', $userId)
            ->count();

        if ($promo->per_user_limit !== null && $userUsageCount >= $promo->per_user_limit) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reached the maximum usage limit for this promo code.',
            ], 200);
        }

        $orderAmount = (float) ($validated['order_amount'] ?? 0);
        if ($orderAmount > 0 && $promo->minimum_order_amount > 0 && $orderAmount < $promo->minimum_order_amount) {
            return response()->json([
                'success' => false,
                'message' => "This promo code requires a minimum order amount of ₦" . number_format($promo->minimum_order_amount, 2),
            ], 200);
        }

        $discountAmount = $promo->calculateDiscount($orderAmount > 0 ? $orderAmount : 100);

        return response()->json([
            'success'  => true,
            'message'  => 'Promo code applied successfully',
            'discount' => [
                'code'                    => $promo->code,
                'discount_type'           => $promo->discount_type,
                'discount_value'          => (float) $promo->discount_value,
                'discount_amount'         => $discountAmount,
                'minimum_order_amount'    => (float) $promo->minimum_order_amount,
                'maximum_discount_amount' => $promo->maximum_discount_amount ? (float) $promo->maximum_discount_amount : null,
            ],
            'data' => [
                'code'           => $promo->code,
                'discount_type'  => $promo->discount_type,
                'discount_value' => (float) $promo->discount_value,
            ],
        ]);
    }
}
