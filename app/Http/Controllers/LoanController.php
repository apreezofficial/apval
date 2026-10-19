<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanController
{
    /**
     * GET /api/loans
     * Returns user's loan dashboard: max limit, active loan, and history.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $kyc = $user->kycRecord;
        $tier = (int)($kyc->level ?? 0);

        // Pre-qualified limit based on verified KYC tier
        $maxLimit = match ($tier) {
            1 => 100000.0,
            2 => 500000.0,
            3 => 2000000.0,
            default => 50000.0,
        };

        $loans = Loan::where('user_id', $user->id)
            ->latest()
            ->get();

        $activeLoan = $loans->first(function ($l) {
            return in_array($l->status, ['under_review', 'approved', 'disbursed', 'pending'], true);
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'max_limit'      => $maxLimit,
                'kyc_tier'       => $tier,
                'is_eligible'    => (bool)($kyc?->bvn_verified),
                'active_loan'    => $activeLoan,
                'recent_history' => $loans,
            ],
        ]);
    }

    /**
     * POST /api/loans/apply
     * Submit a new loan limit / loan application.
     */
    public function apply(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user already has an active loan in review or outstanding
        $existing = Loan::where('user_id', $user->id)
            ->whereIn('status', ['under_review', 'approved', 'disbursed'])
            ->first();

        if ($existing) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You already have an active loan application (' . ucfirst(str_replace('_', ' ', $existing->status)) . ').',
                'data'    => $existing,
            ], 422);
        }

        $validated = $request->validate([
            'amount'              => ['nullable', 'numeric', 'min:1000', 'max:5000000'],
            'tenure_months'       => ['nullable', 'integer', 'min:1', 'max:24'],
            'reason_for_loan'     => ['nullable', 'string', 'max:255'],
            'personal_info'       => ['nullable', 'array'],
            'employment_info'     => ['nullable', 'array'],
            'emergency_contacts'  => ['nullable', 'array'],
            'id_card_url'         => ['nullable', 'string', 'max:1000'],
            'selfie_url'          => ['nullable', 'string', 'max:1000'],
        ]);

        $amount = (float)($validated['amount'] ?? 50000.0);
        $tenure = (int)($validated['tenure_months'] ?? 3);
        $interestRate = 5.0; // 5% flat fee
        $totalRepayment = $amount + ($amount * ($interestRate / 100));

        $loan = Loan::create([
            'user_id'            => $user->id,
            'loan_reference'     => 'LOAN-' . strtoupper(Str::random(8)),
            'amount'             => $amount,
            'tenure_months'      => $tenure,
            'interest_rate'      => $interestRate,
            'total_repayment'    => $totalRepayment,
            'amount_repaid'      => 0.0,
            'due_date'           => now()->addMonths($tenure),
            'status'             => 'under_review',
            'reason_for_loan'    => $validated['reason_for_loan'] ?? ($validated['personal_info']['reasonForLoan'] ?? 'Business capital loans'),
            'personal_info'      => $validated['personal_info'] ?? null,
            'employment_info'    => $validated['employment_info'] ?? null,
            'emergency_contacts' => $validated['emergency_contacts'] ?? null,
            'id_card_url'        => $validated['id_card_url'] ?? null,
            'selfie_url'         => $validated['selfie_url'] ?? null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Loan application submitted successfully and is currently under review.',
            'data'    => $loan,
        ], 201);
    }

    /**
     * GET /api/loans/{id}
     * Get loan details and remaining balance.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $loan = Loan::where('user_id', $user->id)->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => array_merge($loan->toArray(), [
                'remaining_balance' => $loan->remaining_balance,
            ]),
        ]);
    }

    /**
     * POST /api/loans/{id}/repay
     * Repay loan from user's primary wallet.
     */
    public function repay(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $loan = Loan::where('user_id', $user->id)->findOrFail($id);

        if ($loan->status === 'repaid') {
            return response()->json([
                'status'  => 'error',
                'message' => 'This loan is already fully repaid.',
            ], 422);
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
        ]);

        $repayAmount = (float)$request->amount;
        $remaining = $loan->remaining_balance;

        if ($repayAmount > $remaining) {
            $repayAmount = $remaining;
        }

        return DB::transaction(function () use ($user, $loan, $repayAmount) {
            $wallet = Wallet::where('user_id', $user->id)->first();

            if (!$wallet || (float)$wallet->balance < $repayAmount) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Insufficient wallet balance to make this repayment.',
                ], 422);
            }

            // Deduct from wallet
            $wallet->decrement('balance', $repayAmount);

            // Record transaction
            Transaction::create([
                'user_id'          => $user->id,
                'wallet_id'        => $wallet->id,
                'type'             => 'loan_repayment',
                'amount'           => $repayAmount,
                'status'           => 'completed',
                'description'      => "Loan repayment for {$loan->loan_reference}",
                'reference'        => 'LRP-' . strtoupper(Str::random(10)),
            ]);

            $newRepaid = (float)$loan->amount_repaid + $repayAmount;
            $isFullyRepaid = $newRepaid >= (float)$loan->total_repayment;

            $loan->update([
                'amount_repaid' => $newRepaid,
                'status'        => $isFullyRepaid ? 'repaid' : $loan->status,
                'repaid_at'     => $isFullyRepaid ? now() : null,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => $isFullyRepaid ? 'Congratulations! Your loan is fully repaid.' : "Repayment of ₦" . number_format($repayAmount, 2) . " successful.",
                'data'    => $loan->fresh(),
            ]);
        });
    }
}
