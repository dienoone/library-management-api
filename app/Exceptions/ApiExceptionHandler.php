<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApiExceptionHandler
{
  // Global Error Handler...
  public function handle(Throwable $e, Request $request): JsonResponse
  {
    $errorId = uniqid('ERR_', true);

    Log::error('API Exception', [
      'errorId' => $errorId,
      'exception' => get_class($e),
      'message' => $e->getMessage(),
      'trace' => $e->getTraceAsString()
    ]);

    if ($e instanceof CustomException) {
      // Ensure it has an error ID
      if (!$e->getErrorId()) {
        $className = get_class($e);
        $newException = new $className(message: $e->getMessage(), statusCode: $e->getStatusCode(), errorId: $errorId, errorMessages: $e->getErrors());
        return $newException->render($request);
      }
      return $e->render($request);
    } else {
      return (new InternalServerException(
        config('app.debug') ? $e->getMessage() : 'An unexpected error occurred',
        $errorId,
        []
      ))->render($request);
    }
  }
}
