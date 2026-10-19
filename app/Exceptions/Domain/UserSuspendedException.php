<?php
namespace App\Exceptions\Domain;
class UserSuspendedException extends DomainException
{
    public function __construct(string $message = 'User is suspended.')
    {
        parent::__construct('USER_SUSPENDED', $message, 403);
    }
}
