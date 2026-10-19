<?php

namespace App\Services\Kyc;

use Carbon\Carbon;

class BvnVerificationService
{
    public function __construct(private readonly MonnifyClient $monnify) {}

    public function verify(string $bvn, string $firstName, string $lastName, string $phone, string $dobYmd): array
    {
        // Monnify expects dd-MMM-yyyy (e.g., 03-Oct-1993)
        $dob = Carbon::parse($dobYmd)->format('d-M-Y');
        $name = trim($firstName.' '.$lastName);

        // Normalize Nigerian phone number to 080... format for BVN matching
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '234') && strlen($cleanPhone) === 13) {
            $cleanPhone = '0' . substr($cleanPhone, 3);
        }

        [$status, $json] = $this->monnify->post('/api/v1/vas/bvn-details-match', [
            'bvn' => trim($bvn),
            'name' => $name,
            'dateOfBirth' => $dob,
            'mobileNo' => $cleanPhone,
        ]);

        if ($status >= 500) {
            return ['ok' => false, 'reason' => 'PROVIDER_UNAVAILABLE', 'message' => 'BVN verification provider is temporarily unavailable. Please try again later.', 'raw' => $json];
        }

        if (!($json['requestSuccessful'] ?? false)) {
            $providerMsg = $json['responseMessage'] ?? 'BVN verification failed. Please ensure your BVN, name, and date of birth match your bank records.';
            return ['ok' => false, 'reason' => 'KYC_VERIFICATION_FAILED', 'message' => $providerMsg, 'raw' => $json];
        }

        $body = $json['responseBody'] ?? [];

        // Interpret match results: require FULL_MATCH on dob; name or mobile can match
        $dobMatch = ($body['dateOfBirth'] ?? null) === 'FULL_MATCH';
        $mobileMatch = ($body['mobileNo'] ?? null) === 'FULL_MATCH';

        $nameStatus = $body['name']['matchStatus'] ?? null; // FULL_MATCH / PARTIAL_MATCH / NO_MATCH
        $namePercentage = (int)($body['name']['matchPercentage'] ?? 0);
        $nameOk = in_array($nameStatus, ['FULL_MATCH', 'PARTIAL_MATCH'], true) || $namePercentage >= 50;

        $ok = $dobMatch && ($nameOk || $mobileMatch);

        $specificReason = null;
        if (!$ok) {
            if (!$dobMatch) {
                $specificReason = 'Date of birth does not match your BVN records. Please check the birth date registered with your bank.';
            } elseif (!$nameOk && !$mobileMatch) {
                $specificReason = 'Your profile name (' . $name . ') does not match the name on this BVN. Please ensure your profile name matches your official bank records.';
            } else {
                $specificReason = 'BVN verification failed. Please verify that your BVN, date of birth, and name match your bank records exactly.';
            }
        }

        return [
            'ok' => $ok, 
            'reason' => $ok ? null : 'KYC_VERIFICATION_FAILED', 
            'message' => $specificReason,
            'raw' => $json
        ];
    }
}
