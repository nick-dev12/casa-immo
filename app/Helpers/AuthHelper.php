<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Session;
use App\Models\User;

final class AuthHelper
{
    /** @var array<string, mixed>|null|false */
    private static array|null|false $cachedUser = false;

    public static function id(): ?int
    {
        $id = Session::get('user_id');

        return $id !== null ? (int) $id : null;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        if (self::$cachedUser === false) {
            $id = self::id();
            self::$cachedUser = $id !== null ? (new User())->findById($id) : null;
        }

        return self::$cachedUser ?: null;
    }

    public static function login(int $userId): void
    {
        Session::regenerate();
        Session::set('user_id', $userId);
        self::$cachedUser = false;
    }

    public static function logout(): void
    {
        Session::remove('user_id');
        self::$cachedUser = null;
    }

    public static function fullName(?array $user = null): string
    {
        $user ??= self::user();
        if ($user === null) {
            return '';
        }

        return trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''));
    }

    public static function isAgency(?int $userId = null): bool
    {
        $userId ??= self::id();
        if ($userId === null) {
            return false;
        }

        return (new User())->hasRole($userId, 'agency');
    }

    public static function isAdmin(?int $userId = null): bool
    {
        $userId ??= self::id();
        if ($userId === null) {
            return false;
        }

        return (new User())->hasRole($userId, 'admin');
    }

    public static function homePath(?int $userId = null): string
    {
        $userId ??= self::id();
        if ($userId === null) {
            return '/profile';
        }

        $userModel = new User();
        if ($userModel->hasRole($userId, 'admin')) {
            return '/admin';
        }

        if ($userModel->hasRole($userId, 'agency')
            || $userModel->hasRole($userId, 'owner')
            || $userModel->hasRole($userId, 'land_seller')) {
            $establishment = (new \App\Models\Establishment())->findByOwnerId($userId);
            if ($establishment !== null) {
                return '/host';
            }
        }

        return '/profile';
    }

    /** guest | client | host | admin — barre de navigation mobile */
    public static function bottomNavProfileKind(?int $userId = null): string
    {
        if (!self::check()) {
            return 'guest';
        }

        $path = self::homePath($userId);
        if ($path === '/host' || self::isAgency($userId)) {
            return 'host';
        }
        if ($path === '/admin') {
            return 'admin';
        }

        return 'client';
    }
}
