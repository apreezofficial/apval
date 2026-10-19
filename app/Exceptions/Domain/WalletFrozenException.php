<?php

namespace App\Exceptions\Domain;

class WalletFrozenException extends DomainException
{
    public function __construct(string $message = 'Wallet is frozen.')
    {
        parent::__construct('WALLET_FROZEN', $message, 403);
    }
}
