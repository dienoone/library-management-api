<?php

namespace App\Exceptions;

class UnauthorizedException extends CustomException
{
    public function __construct(string $message = 'Unauthorized access', ?string $errorId = null)
    {
        parent::__construct($message, 401, $errorId, []);
    }
}
