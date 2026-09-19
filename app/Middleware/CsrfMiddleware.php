<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Session;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(callable $next): mixed
    {
        $request = new Request();

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            if ($request->isPostBodyTruncated()) {
                throw new HttpException(__('host.error.post_too_large'), 413);
            }

            /** @var array<string, mixed> $config */
            $config = require dirname(__DIR__, 2) . '/config/app.php';
            $tokenName = (string) $config['csrf_token_name'];
            $submitted = (string) ($request->input($tokenName) ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

            if (!hash_equals(Session::csrfToken(), $submitted)) {
                throw new HttpException('Jeton CSRF invalide.', 419);
            }
        }

        return $next();
    }
}
