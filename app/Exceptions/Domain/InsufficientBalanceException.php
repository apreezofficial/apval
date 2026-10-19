<?php

namespace App\Exceptions\Domain;

class InsufficientBalanceException extends DomainException
{
    public function __construct(string $message = 'Insufficient balance.')
    {
        parent::__construct('INSUFFICIENT_BALANCE', $message, 422);
    }
}
