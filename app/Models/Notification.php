<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Notification extends Model
{
    public function unreadCountForUser(int $userId): int
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM notifications
            WHERE user_id = :user_id
              AND is_read = 0
        ');
        $stmt->execute([':user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function unreadCountByType(int $userId, string $type): int
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM notifications
            WHERE user_id = :user_id
              AND type = :type
              AND is_read = 0
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<int>
     */
    public function unreadBookingIds(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT data
            FROM notifications
            WHERE user_id = :user_id
              AND type = \'booking\'
              AND is_read = 0
            ORDER BY id DESC
        ');
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll();
        $ids = [];

        foreach ($rows as $row) {
            $payload = json_decode((string) ($row['data'] ?? ''), true);
            if (is_array($payload) && !empty($payload['booking_id'])) {
                $ids[] = (int) $payload['booking_id'];
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function latestUnreadByType(int $userId, string $type): ?array
    {
        $stmt = $this->db->prepare('
            SELECT id, type, title, body, data, created_at
            FROM notifications
            WHERE user_id = :user_id
              AND type = :type
              AND is_read = 0
            ORDER BY id DESC
            LIMIT 1
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare('
            SELECT id, type, title, body, is_read, created_at
            FROM notifications
            WHERE user_id = :user_id
            ORDER BY created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(int $userId, string $type, string $title, ?string $body = null, ?array $data = null): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO notifications (user_id, type, title, body, data, is_read)
            VALUES (:user_id, :type, :title, :body, :data, 0)
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':title' => $title,
            ':body' => $body,
            ':data' => $data !== null ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function markReadByConversation(int $userId, int $conversationId): void
    {
        $like = '%"conversation_id":' . $conversationId . '%';
        $stmt = $this->db->prepare('
            UPDATE notifications
            SET is_read = 1, read_at = NOW()
            WHERE user_id = :user_id
              AND type = \'message\'
              AND is_read = 0
              AND data LIKE :conversation_like
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':conversation_like' => $like,
        ]);
    }

    public function markReadByBooking(int $userId, int $bookingId): void
    {
        $like = '%"booking_id":' . $bookingId . '%';
        $stmt = $this->db->prepare('
            UPDATE notifications
            SET is_read = 1, read_at = NOW()
            WHERE user_id = :user_id
              AND type = \'booking\'
              AND is_read = 0
              AND data LIKE :booking_like
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':booking_like' => $like,
        ]);
    }
}
