<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;
use App\Helpers\AuthHelper;
use App\Models\User;

final class AdminMiddleware implements MiddlewareInterface
{
    public function handle(callable $next): mixed
    {
        if (!AuthHelper::check()) {
            Session::flash('auth_error', __('auth.login_required'));
            header('Location: ' . url('/login?redirect=/admin'));
            exit;
        }

        $userId = AuthHelper::id();
        if ($userId === null || !(new User())->hasRole($userId, 'admin')) {
            http_response_code(403);
            echo __('admin.error.forbidden');

            return null;
        }

        return $next();
    }
}
