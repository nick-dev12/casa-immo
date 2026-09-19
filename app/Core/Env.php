<?php

declare(strict_types=1);

namespace App\Core;

final class Env
{
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        $file = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';

        if (!is_readable($file)) {
            self::$loaded = true;
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            self::$loaded = true;
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if ($value !== '' && (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            )) {
                $value = substr($value, 1, -1);
            }

            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("{$name}={$value}");
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        return $value;
    }
}
