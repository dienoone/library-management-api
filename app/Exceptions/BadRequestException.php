<?php

namespace App\Exceptions;

class BadRequestException extends CustomException
{
    public function __construct(string $message = 'Bad request', ?string $errorId = null, ?array $errors = [])
    {
        parent::__construct($message, 400, $errorId,  $errors);
    }
}
