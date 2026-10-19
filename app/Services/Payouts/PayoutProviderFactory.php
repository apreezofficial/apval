<?php
namespace App\Services\Payouts;

class PayoutProviderFactory
{
    public function __construct(
        private MonnifyPayoutProvider $monnify,
        private ManualPayoutProvider $manual
    ) {}

    public function for(string $mode): PayoutProvider
    {
        return match ($mode) {
            'manual' => $this->manual,
            default => $this->monnify,
        };
    }
}
