<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final class NotificationEmailService
{
    public function sendToUser(
        int $userId,
        string $subject,
        string $body,
        string $targetUrl,
        ?string $actionLabel = null
    ): void {
        if ($userId <= 0) {
            return;
        }

        $preferences = user_preferences($userId);
        if (empty($preferences['email_notifications'])) {
            return;
        }

        $user = (new User())->findById($userId);
        $email = trim((string) ($user['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $firstName = trim((string) ($user['first_name'] ?? ''));
        $appName = (string) config('app', 'name', 'Casa-blog Immo');
        $url = url($targetUrl);

        $buttonLabel = $actionLabel !== null && $actionLabel !== '' ? $actionLabel : __('emails.action_open');

        ob_start();
        include base_path('views/emails/notification.php');
        $html = (string) ob_get_clean();

        // Les notifications applicatives ne doivent jamais bloquer l'action principale.
        (new MailService())->send($email, $subject, $html, $body . "\n\n" . $url);
    }
}
