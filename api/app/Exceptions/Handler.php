<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Exception Handler
 * 
 * This class handles all exceptions thrown by the application and formats them
 * into consistent API responses.
 */
class Handler extends ExceptionHandler
{
  /**
   * The list of the inputs that are never flashed to the session on validation exceptions.
   *
   * @var array<int, string>
   */
  protected $dontFlash = [
    'current_password',
    'password',
    'password_confirmation',
  ];

  /**
   * Register the exception handling callbacks for the application.
   */
  public function register(): void
  {
    $this->reportable(function (Throwable $e) {
      //
    });

    $this->renderable(function (Throwable $e, Request $request) {
      if ($request->expectsJson() || $request->is('api/*')) {
        return $this->handleApiException($e, $request);
      }
    });
  }

  /**
   * Handle API exceptions and return consistent JSON responses.
   *
   * @param  \Throwable  $exception
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  private function handleApiException(Throwable $exception, Request $request): JsonResponse
  {
    if ($exception instanceof ValidationException) {
      return $this->convertValidationExceptionToResponse($exception, $request);
    }

    if ($exception instanceof ModelNotFoundException) {
      return $this->errorResponse(
        'Resource not found',
        404
      );
    }

    if ($exception instanceof AuthenticationException) {
      return $this->errorResponse(
        'Unauthenticated',
        401
      );
    }

    if ($exception instanceof AuthorizationException) {
      return $this->errorResponse(
        'Unauthorized',
        403
      );
    }

    if ($exception instanceof NotFoundHttpException) {
      return $this->errorResponse(
        'The specified URL cannot be found',
        404
      );
    }

    if ($exception instanceof HttpException) {
      return $this->errorResponse(
        $exception->getMessage() ?: 'HTTP Exception',
        $exception->getStatusCode()
      );
    }

    // Handle all other exceptions
    if (config('app.debug')) {
      return $this->errorResponse(
        $exception->getMessage(),
        500,
        [
          'exception' => get_class($exception),
          'file' => $exception->getFile(),
          'line' => $exception->getLine(),
          'trace' => $exception->getTrace(),
        ]
      );
    }

    return $this->errorResponse(
      'Unexpected error. Try again later.',
      500
    );
  }

  /**
   * Convert a validation exception into a JSON response.
   *
   * @param  \Illuminate\Validation\ValidationException  $e
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  protected function convertValidationExceptionToResponse(ValidationException $e, $request): JsonResponse
  {
    return $this->errorResponse(
      'The given data was invalid',
      422,
      $e->errors()
    );
  }

  /**
   * Return a standard error JSON response.
   *
   * @param  string  $message
   * @param  int  $statusCode
   * @param  mixed  $errors
   * @return \Illuminate\Http\JsonResponse
   */
  protected function errorResponse(string $message, int $statusCode, $errors = null): JsonResponse
  {
    $response = [
      'success' => false,
      'message' => $message,
    ];

    if ($errors !== null) {
      $response['errors'] = $errors;
    }

    return response()->json($response, $statusCode);
  }
}
