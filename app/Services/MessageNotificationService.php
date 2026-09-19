<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuthHelper;
use App\Models\Conversation;
use App\Models\Notification;
use App\Models\User;

final class MessageNotificationService
{
    /**
     * @param array<string, mixed> $message
     */
    public function notifyRecipient(int $conversationId, int $senderId, array $message): void
    {
        $recipientId = (new Conversation())->recipientUserId($conversationId, $senderId);
        if ($recipientId === null) {
            return;
        }

        $sender = (new User())->findById($senderId);
        $senderName = AuthHelper::fullName($sender);
        if ($senderName === '') {
            $senderName = __('messages.unknown');
        }

        $body = trim((string) ($message['body'] ?? ''));
        if (mb_strlen($body) > 160) {
            $body = mb_substr($body, 0, 157) . '…';
        }

        $title = __('messages.notify.title', ['name' => $senderName]);

        (new Notification())->create(
            $recipientId,
            'message',
            $title,
            $body,
            [
                'conversation_id' => $conversationId,
                'message_id' => (int) ($message['id'] ?? 0),
                'sender_id' => $senderId,
            ]
        );

        (new NotificationEmailService())->sendToUser(
            $recipientId,
            $title,
            $body,
            '/messages/' . $conversationId
        );
    }
}
