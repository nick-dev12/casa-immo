<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);

        if (!is_string($uri) || $uri === '') {
            return '/';
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($basePath !== '' && $basePath !== '/' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        $uri = '/' . trim($uri, '/');

        // Accès direct à index.php (fichier existant, sans réécriture) → page d'accueil
        if ($uri === '/index.php') {
            return '/';
        }

        return $uri === '/' ? '/' : rtrim($uri, '/');
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    public function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        return str_contains($accept, 'application/json') || $this->isAjax();
    }

    /**
     * POST tronqué ou refusé quand post_max_size est dépassé.
     */
    public function isPostBodyTruncated(): bool
    {
        if (!$this->isPost()) {
            return false;
        }

        $length = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($length <= 0) {
            return false;
        }

        $postMax = self::iniToBytes((string) ini_get('post_max_size'));
        if ($postMax > 0 && $length > $postMax) {
            return true;
        }

        if ($_POST !== []) {
            return false;
        }

        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
        if (str_contains($contentType, 'multipart/form-data') && $length > 1024) {
            return true;
        }

        return $_FILES === [] && $length > 1024;
    }

    private static function iniToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    public function file(string $key): ?array
    {
        $file = $_FILES[$key] ?? null;

        return is_array($file) ? $file : null;
    }

    public function hasUploadedFile(string $key): bool
    {
        $file = $this->file($key);
        if ($file === null) {
            return false;
        }

        if (!isset($file['name'])) {
            return false;
        }

        if (is_array($file['name'])) {
            foreach ($file['error'] ?? [] as $error) {
                if ((int) $error !== UPLOAD_ERR_NO_FILE) {
                    return true;
                }
            }

            return false;
        }

        return (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }
}
