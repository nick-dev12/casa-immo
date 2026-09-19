<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;

final class App
{
    private static ?self $instance = null;

    private Router $router;

    private Request $request;

    /** @var array<string, mixed> */
    private array $config;

    private function __construct()
    {
        $this->config = require dirname(__DIR__, 2) . '/config/app.php';
        $this->request = new Request();
        $this->router = new Router();

        date_default_timezone_set((string) $this->config['timezone']);

        View::setPath(dirname(__DIR__, 2) . '/views');
        Session::start();
        \App\Helpers\LocaleHelper::bootstrap();

        $this->registerMiddleware();
        $this->loadRoutes();
        $this->bootstrapInfrastructure();
    }

    private function bootstrapInfrastructure(): void
    {
        try {
            (new \App\Services\DatabaseMigrationService())->runPending();
            (new \App\Services\BackgroundSchedulerService())->tick();
        } catch (\Throwable $exception) {
            $dir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @file_put_contents(
                $dir . '/app-' . date('Y-m-d') . '.log',
                '[' . date('c') . '] bootstrap: ' . $exception->getMessage() . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    public static function boot(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function run(): void
    {
        $result = $this->router->dispatch($this->request);

        if (is_string($result)) {
            echo $result;
        }
    }

    public function router(): Router
    {
        return $this->router;
    }

    /** @return array<string, mixed> */
    public function config(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? $default;
    }

    private function registerMiddleware(): void
    {
        $this->router->middleware('csrf', CsrfMiddleware::class);
        $this->router->middleware('admin', AdminMiddleware::class);
    }

    private function loadRoutes(): void
    {
        $router = $this->router;
        require dirname(__DIR__, 2) . '/routes/web.php';
    }
}
