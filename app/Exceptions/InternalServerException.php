<?php

namespace App\Exceptions;

class InternalServerException extends CustomException
{
    public function __construct(string $message = 'Internal server error', ?string $errorId = null, ?array $errors = [],)
    {
        parent::__construct($message, 500, $errorId, $errors);
    }
}
