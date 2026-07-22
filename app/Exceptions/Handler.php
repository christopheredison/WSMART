<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response and log 5xx details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e): SymfonyResponse
    {
        $response = parent::render($request, $e);
        $statusCode = method_exists($response, 'getStatusCode') ? (int) $response->getStatusCode() : 500;

        if ($statusCode >= 500) {
            $this->logServerError($request, $e, $response, $statusCode);
        }

        return $response;
    }

    private function logServerError(Request $request, Throwable $e, SymfonyResponse $response, int $statusCode): void
    {
        try {
            $payload = $this->sanitizeArray($request->all());

            Log::channel(config('logging.server_error_channel', 'server_error'))->error('HTTP 5xx captured', [
                'status_code' => $statusCode,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_id' => $request->headers->get('X-Request-Id'),
                'user_id' => optional($request->user())->id,
                'request' => [
                    'query' => $this->sanitizeArray($request->query()),
                    'payload' => $payload,
                    'headers' => $this->sanitizeHeaders($request->headers->all()),
                    'files' => array_keys($request->allFiles()),
                ],
                'response' => [
                    'content_type' => $response->headers->get('Content-Type'),
                    'body' => $this->extractResponseBody($response),
                ],
                'exception' => [
                    'class' => get_class($e),
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ]);
        } catch (Throwable $logError) {
            // Fallback: never break error rendering because logging failed.
            Log::error('Failed to log HTTP 5xx details', [
                'reason' => $logError->getMessage(),
                'original_exception' => get_class($e),
            ]);
        }
    }

    private function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];
        foreach ($headers as $key => $value) {
            $lower = strtolower((string) $key);
            if (
                str_contains($lower, 'authorization') ||
                str_contains($lower, 'cookie') ||
                str_contains($lower, 'token') ||
                str_contains($lower, 'secret') ||
                str_contains($lower, 'api-key')
            ) {
                $sanitized[$key] = '[MASKED]';
                continue;
            }

            $sanitized[$key] = is_array($value)
                ? array_map(fn($item) => $this->truncateValue((string) $item), $value)
                : $this->truncateValue((string) $value);
        }

        return $sanitized;
    }

    private function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $lower = strtolower((string) $key);
            if (
                str_contains($lower, 'password') ||
                str_contains($lower, 'token') ||
                str_contains($lower, 'secret') ||
                str_contains($lower, 'api_key') ||
                str_contains($lower, 'authorization')
            ) {
                $sanitized[$key] = '[MASKED]';
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
                continue;
            }

            if (is_object($value)) {
                $sanitized[$key] = '[OBJECT]';
                continue;
            }

            $sanitized[$key] = $this->truncateValue((string) $value);
        }

        return $sanitized;
    }

    private function extractResponseBody(SymfonyResponse $response): string
    {
        if ($response instanceof HttpResponse) {
            return $this->truncateValue((string) $response->getContent(), 5000);
        }

        if (method_exists($response, 'getContent')) {
            try {
                return $this->truncateValue((string) $response->getContent(), 5000);
            } catch (Throwable $e) {
                return '[UNAVAILABLE_RESPONSE_CONTENT]';
            }
        }

        return '[UNAVAILABLE_RESPONSE_CONTENT]';
    }

    private function truncateValue(string $value, int $maxLength = 1000): string
    {
        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength) . '...[TRUNCATED]';
    }
}
