<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\KycRecord;
use App\Models\Wallet;
use App\Services\Kyc\BvnVerificationService;
use App\Services\Kyc\BvnVerificationService as KycBvnVerificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;


class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        if (isset($input['email']) && is_string($input['email'])) {
            $input['email'] = strtolower(trim($input['email']));
        }
        if (isset($input['phone']) && is_string($input['phone'])) {
            $input['phone'] = trim($input['phone']);
        }
        if (isset($input['role']) && $input['role'] === 'buyer') {
            $input['role'] = 'customer';
        }

        // Validate ONCE (before calling BVN provider)
        $validator = Validator::make($input, [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'      => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password'   => ['required', 'string', Password::min(8)],

            'role'          => ['required', 'in:customer,farmer,vendor'],
            'market'        => ['nullable', 'string', 'max:120'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],

            'bvn'        => ['nullable', 'string', 'size:11'],
            'dob'        => ['nullable', 'date_format:Y-m-d'],
        ]);

        $validator->after(function ($validator) use ($input) {
            $role = $input['role'] ?? null;
            if (in_array($role, ['farmer', 'vendor'], true) && empty($input['market'])) {
                $validator->errors()->add('market', 'Market is required for farmers and vendors.');
            }
        });

        $validator->validate();

        // Check referrer user
        $referrer = null;
        if (!empty($input['referral_code'])) {
            $referrer = User::where('referral_code', $input['referral_code'])->first();
        }

        // ✅ BVN verification call (only if bvn and dob provided)
        $result = null;
        if (!empty($input['bvn']) && !empty($input['dob'])) {
            $bvnService = app(KycBvnVerificationService::class);
            $result = $bvnService->verify(
                $input['bvn'],
                $input['first_name'],
                $input['last_name'],
                $input['phone'],
                $input['dob'],
            );

            if (!$result['ok']) {
                if (($result['reason'] ?? null) === 'PROVIDER_UNAVAILABLE') {
                    throw new \App\Exceptions\Domain\ProviderUnavailableException('BVN provider unavailable. Try again later.');
                }

                throw new \App\Exceptions\Domain\KycVerificationFailedException('BVN verification failed.');
            }
        }

        // ✅ Atomic create: user + kyc + wallets + referral
        return DB::transaction(function () use ($input, $result, $referrer) {

            $user = User::create([
                'first_name'  => trim($input['first_name']),
                'last_name'   => trim($input['last_name']),
                'email'       => $input['email'],
                'phone'       => $input['phone'],
                'password'    => Hash::make($input['password']),
                'role'        => $input['role'],
                'market'      => $input['market'] ?? null,
                'referred_by' => $referrer ? $referrer->id : null,
                'status'      => 'active',
            ]);

            if ($referrer) {
                \App\Models\Referral::create([
                    'referrer_id' => $referrer->id,
                    'referee_id'  => $user->id,
                    'status'      => 'pending',
                ]);
            }

            KycRecord::create([
                'user_id' => $user->id,
                'level' => $result ? 1 : 0,
                'dob' => $input['dob'] ?? null,
                'bvn' => !empty($input['bvn']) ? encrypt($input['bvn']) : null,
                'bvn_verified' => $result !== null,
                'status' => $result ? 'verified' : 'pending',
                'provider_response' => $result['raw'] ?? null,
                'verified_at' => $result ? now() : null,
            ]);

            Wallet::create(['user_id' => $user->id, 'currency' => 'NGN']);
            Wallet::create(['user_id' => $user->id, 'currency' => 'USDT']);

            return $user;
        });
    }
}
