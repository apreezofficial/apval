<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Http\Requests\Commerce\UpdateVendorProfileRequest;
use App\Models\VendorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class VendorProfileController
{
    /**
     * Fetch the authenticated vendor's store settings.
     */
    public function show(): JsonResponse
    {
        $user = Auth::user();

        // Lazy initialize the vendor profile if it does not exist yet
        $profile = $user->vendorProfile;
        if (!$profile) {
            $profile = $user->vendorProfile()->create([
                'store_name' => $user->first_name . ' ' . $user->last_name . "'s Store",
                'phone' => $user->phone,
                'email' => $user->email,
                'is_open' => true,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $profile,
        ]);
    }

    /**
     * Update the authenticated vendor's store settings.
     */
    public function update(UpdateVendorProfileRequest $request): JsonResponse
    {
        $user = Auth::user();

        $profile = $user->vendorProfile;
        if (!$profile) {
            $profile = $user->vendorProfile()->create([
                'store_name' => $request->input('store_name'),
                'phone' => $user->phone,
                'email' => $user->email,
                'is_open' => true,
            ]);
        }

        $profile->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Store settings updated successfully',
            'data' => $profile->fresh(),
        ]);
    }

    /**
     * Public storefront endpoint for customer view.
     */
    public function publicStore(string $vendorId): JsonResponse
    {
        $vendor = \App\Models\User::with(['vendorProfile', 'kycRecord'])->findOrFail($vendorId);
        $products = \App\Models\Product::with(['images' => function ($q) {
            $q->where('is_primary', true)->orWhere('sort_order', 0);
        }])
        ->where('vendor_id', $vendorId)
        ->where('status', 'active')
        ->latest()
        ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'vendor' => [
                    'id' => $vendor->id,
                    'name' => trim(($vendor->first_name ?? '') . ' ' . ($vendor->last_name ?? '')),
                    'store_name' => $vendor->vendorProfile?->store_name ?? (trim(($vendor->first_name ?? '') . ' ' . ($vendor->last_name ?? '')) . "'s Store"),
                    'bio' => $vendor->vendorProfile?->bio ?? 'Verified AgreonPay Agricultural Producer & Merchant.',
                    'market' => $vendor->market ?? 'National Marketplace',
                    'rating' => $vendor->vendorProfile?->rating ?? 4.8,
                    'is_verified' => (bool) ($vendor->kycRecord?->bvn_verified ?? false),
                    'member_since' => $vendor->created_at?->format('M Y') ?? '2025',
                ],
                'products' => $products,
            ]
        ]);
    }
}
