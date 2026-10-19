<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

class OtpCooldownException extends DomainException
{
    public function __construct(string $message = 'Please wait before requesting another OTP.')
    {
        parent::__construct('OTP_COOLDOWN', $message, 429);
    }
}
