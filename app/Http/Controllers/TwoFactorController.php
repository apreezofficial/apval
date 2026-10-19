<?php

namespace App\Http\Controllers;

use App\Services\Security\TwoFactorService;
use Illuminate\Http\Request;

class TwoFactorController
{
    public function __construct(private TwoFactorService $twoFactor) {}

    public function enable(Request $request)
    {
        $request->validate([
            'channel' => ['required', 'in:sms,email'],
        ]);

        $user = $request->user();
        $user->update([
            'two_factor_channel' => $request->channel,
            'two_factor_enabled' => true,
            'two_factor_enabled_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '2FA enabled.',
        ]);
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'purpose' => ['required', 'in:withdrawal,transfer,login,verification'],
        ]);

        $this->twoFactor->sendOtp($request->user(), $request->purpose);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent.',
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'purpose' => ['required', 'in:withdrawal,transfer,login,verification'],
        ]);

        $this->twoFactor->resendOtp($request->user(), $request->purpose);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP resent successfully.',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'purpose' => ['required', 'in:withdrawal,transfer,login,verification'],
            'otp' => ['required','string','max:10'],
        ]);

        $this->twoFactor->assertValidOtp($request->user(), $request->otp, $request->purpose);

        return response()->json([
            'status' => 'success',
            'message' => $request->purpose === 'verification' ? 'Email verified successfully.' : 'OTP verified.',
        ]);
    }
}
