<?php

namespace App\Exceptions;

class ConflictException extends CustomException
{
    public function __construct(string $message = 'Resource conflict', ?string $errorId = null)
    {
        parent::__construct($message, 409, $errorId, []);
    }
}
