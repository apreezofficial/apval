<?php

namespace App\Exceptions\Domain;

class InvalidOtpException extends DomainException
{
    public function __construct(string $message = 'Invalid or expired OTP.')
    {
        parent::__construct('INVALID_OTP', $message, 422);
    }
}
