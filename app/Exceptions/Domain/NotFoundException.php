<?php

namespace App\Exceptions\Domain;

class NotFoundException extends DomainException
{
    public function __construct(string $message = 'Resource not found.')
    {
        parent::__construct('NOT_FOUND', $message, 404);
    }
}
