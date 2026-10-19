<?php
namespace App\Exceptions\Domain;
class LiquidityInsufficientException extends DomainException
{
    public function __construct(string $message = 'Liquidity temporarily insufficient. Withdrawal queued.')
    {
        parent::__construct('LIQUIDITY_INSUFFICIENT', $message, 202);
    }
}
