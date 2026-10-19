<?php

namespace App\Exceptions\Domain;

class InvalidRequestException extends DomainException
{
    public function __construct(string $message = 'Invalid request.')
    {
        parent::__construct('INVALID_REQUEST', $message, 422);
    }
}
