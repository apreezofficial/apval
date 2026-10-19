<?php
namespace App\Exceptions\Domain;
class IdempotencyReplayException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            'IDEMPOTENCY_REPLAY',
            'Request already submitted.',
            409
        );
    }
}
