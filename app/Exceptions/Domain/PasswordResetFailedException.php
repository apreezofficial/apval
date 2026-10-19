<?php

namespace App\Exceptions\Domain;

class PasswordResetFailedException extends DomainException
{
    public function __construct(string $message = 'Reset failed.')
    {
        parent::__construct('RESET_FAILED', $message, 422);
    }
}
