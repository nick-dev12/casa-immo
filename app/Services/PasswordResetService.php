<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PasswordResetToken;
use App\Models\User;

final class PasswordResetService
{
    /**
     * @return array{ok: bool, mailed: bool, error?: string}
     */
    public function requestReset(string $email): array
    {
        $email = mb_strtolower(trim($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'mailed' => false, 'error' => 'invalid_email'];
        }

        $user = (new User())->findByEmail($email);
        if ($user === null) {
            return ['ok' => true, 'mailed' => true];
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        (new PasswordResetToken())->store($email, $code);

        $appName = (string) config('app', 'name', 'Zig Imobilier');
        $firstName = trim((string) ($user['first_name'] ?? ''));

        $html = $this->renderEmail('emails/password-reset', [
            'appName' => $appName,
            'firstName' => $firstName !== '' ? $firstName : __('auth.reset_greeting_default'),
            'resetCode' => $code,
            'expiresMinutes' => PasswordResetToken::ttlMinutes(),
        ]);

        $result = (new MailService())->send(
            $email,
            __('auth.reset_email_subject', ['name' => $appName]),
            $html,
            null,
            true
        );

        if (!$result['success']) {
            return [
                'ok' => false,
                'mailed' => false,
                'error' => $result['message'],
            ];
        }

        return [
            'ok' => true,
            'mailed' => (bool) ($result['mailed'] ?? true),
        ];
    }

    public function verifyCode(string $email, string $code): bool
    {
        $email = mb_strtolower(trim($email));
        $code = preg_replace('/\D+/', '', $code) ?? '';

        if ($email === '' || strlen($code) !== 6) {
            return false;
        }

        return (new PasswordResetToken())->isValid($email, $code);
    }

    public function resetPassword(string $email, string $token, string $password): bool|string
    {
        $email = mb_strtolower(trim($email));
        $token = preg_replace('/\D+/', '', trim($token)) ?? trim($token);

        if ($email === '' || $token === '' || strlen($password) < 8) {
            return __('auth.reset_error_invalid');
        }

        $tokenModel = new PasswordResetToken();
        if (!$tokenModel->isValid($email, $token)) {
            return __('auth.reset_error_expired');
        }

        $user = (new User())->findByEmail($email);
        if ($user === null) {
            return __('auth.reset_error_invalid');
        }

        (new User())->updatePassword((int) $user['id'], $password);
        $tokenModel->delete($email);

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderEmail(string $view, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include base_path('views/' . $view . '.php');

        return (string) ob_get_clean();
    }
}
