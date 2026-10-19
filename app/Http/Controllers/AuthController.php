<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Services\Security\ResendEmailService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class AuthController
{
    public function __construct(private AuthService $authService) {}

    // POST /api/userManager/register
    public function register(Request $request, ResendEmailService $resendEmailService): JsonResponse
    {
        $input = $request->only([
            'first_name', 'last_name', 'email', 'phone',
            'password', 'password_confirmation', 'role', 'market', 'bvn', 'dob', 'referral_code',
        ]);

        if (isset($input['email']) && is_string($input['email'])) {
            $input['email'] = strtolower(trim($input['email']));
        }
        if (isset($input['phone']) && is_string($input['phone'])) {
            $input['phone'] = trim($input['phone']);
        }
        if (isset($input['role']) && $input['role'] === 'buyer') {
            $input['role'] = 'customer';
        }

        $validator = Validator::make($input, [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'      => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password'   => ['required', 'string', PasswordRule::min(8)],
            'role'       => ['required', 'in:customer,farmer,vendor'],
            'market'     => ['nullable', 'string', 'max:120'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
            'bvn'        => ['nullable', 'string', 'size:11'],
            'dob'        => ['nullable', 'date_format:Y-m-d'],
        ]);

        $validator->after(function ($validator) use ($input) {
            $role = $input['role'] ?? null;
            if (in_array($role, ['farmer', 'vendor'], true) && empty($input['market'])) {
                $validator->errors()->add('market', 'Market is required for farmers and vendors.');
            }
        });

        $validator->validate();

        $email = $input['email'];
        $otp = (string) random_int(100000, 999999);

        // Store pending registration data in cache for 15 minutes — DO NOT save to database yet
        Cache::put("pending_reg:{$email}", [
            'input' => $input,
            'otp_hash' => password_hash($otp, PASSWORD_BCRYPT),
            'created_at' => now()->timestamp,
        ], now()->addMinutes(15));

        Cache::put("pending_reg_cooldown:{$email}", true, now()->addSeconds(60));

        // Dispatch verification OTP to user's email
        try {
            $resendEmailService->sendOtpEmail($email, $otp, 'verification');
        } catch (\Throwable $e) {
            logger()->error('Failed to dispatch registration OTP email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Registration initiated. A verification OTP has been sent to your email.',
            'data' => [
                'email' => $email,
                'requires_otp' => true,
            ],
        ], 200);
    }

    // POST /api/userManager/register/verify-otp
    public function verifyRegistrationOtp(
        Request $request,
        CreatesNewUsers $creator,
        ResendEmailService $resendEmailService
    ): JsonResponse {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'otp'   => ['required', 'string', 'max:10'],
        ]);

        $email = strtolower(trim($request->email));
        $cached = Cache::get("pending_reg:{$email}");

        if (!$cached || empty($cached['input']) || empty($cached['otp_hash'])) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'REGISTRATION_EXPIRED',
                'message' => 'Registration session expired or not found. Please register again.',
            ], 422);
        }

        if (!password_verify($request->otp, $cached['otp_hash'])) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'INVALID_OTP',
                'message' => 'Invalid or expired OTP code.',
            ], 422);
        }

        // OTP is valid => create user in database atomically
        $user = $creator->create($cached['input']);
        $user->forceFill(['email_verified_at' => now()])->save();
        $token = $user->createToken('mobile')->plainTextToken;

        // Ensure KYC Record is active at Tier 0 (Email Verified, pending Tier 1 BVN)
        $kyc = $user->kycRecord;
        if ($kyc && !$kyc->bvn_verified) {
            $kyc->update([
                'level' => 0,
                'status' => 'pending',
            ]);
        }

        // Clear pending registration cache
        Cache::forget("pending_reg:{$email}");
        Cache::forget("pending_reg_cooldown:{$email}");

        // Dispatch Tier 0 Welcome & Verification link email
        try {
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addDays(14),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );

            $resendEmailService->sendTier0VerificationEmail($user, $verificationUrl);

            // Dispatch Welcome Onboarding Email
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\WelcomeMail($user));
        } catch (\Throwable $e) {
            logger()->error('Failed to dispatch Tier 0 verification / welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Account created and email verified successfully.',
            'data'    => [
                'token' => $token,
                'user'  => $user->load(['kycRecord', 'wallets']),
            ],
        ], 201);
    }

    // POST /api/userManager/register/resend-otp
    public function resendRegistrationOtp(Request $request, ResendEmailService $resendEmailService): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = strtolower(trim($request->email));
        $cached = Cache::get("pending_reg:{$email}");

        if (!$cached) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'REGISTRATION_EXPIRED',
                'message' => 'Registration session expired or not found. Please register again.',
            ], 422);
        }

        if (Cache::has("pending_reg_cooldown:{$email}")) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'OTP_COOLDOWN',
                'message' => 'Please wait 60 seconds before requesting another code.',
            ], 429);
        }

        $otp = (string) random_int(100000, 999999);
        $cached['otp_hash'] = password_hash($otp, PASSWORD_BCRYPT);
        Cache::put("pending_reg:{$email}", $cached, now()->addMinutes(15));
        Cache::put("pending_reg_cooldown:{$email}", true, now()->addSeconds(60));

        try {
            $resendEmailService->sendOtpEmail($email, $otp, 'verification');
        } catch (\Throwable $e) {
            logger()->error('Failed to resend registration OTP email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'A fresh verification OTP has been sent to your email.',
        ]);
    }

    // POST /api/userManager/login
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->authService->login(
            $data['login'],
            $data['password'],
            $data['device_name'] ?? 'mobile'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => $result,
        ]);
    }

    // POST /api/userManager/logout
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    // GET /api/userManager/me
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->load(['kycRecord', 'wallets']),
        ]);
    }

    // POST /api/userManager/passwordChange
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        $this->authService->changePassword(
            $request->user(),
            $data['current_password'],
            $data['new_password']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed. Please login again.',
        ]);
    }

    // POST /api/userManager/forgotPassword
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = $this->authService->sendResetLink($request->only('email'));

        return response()->json([
            'status' => $status === Password::RESET_LINK_SENT ? 'success' : 'error',
            'message' => __($status),
        ], $status === Password::RESET_LINK_SENT ? 200 : 422);
    }

    // POST /api/userManager/resetPassword
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $status = $this->authService->resetPassword(
            $request->only('email', 'password', 'password_confirmation', 'token')
        );

        if ($status === Password::PASSWORD_RESET) {
            // Revoke all existing tokens so stolen sessions can't persist after a reset
            User::where('email', $request->email)->first()?->tokens()->delete();
        }

        return response()->json([
            'status'  => $status === Password::PASSWORD_RESET ? 'success' : 'error',
            'message' => __($status),
        ], $status === Password::PASSWORD_RESET ? 200 : 422);
    }
}
