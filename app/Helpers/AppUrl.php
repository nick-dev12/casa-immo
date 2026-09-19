<?php

declare(strict_types=1);

namespace App\Helpers;

final class AppUrl
{
    /**
     * URL publique du site (liens, assets, e-mails).
     * Si APP_URL pointe encore vers localhost sur un hébergement en ligne, on déduit l’URL depuis la requête HTTP.
     */
    public static function resolve(): string
    {
        $configured = rtrim((string) env('APP_URL', ''), '/');

        if ($configured === '') {
            return self::fromRequest();
        }

        if (self::isLocalUrl($configured) && !self::isLocalHost()) {
            return self::fromRequest();
        }

        return $configured;
    }

    public static function fromRequest(): string
    {
        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            $fallback = rtrim((string) env('APP_URL', 'http://localhost'), '/');

            return $fallback !== '' ? $fallback : 'http://localhost';
        }

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443')
            || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
            || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '')) === 'on');

        $scheme = $https ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');

        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $basePath = rtrim(dirname($scriptName), '/');
        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        }

        return $scheme . '://' . $host . $basePath;
    }

    private static function isLocalUrl(string $url): bool
    {
        return preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?(/|$)#i', $url) === 1;
    }

    private static function isLocalHost(): bool
    {
        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));

        if ($host === '') {
            return PHP_SAPI === 'cli';
        }

        return str_contains($host, 'localhost')
            || str_starts_with($host, '127.0.0.1');
    }
}
