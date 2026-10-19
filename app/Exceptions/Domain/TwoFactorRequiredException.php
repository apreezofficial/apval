<?php
namespace App\Exceptions\Domain;

class TwoFactorRequiredException extends DomainException
{
    public function __construct(string $message = 'Two-factor authentication is required.')
    {
        parent::__construct('TWO_FACTOR_REQUIRED', $message, 403);
    }
}
