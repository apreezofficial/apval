<?php

namespace App\Services\Payouts;

use App\Exceptions\Domain\ProviderUnavailableException;
use App\Models\WithdrawalRequest;
use App\Services\Kyc\MonnifyClient;
use App\Support\TextSanitizer;

class MonnifyPayoutProvider implements PayoutProvider
{
    public function __construct(private MonnifyClient $monnify) {}

    public function payout(WithdrawalRequest $wd): array
    {
        /**
         * NOTE:
         * Monnify disbursement endpoint may differ depending on your enabled product.
         * Put the exact path in config/services.php so you can swap without code changes.
         */
        $path = config('services.monnify.single_transfer_path');
        if (!$path) {
            throw new ProviderUnavailableException('Monnify single transfer path not configured.');
        }

        $payload = [
            // Your internal reference
            'reference' => $wd->reference,

            'amount' => (float) $wd->net_amount,
            'currency' => $wd->currency,

            // REQUIRED: the account money is paid FROM
            'sourceAccountNumber' => config('services.monnify.payout_source_account_number'),

            // Destination account
            'destinationBankCode' => $wd->bank_code,
            'destinationAccountNumber' => $wd->account_number,
            'destinationAccountName' => $wd->account_name,

            'narration' => 'AgreonPay withdrawal',
        ];

        [$status, $json] = $this->monnify->post($path, $payload);

        if ($status === 0 || $status >= 500) {
            throw new ProviderUnavailableException('Monnify unavailable. Try again.');
        }

        if (!($json['requestSuccessful'] ?? false)) {
            $msg = $json['responseMessage'] ?? 'Monnify payout failed.';
            return ['ok' => false, 'provider_reference' => null, 'raw' => $json + ['http' => $status]];
        }

        $body = $json['responseBody'] ?? [];
        $providerRef =
        data_get($body, 'reference')
        ?? data_get($body, 'transactionReference')
        ?? data_get($body, 'paymentReference')
        ?? data_get($body, 'data.reference')
        ?? data_get($body, 'data.transactionReference')
        ?? data_get($body, 'data.paymentReference')
        ?? null;

        return ['ok' => true, 'provider_reference' => $providerRef, 'raw' => $json + ['http' => $status]];

        $narration = TextSanitizer::narration($wd->meta['narration'] ?? null);

            $payload['narration'] = $narration;
    }
}
