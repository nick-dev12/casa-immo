<?php

declare(strict_types=1);

namespace App\Helpers;

final class SecurityHelper
{
    public static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function verifyCsrf(string $token): bool
    {
        return hash_equals(\App\Core\Session::csrfToken(), $token);
    }
}
