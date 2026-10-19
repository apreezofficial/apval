<?php

namespace App\Exceptions\Domain;

class MonnifyFailure extends DomainException
{
    /**
     * Monnify returned a requestSuccessful=false response (not a crash).
     * This is typically a 4xx-style validation/business failure.
     */
    public function __construct(
        string $message = 'Monnify request failed.',
        string $errorCode = 'MONNIFY_FAILED',
        int $httpStatus = 422,
        array $errors = []
    ) {
        parent::__construct($errorCode, $message, $httpStatus, $errors);
    }
}
