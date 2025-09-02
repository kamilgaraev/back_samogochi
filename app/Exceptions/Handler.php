<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

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
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Если это API запрос, возвращаем JSON ответ
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Обработка исключений для API запросов
     */
    protected function handleApiException(Request $request, Throwable $e)
    {
        $statusCode = 500;
        $message = 'Внутренняя ошибка сервера';
        $errors = null;

        if ($e instanceof ValidationException) {
            $statusCode = 422;
            $message = 'Ошибки валидации';
            $errors = $e->errors();
        } elseif ($e instanceof AuthenticationException) {
            $statusCode = 401;
            $message = 'Необходима авторизация';
        } elseif ($e instanceof ModelNotFoundException) {
            $statusCode = 404;
            $message = 'Ресурс не найден';
        } elseif ($e instanceof NotFoundHttpException) {
            $statusCode = 404;
            $message = 'Эндпоинт не найден';
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            $statusCode = 405;
            $message = 'Метод запроса не разрешен';
            $errors = [
                'method' => 'Данный HTTP метод не поддерживается для этого эндпоинта',
                'allowed_methods' => $e->getHeaders()['Allow'] ?? 'Неизвестно'
            ];
        } elseif ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $message = $e->getMessage() ?: $this->getDefaultMessageForStatusCode($statusCode);
        } else {
            // Для других исключений используем статус код и сообщение по умолчанию
            if (method_exists($e, 'getStatusCode')) {
                $statusCode = $e->getStatusCode();
            }
            
            if (config('app.debug')) {
                $message = $e->getMessage();
            }
        }

        $response = [
            'success' => false,
            'message' => $message,
            'status_code' => $statusCode,
            'timestamp' => now()->toISOString(),
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod()
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        // В debug режиме добавляем дополнительную информацию
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Получить сообщение по умолчанию для статус кода
     */
    protected function getDefaultMessageForStatusCode(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Неверный запрос',
            401 => 'Необходима авторизация',
            403 => 'Доступ запрещен',
            404 => 'Ресурс не найден',
            405 => 'Метод не разрешен',
            408 => 'Время ожидания запроса истекло',
            409 => 'Конфликт данных',
            410 => 'Ресурс больше не доступен',
            422 => 'Ошибки валидации',
            429 => 'Слишком много запросов',
            500 => 'Внутренняя ошибка сервера',
            502 => 'Неверный шлюз',
            503 => 'Сервис недоступен',
            504 => 'Время ожидания шлюза истекло',
            default => 'Неизвестная ошибка'
        };
    }

    /**
     * Обработка ошибок аутентификации
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Необходима авторизация',
                'status_code' => 401,
                'timestamp' => now()->toISOString()
            ], 401);
        }

        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}
