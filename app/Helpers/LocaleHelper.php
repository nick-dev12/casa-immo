<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Session;

final class LocaleHelper
{
    public const COOKIE_NAME = 'zig_lang';

    /** @var list<string> */
    public const SUPPORTED = ['fr'];

    private static string $locale = 'fr';

    /** @var array<string, string>|null */
    private static ?array $lines = null;

    public static function bootstrap(): void
    {
        $default = (string) config('app', 'locale', 'fr');
        $locale = self::normalize(Session::get('locale'));

        if ($locale === null) {
            $locale = self::normalize($_COOKIE[self::COOKIE_NAME] ?? null);
        }

        if ($locale === null) {
            $locale = self::normalize($default) ?? 'fr';
        }

        self::set($locale, false);
    }

    public static function set(string $locale, bool $persist = true): void
    {
        $locale = self::normalize($locale) ?? 'fr';
        self::$locale = $locale;
        self::$lines = null;

        if ($persist) {
            Session::set('locale', $locale);
            setcookie(self::COOKIE_NAME, $locale, [
                'expires' => time() + 365 * 24 * 60 * 60,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }
    }

    public static function current(): string
    {
        return self::$locale;
    }

    public static function isDefault(): bool
    {
        return self::$locale === 'fr';
    }

    /**
     * @return array<string, array{code: string, label: string, short: string}>
     */
    public static function options(): array
    {
        return [
            'fr' => ['code' => 'fr', 'label' => 'Français', 'short' => 'FR'],
        ];
    }

    public static function translate(string $key, array $replace = []): string
    {
        $lines = self::loadLines();
        $text = $lines[$key] ?? self::fallbackLine($key) ?? $key;

        foreach ($replace as $search => $value) {
            $text = str_replace(':' . $search, (string) $value, $text);
        }

        return $text;
    }

    private static function fallbackLine(string $key): ?string
    {
        if (self::$locale === 'fr') {
            return null;
        }

        $path = base_path('lang/fr.php');
        if (!is_file($path)) {
            return null;
        }

        /** @var array<string, string> $lines */
        $lines = require $path;

        return $lines[$key] ?? null;
    }

    /**
     * @return array<string, string>
     */
    private static function loadLines(): array
    {
        if (self::$lines !== null) {
            return self::$lines;
        }

        $path = base_path('lang/' . self::$locale . '.php');
        if (!is_file($path)) {
            $path = base_path('lang/fr.php');
        }

        /** @var array<string, string> $lines */
        $lines = is_file($path) ? require $path : [];

        self::$lines = $lines;

        return self::$lines;
    }

    private static function normalize(?string $locale): ?string
    {
        if ($locale === null || $locale === '') {
            return null;
        }

        $locale = strtolower(trim($locale));

        return in_array($locale, self::SUPPORTED, true) ? $locale : null;
    }
}
