<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

final class Router
{
    /** @var array<int, array{method: string, path: string, handler: callable|string, middleware: array<int, string>}> */
    private array $routes = [];

    /** @var array<string, class-string> */
    private array $middlewareAliases = [];

    public function get(string $path, callable|string $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|string $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function addRoute(string $method, string $path, callable|string $handler, array $middleware = []): self
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $this->normalizePath($path),
            'handler' => $handler,
            'middleware' => $middleware,
        ];

        return $this;
    }

    public function middleware(string $alias, string $class): self
    {
        $this->middlewareAliases[$alias] = $class;

        return $this;
    }

    public function dispatch(Request $request): mixed
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchPath($route['path'], $uri);

            if ($params === null) {
                continue;
            }

            $handler = $this->resolveHandler($route['handler'], $params);
            $pipeline = $this->buildPipeline($route['middleware']);

            return $pipeline($handler);
        }

        throw new NotFoundException("Route introuvable : {$method} {$uri}");
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * @return array<string, string>|null
     */
    private function matchPath(string $routePath, string $uri): ?array
    {
        if ($routePath === $uri) {
            return [];
        }

        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!is_string($pattern) || !preg_match($pattern, $uri, $matches)) {
            return null;
        }

        $params = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * @param array<string, string> $params
     */
    private function resolveHandler(callable|string $handler, array $params): callable
    {
        if ($handler instanceof Closure || is_callable($handler)) {
            return static function () use ($handler, $params) {
                return $handler(...array_values($params));
            };
        }

        if (!is_string($handler) || !str_contains($handler, '@')) {
            throw new \InvalidArgumentException('Handler de route invalide.');
        }

        [$controllerClass, $method] = explode('@', $handler, 2);
        $controllerClass = 'App\\Controllers\\' . ltrim($controllerClass, '\\');

        if (!class_exists($controllerClass)) {
            throw new NotFoundException("Contrôleur introuvable : {$controllerClass}");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new NotFoundException("Méthode introuvable : {$controllerClass}@{$method}");
        }

        return static function () use ($controller, $method, $params) {
            return $controller->{$method}(...array_values($params));
        };
    }

    /**
     * @param array<int, string> $middleware
     */
    private function buildPipeline(array $middleware): callable
    {
        $aliases = $this->middlewareAliases;

        return function (callable $handler) use ($middleware, $aliases): mixed {
            $pipeline = $handler;

            foreach (array_reverse($middleware) as $alias) {
                if (!isset($aliases[$alias])) {
                    throw new \InvalidArgumentException("Middleware inconnu : {$alias}");
                }

                $middlewareClass = $aliases[$alias];
                $instance = new $middlewareClass();
                $next = $pipeline;
                $pipeline = static fn (): mixed => $instance->handle($next);
            }

            return $pipeline();
        };
    }
}
