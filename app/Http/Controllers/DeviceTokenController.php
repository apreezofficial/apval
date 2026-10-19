<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserDeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController
{
    /**
     * Store or update a user device FCM push token.
     * POST /api/userManager/device-token
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token'       => ['required', 'string', 'max:500'],
            'device_type' => ['sometimes', 'string', 'in:android,ios,web'],
        ]);

        $deviceToken = UserDeviceToken::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'token'   => $validated['token'],
            ],
            [
                'device_type'  => $validated['device_type'] ?? 'android',
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Device push token registered successfully.',
            'data'    => $deviceToken,
        ], 200);
    }

    /**
     * Delete a device token (e.g. on logout or app uninstall).
     * DELETE /api/userManager/device-token
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        UserDeviceToken::where('user_id', $request->user()->id)
            ->where('token', $validated['token'])
            ->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Device push token removed successfully.',
        ]);
    }
}
