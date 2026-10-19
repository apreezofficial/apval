<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Security\ChangePinRequest;
use App\Http\Requests\Security\SetPinRequest;
use App\Services\Security\PinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PinController
{
   public function __construct(private PinService $pins) {}

    /**
     * POST /api/userManager/pin/request-otp
     * Send a 6-digit verification code to the user's email for PIN authorization
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $user = $request->user();

        if (empty($user->email)) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'EMAIL_REQUIRED',
                'message' => 'A registered email address is required to receive verification codes.',
            ], 422);
        }

        $otp = (string) random_int(100000, 999999);
        \Illuminate\Support\Facades\Cache::put(
            "pin_auth_otp:{$user->id}",
            password_hash($otp, PASSWORD_BCRYPT),
            now()->addMinutes(10)
        );

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\OtpMail($otp, 'Transaction PIN Setup / Change', 10)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send PIN OTP email: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'code'    => 'EMAIL_FAILED',
                'message' => 'Unable to send verification email. Please try again.',
            ], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'A 6-digit verification code has been sent to your email.',
        ]);
    }

    public function set(SetPinRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        // 1) Verify OTP if provided
        $hasOtp = !empty($data['otp']);
        $hasPassword = !empty($data['password']);

        if (!$hasOtp && !$hasPassword) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'AUTH_REQUIRED',
                'message' => 'Please provide either your password or the email verification code.',
            ], 422);
        }

        if ($hasOtp) {
            $cachedHash = \Illuminate\Support\Facades\Cache::get("pin_auth_otp:{$user->id}");
            if (!$cachedHash || !password_verify($data['otp'], $cachedHash)) {
                return response()->json([
                    'status'  => 'error',
                    'code'    => 'INVALID_OTP',
                    'message' => 'Invalid or expired verification code.',
                ], 422);
            }
            \Illuminate\Support\Facades\Cache::forget("pin_auth_otp:{$user->id}");
        } elseif ($hasPassword) {
            if (!Hash::check($data['password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Password is incorrect.',
                ], 422);
            }
        }

        $this->pins->setPin($user, $data['pin']);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction PIN set successfully.',
        ]);
    }

    public function change(ChangePinRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        // Verify OTP if supplied
        if (!empty($data['otp'])) {
            $cachedHash = \Illuminate\Support\Facades\Cache::get("pin_auth_otp:{$user->id}");
            if (!$cachedHash || !password_verify($data['otp'], $cachedHash)) {
                return response()->json([
                    'status'  => 'error',
                    'code'    => 'INVALID_OTP',
                    'message' => 'Invalid or expired verification code.',
                ], 422);
            }
            \Illuminate\Support\Facades\Cache::forget("pin_auth_otp:{$user->id}");
        }

        $this->pins->assertValidPin($user, $data['current_pin']);
        $this->pins->setPin($user, $data['new_pin']);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction PIN changed successfully.',
        ]);
    }

    public function enableBiometric(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['biometric_enabled' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Biometric authentication enabled.',
        ]);
    }

    public function disableBiometric(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['biometric_enabled' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Biometric authentication disabled.',
        ]);
    }
}
