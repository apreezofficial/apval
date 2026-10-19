<?php

namespace App\Services\Banks;

use App\Exceptions\Domain\InvalidRequestException;
use App\Exceptions\Domain\ProviderUnavailableException;
use App\Models\Bank;
use App\Services\Kyc\MonnifyClient;

class AccountResolutionService
{
    public function __construct(
        private MonnifyClient $monnify,
        private BankDirectory $banks,
    ) {}

    public function resolve(string $bankCode, string $accountNumber): array
    {
        $body = $this->monnify->validateAccount($bankCode, $accountNumber);

        $bank = Bank::where('code', $bankCode)->first();
        $accountName = $body['accountName'] ?? $body['account_name'] ?? null;

        if (empty($accountName)) {
            throw new \RuntimeException('Account name could not be resolved for this account number.');
        }

        return [
            'bank_code' => $bankCode,
            'bank_name' => $bank?->name ?? ($body['bankName'] ?? null),
            'account_number' => $accountNumber,
            'account_name' => $accountName,
        ];
    }

    public function suggest(string $accountNumber): array
    {
        $suggestions = [];

        foreach ($this->banks->topSuggestionCodes() as $bankCode) {
            try {
                $suggestions[] = $this->resolve($bankCode, $accountNumber);
            } catch (\Throwable $e) {
                // ignore failed guesses
            }
        }

        return $suggestions;
    }
}
