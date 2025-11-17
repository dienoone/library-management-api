<?php

namespace App\Exceptions;

class NotFoundException extends CustomException
{
    public function __construct(string $message = 'Resource not found', ?string $errorId = null)
    {
        parent::__construct($message, 404, $errorId, []);
    }
}
