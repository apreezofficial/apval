<?php
namespace App\Services\Payouts;

use App\Models\WithdrawalRequest;

class ManualPayoutProvider implements PayoutProvider
{
    public function payout(WithdrawalRequest $wd): array
    {
        // No external API call. Admin will later mark success/failed.
        return ['ok' => true, 'provider_reference' => null, 'raw' => ['mode' => 'manual', 'queued' => true]];
    }
}
