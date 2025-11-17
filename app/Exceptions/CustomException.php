<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomException extends Exception
{

    public function __construct(
        string $message = '',
        protected int $statusCode = 500,
        protected ?string $errorId = null,
        protected ?array $errors = []
    ) {
        parent::__construct($message);
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorId(): ?string
    {
        return $this->errorId;
    }

    public function render(Request $request): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $this->getMessage(),
            'error_id' => $this->getErrorId(),
            'timestamp' => now()->toIso8601String(),
        ];

        if (!empty($this->getErrors())) {
            $response['errors'] = $this->getErrors();
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => class_basename($this),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
            ];
        }

        return response()->json($response, $this->statusCode);
    }
}
