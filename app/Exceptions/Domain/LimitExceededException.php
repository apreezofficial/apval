<?php

namespace App\Exceptions\Domain;

class LimitExceededException extends DomainException
{
    public function __construct(string $message = 'Transaction limit exceeded.')
    {
        parent::__construct('LIMIT_EXCEEDED', $message, 422);
    }
}
