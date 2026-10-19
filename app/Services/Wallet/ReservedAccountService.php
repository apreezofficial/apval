<?php
namespace App\Services\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Models\VirtualAccount;
use App\Services\Kyc\MonnifyClient;
use App\Exceptions\Domain\KycRequiredException;
use App\Exceptions\Domain\MonnifyFailure;
use App\Exceptions\Domain\ProviderUnavailableException;
use App\Services\Kyc\MonnifySanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservedAccountService
{
    public function __construct(
        private MonnifyClient $monnify,
        private MonnifySanitizer $sanitizer,
    ){}


    public function getOrCreateForUser(User $user): VirtualAccount
    {
        // 1) KYC enforcement
        $kyc = $user->kycRecord;
        if (!$kyc || !$kyc->bvn_verified || ($kyc->level ?? 0) < 1) {
            throw new KycRequiredException('KYC Level 1 (BVN verified) is required to generate virtual account.');
        }

        // 2) Return existing active VA if any
        $existing = VirtualAccount::where('user_id', $user->id)
            ->where('provider', 'monnify')
            ->where('currency', 'NGN')
            ->where('status', 'active')
            ->first();

        if ($existing) return $existing;

        // 3) Get NGN wallet
        $ngnWallet = Wallet::where('user_id', $user->id)->where('currency', 'NGN')->firstOrFail();

        $contractCode = config('services.monnify.contract_code');
        if (!$contractCode) {
            throw new \RuntimeException('MONNIFY_CONTRACT_CODE is missing.');
        }

        // ✅ BVN must be present for Monnify reserved accounts (or NIN)
        $bvn = $this->decryptIfEncrypted($kyc->bvn);

        if (!$bvn || !preg_match('/^\d{11}$/', $bvn)) {
            throw new KycRequiredException('BVN is required (11 digits) to generate virtual account.');
        }

        // 4) Concurrency-safe create using DB transaction + unique constraint
        return DB::transaction(function () use ($user, $ngnWallet, $contractCode, $bvn) {

            // re-check inside txn (prevents double-create under concurrency)
            $existing = VirtualAccount::where('user_id', $user->id)
                ->where('provider', 'monnify')
                ->where('currency', 'NGN')
                ->lockForUpdate()
                ->first();

            if ($existing) return $existing;

            $accountReference = 'AGP_VA_' . Str::lower(Str::random(24));
            $accountName = 'AgreonPay - ' . strtoupper($user->first_name . ' ' . $user->last_name);

            /**
             * ✅ Monnify Reserved Account payload
             * NOTE: BVN is mandatory unless you are supplying NIN-based variant.
             * We pass BVN from verified KYC record.
             */
            $payload = [
                'accountReference' => $accountReference,
                'accountName' => $accountName,
                'currencyCode' => 'NGN',
                'contractCode' => $contractCode,
                'customerEmail' => $user->email,
                'customerName' => $user->first_name.' '.$user->last_name,
                'bvn' => $bvn,
                'getAllAvailableBanks' => (bool) config('services.monnify.get_all_banks', true),
            ];

            [$status, $json] = $this->monnify->post('/api/v2/bank-transfer/reserved-accounts', $payload);

            // 5) Provider downtime/network (true provider unavailable)
            if ($status === 0 || $status >= 500) {
                throw new ProviderUnavailableException('Monnify unavailable. Try again.');
            }

            // 6) Monnify responded but rejected request (validation/business failure)
            if (!($json['requestSuccessful'] ?? false)) {
                $msg = $json['responseMessage'] ?? 'Reserved account creation failed.';
                $code = 'MONNIFY_FAILED';

                // ✅ Map known messages to meaningful codes
                $msgLower = strtolower($msg);

                if (str_contains($msgLower, 'bvn') || str_contains($msgLower, 'nin')) {
                    throw new KycRequiredException($msg);
                }

                if (str_contains($msgLower, 'contract')) {
                    // wrong contractCode, not provider downtime
                    throw new MonnifyFailure($msg, 'MONNIFY_CONTRACT_INVALID', 422);
                }

                // generic Monnify failure (422)
                throw new MonnifyFailure($msg, $code, 422);
            }

            $body = $json['responseBody'] ?? [];

            return VirtualAccount::create([
                'user_id' => $user->id,
                'wallet_id' => $ngnWallet->id,
                'provider' => 'monnify',
                'currency' => 'NGN',
                'account_reference' => $accountReference,
                'reservation_reference' => $body['reservationReference'] ?? null,
                'account_name' => $body['accountName'] ?? $accountName,
                'accounts' => $body['accounts'] ?? null,
                'status' => 'active',
                'provider_response' => $this->sanitizer->sanitize($json),
            ]);
        });
    }

    private function decryptIfEncrypted(?string $value): ?string
    {
        if (!$value) return null;
        try { return decrypt($value); } catch (\Throwable $e) { return $value; }
    }
}
