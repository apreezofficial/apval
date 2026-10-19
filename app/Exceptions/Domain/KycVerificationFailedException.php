<?php

namespace App\Exceptions\Domain;

class KycVerificationFailedException extends DomainException
{
    public function __construct(string $message = 'KYC verification failed.')
    {
        parent::__construct('KYC_VERIFICATION_FAILED', $message, 422);
    }
}
