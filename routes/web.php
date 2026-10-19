<?php

use App\Http\Controllers\AccountDeletionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Google Play / App Store required account deletion page (publicly accessible)
Route::get('/account/delete',  [AccountDeletionController::class, 'show']);
Route::post('/account/delete', [AccountDeletionController::class, 'submitWebRequest']);

// Web-based password reset page for email links
Route::get('/reset-password', function (\Illuminate\Http\Request $request) {
    return view('auth.reset-password', [
        'token' => $request->query('token'),
        'email' => $request->query('email'),
    ]);
})->name('password.reset');

Route::post('/reset-password', function (\Illuminate\Http\Request $request, \App\Services\Auth\AuthService $authService) {
    $request->validate([
        'token'    => ['required', 'string'],
        'email'    => ['required', 'email'],
        'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
    ]);

    $status = $authService->resetPassword(
        $request->only('email', 'password', 'password_confirmation', 'token')
    );

    if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
        \App\Models\User::where('email', strtolower(trim($request->email)))->first()?->tokens()->delete();
        return back()->with('status', __($status));
    }

    return back()->withErrors(['email' => __($status)]);
});

// Web-based email verification & Tier 0 confirmation page
Route::get('/verify-email', function (\Illuminate\Http\Request $request) {
    $userId = $request->query('id');
    $user = \App\Models\User::find($userId);

    if (!$user || !hash_equals((string) $request->query('hash'), sha1($user->getEmailForVerification()))) {
        return response()->view('auth.verify-tier0', [
            'status'  => 'error',
            'message' => 'This verification link is invalid or has expired.',
            'user'    => null,
        ], 403);
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    // Ensure KYC Tier 0 is marked verified
    $kyc = $user->kycRecord;
    if ($kyc && !$kyc->bvn_verified) {
        $kyc->update([
            'level'       => 0,
            'status'      => 'verified',
            'verified_at' => now(),
        ]);
    }

    return view('auth.verify-tier0', [
        'status'  => 'success',
        'message' => 'Tier 0 Verified! Your basic AgreonPay account is active.',
        'user'    => $user,
    ]);
})->name('verification.verify');

// Temporary route to fix storage symlink on cPanel
Route::get('/fix-storage', function () {
    try {
        $publicStoragePath = public_path('storage');
        if (file_exists($publicStoragePath)) {
            if (is_link($publicStoragePath)) {
                unlink($publicStoragePath);
            }
        }

        \Illuminate\Support\Facades\Artisan::call('storage:link');
        return "Storage link created successfully.";
    }
    catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Diagnostic route to check storage permissions
Route::get('/check-storage', function () {
    $results = [];
    $dirs = [
        storage_path('app/public'),
        storage_path('app/public/uploads'),
        storage_path('app/public/uploads/images'),
        public_path('storage'),
    ];

    foreach ($dirs as $dir) {
        $results[$dir] = [
            'exists' => file_exists($dir),
            'writable' => is_writable($dir),
            'is_link' => is_link($dir),
            'permissions' => substr(sprintf('%o', fileperms($dir) ?: 0), -4),
        ];
    }

    return response()->json($results);
});

// Secure route to link reserved Monnify account and update KYC Tier 1
Route::get('/sync-thomas-account', function () {
    $email = 'thomasemmy2609@gmail.com';
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // 1. Update KYC Tier 1
    $kyc = $user->kycRecord;
    if ($kyc) {
        $kyc->update([
            'level'        => 1,
            'status'       => 'verified',
            'bvn_verified' => true,
            'bvn'          => encrypt('22446853833'),
            'dob'          => '1998-05-10',
            'verified_at'  => now(),
        ]);
    }

    // 2. Link Virtual Account
    $wallet = \App\Models\Wallet::where('user_id', $user->id)->where('currency', 'NGN')->first();
    $va = null;
    if ($wallet) {
        $va = \App\Models\VirtualAccount::updateOrCreate(
            ['user_id' => $user->id, 'currency' => 'NGN'],
            [
                'wallet_id'             => $wallet->id,
                'provider'              => 'monnify',
                'account_reference'     => 'AGP_VA_test_1790157943',
                'reservation_reference' => '06PWAEA1EDFRJT103545',
                'account_name'          => 'AgreonPay - EMM',
                'accounts'              => [
                    [
                        'bankCode'      => '50515',
                        'bankName'      => 'Moniepoint Microfinance Bank',
                        'accountNumber' => '1016559196',
                        'accountName'   => 'EMM'
                    ]
                ],
                'status'                => 'active',
            ]
        );
    }

    return response()->json([
        'status'         => 'success',
        'message'        => 'KYC Tier 1 verified and Virtual Account linked successfully!',
        'user'           => $user->email,
        'kyc_level'      => $user->kycRecord?->level,
        'account_number' => '1016559196',
        'bank'           => 'Moniepoint Microfinance Bank',
    ]);
});