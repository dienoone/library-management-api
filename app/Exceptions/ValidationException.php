<?php

namespace App\Exceptions;

class ValidationException extends CustomException
{
    public function __construct(string $message = 'Validation failed', ?string $errorId = null, ?array $errors = [])
    {
        parent::__construct($message, 422, $errorId, $errors);
    }
}
