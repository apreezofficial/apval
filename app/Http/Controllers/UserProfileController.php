<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserProfileController
{
    /**
     * Get the authenticated user's full profile details.
     * GET /api/userManager/profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['kycRecord', 'wallets', 'vendorProfile']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id'           => $user->id,
                'first_name'   => $user->first_name,
                'last_name'    => $user->last_name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'avatar_url'   => $user->avatar_url,
                'address'      => $user->address,
                'role'         => $user->role,
                'status'       => $user->status,
                'market'       => $user->market,
                'referral_code' => $user->referral_code,
                'kyc'          => $user->kycRecord,
                'wallets'      => $user->wallets,
                'vendor'       => $user->vendorProfile,
                'created_at'   => $user->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update the authenticated user's profile information.
     * PUT|POST /api/userManager/profile
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->filled('name') && !$request->filled('first_name')) {
            $parts = explode(' ', trim((string)$request->input('name')), 2);
            $request->merge([
                'first_name' => $parts[0] ?? '',
                'last_name' => $parts[1] ?? '',
            ]);
        }

        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name'  => ['sometimes', 'nullable', 'string', 'max:100'],
            'phone'      => ['sometimes', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'avatar_url' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'avatar'     => ['sometimes', 'nullable', 'file', 'max:10240'],
            'photo'      => ['sometimes', 'nullable', 'file', 'max:10240'],
            'image'      => ['sometimes', 'nullable', 'file', 'max:10240'],
            'address'    => ['sometimes', 'nullable', 'string', 'max:1000'],
            'market'     => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        // Handle direct file upload if passed in request
        $file = $request->file('avatar') ?? $request->file('photo') ?? $request->file('image');
        if ($file && $file->isValid()) {
            $extension = $file->guessExtension() ?? 'jpg';
            $filename = Str::uuid() . '.' . $extension;
            $path = $file->storeAs('uploads/images', $filename, 'public');
            $validated['avatar_url'] = Storage::disk('public')->url($path);
        }

        unset($validated['avatar'], $validated['photo'], $validated['image']);

        $user->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully.',
            'data'    => [
                'id'         => $user->id,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'avatar_url' => $user->avatar_url,
                'address'    => $user->address,
                'market'     => $user->market,
            ],
        ]);
    }
}
