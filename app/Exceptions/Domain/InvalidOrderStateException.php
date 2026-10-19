<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

class InvalidOrderStateException extends DomainException
{
    public function __construct(string $message = 'Order item is not in the required state for this action.')
    {
        parent::__construct('INVALID_ORDER_STATE', $message, 422);
    }
}
