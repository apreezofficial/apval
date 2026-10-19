<?php
namespace App\Services\Payouts;

use App\Models\WithdrawalRequest;

interface PayoutProvider
{
    /**
     * Execute the payout.
     * Must return: ['ok' => bool, 'provider_reference' => ?string, 'raw' => array]
     */
    public function payout(WithdrawalRequest $wd): array;
}
