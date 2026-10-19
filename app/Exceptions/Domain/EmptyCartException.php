<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

class EmptyCartException extends DomainException
{
    public function __construct(string $message = 'Cannot place an order with an empty cart.')
    {
        parent::__construct('EMPTY_CART', $message, 422);
    }
}
