<?php

namespace App\Exceptions\Domain;

use RuntimeException;

abstract class DomainException extends RuntimeException
{
    public function __construct(
        protected readonly string $errorCode,
        string $message = 'Request failed.',
        protected readonly int $httpStatus = 400,
        protected readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
