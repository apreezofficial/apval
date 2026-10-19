<?php

namespace App\Exceptions\Domain;
class InvalidCredentialsException extends DomainException
{
    public function __construct(string $message = 'Invalid credentials.')
    {
        parent::__construct('INVALID_CREDENTIALS', $message, 422);
    }
}
