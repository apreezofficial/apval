<?php
namespace App\Services\Banks;

use App\Models\Bank;
use Illuminate\Support\Collection;

class BankDirectory
{
    public function isValidMonnifyBankCode(string $code): bool
    {
        return Bank::where('provider', 'monnify')
            ->where('code', $code)
            ->where('active', true)
            ->exists();
    }

    public function listMonnifyBanks(): Collection
    {
        return Bank::where('provider', 'monnify')
            ->where('active', true)
            ->orderBy('name')
            ->get(['code', 'name']);
    }

    public function topSuggestionCodes(): array
    {
        return [
            '999991', // PalmPay
            '999992', // OPay
            '090267', // Kuda
            '50515',  // Moniepoint MFB
            '058',    // GTBank
            '044',    // Access
            '057',    // Zenith
            '033',    // UBA
            '050',    // EcoBank
            '070',    // Fidelity Bank
            '011',    // First Bank of Nigeria
            '214',    // First City Monument Bank (FCMB)
            '301',    // Jaiz Bank
            '082',    // Keystone Bank
            '076',    // Polaris Bank
            '221',    // Stanbic IBTC Bank
            '032',    // Union Bank
            '215',    // Unity Bank
            '035',    // Wema Bank
        ];
    }
}
