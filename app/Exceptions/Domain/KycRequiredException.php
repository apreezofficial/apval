<?php

namespace App\Exceptions\Domain;

class KycRequiredException extends DomainException
{
    public function __construct(string $message = 'KYC Level 1 is required.')
    {
        parent::__construct('KYC_REQUIRED', $message, 403);
    }
}
