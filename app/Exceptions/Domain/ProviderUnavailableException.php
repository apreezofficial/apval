<?php

namespace App\Exceptions\Domain;

class ProviderUnavailableException extends DomainException
{
    public function __construct(string $message = 'Provider unavailable.')
    {
        parent::__construct('PROVIDER_UNAVAILABLE', $message, 503);
    }
}
