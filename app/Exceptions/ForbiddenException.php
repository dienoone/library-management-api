<?php

namespace App\Exceptions;

class ForbiddenException extends CustomException
{
    public function __construct(string $message = 'Access forbidden', ?string $errorId = null)
    {
        parent::__construct($message,  403, $errorId, []);
    }
}
