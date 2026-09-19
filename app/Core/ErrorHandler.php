<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class ErrorHandler
{
    private static bool $registered = false;

    public static function register(bool $debug): void
    {
        if (self::$registered) {
            return;
        }

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            // Les dépréciations PHP 8.5+ ne doivent pas faire échouer une requête (ex. publication).
            if (($severity & (E_DEPRECATED | E_USER_DEPRECATED)) !== 0) {
                return true;
            }

            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(static function (Throwable $exception) use ($debug): void {
            self::handleException($exception, $debug);
        });

        register_shutdown_function(static function () use ($debug): void {
            $error = error_get_last();

            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                self::handleException(
                    new \ErrorException(
                        $error['message'],
                        0,
                        $error['type'],
                        $error['file'],
                        $error['line']
                    ),
                    $debug
                );
            }
        });

        self::$registered = true;
    }

    private static function handleException(Throwable $exception, bool $debug): void
    {
        self::log($exception);

        $request = new Request();

        if ($exception instanceof NotFoundException) {
            http_response_code(404);
        } elseif ($exception instanceof HttpException) {
            http_response_code($exception->getStatusCode());
        } else {
            http_response_code(500);
        }

        if ($request->wantsJson()) {
            $payload = [
                'success' => false,
                'message' => $exception instanceof HttpException
                    ? $exception->getMessage()
                    : 'Une erreur interne est survenue.',
            ];

            if ($debug && !($exception instanceof HttpException)) {
                $payload['error'] = [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($payload, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $statusCode = http_response_code() ?: 500;
        $view = $statusCode === 404 ? 'errors/404' : 'errors/500';

        try {
            echo View::render($view, [
                'title' => $statusCode === 404 ? 'Page introuvable' : 'Erreur serveur',
                'message' => $exception instanceof HttpException
                    ? $exception->getMessage()
                    : 'Une erreur interne est survenue.',
                'debug' => $debug,
                'exception' => $debug ? $exception : null,
            ], 'layouts/main');
        } catch (Throwable) {
            echo '<h1>Erreur ' . htmlspecialchars((string) $statusCode, ENT_QUOTES, 'UTF-8') . '</h1>';
        }

        exit;
    }

    private static function log(Throwable $exception): void
    {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        $logFile = $logDir . '/app-' . date('Y-m-d') . '.log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $entry = sprintf(
            "[%s] %s: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
