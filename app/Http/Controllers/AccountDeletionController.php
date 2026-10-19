<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountDeletionController
{
    public function show(): View
    {
        return view('account-deletion');
    }

    /**
     * Handle deletion request submitted from the public web form.
     * Looks up the user by email, verifies password, then deletes.
     */
    public function submitWebRequest(Request $request): View
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'reason'   => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return view('account-deletion', [
                'error' => 'The email or password you entered is incorrect.',
            ]);
        }

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->delete();
        });

        return view('account-deletion', [
            'success' => 'Your account has been permanently deleted. All your data has been removed from our system.',
        ]);
    }

    /**
     * Authenticated API endpoint — mobile app calls this to delete the
     * currently logged-in user's account immediately.
     */
    public function deleteAuthenticatedAccount(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->delete();
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Your account has been permanently deleted.',
        ]);
    }
}
