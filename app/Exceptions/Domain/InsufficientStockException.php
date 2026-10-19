<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

class InsufficientStockException extends DomainException
{
    public function __construct(string $message = 'Insufficient stock available.')
    {
        parent::__construct('INSUFFICIENT_STOCK', $message, 422);
    }
}
