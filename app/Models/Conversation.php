<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Helpers\AuthHelper;

final class Conversation extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function forUser(int $userId): array
    {
        $listingSelect = $this->hasListingColumns()
            ? '
                c.property_id,
                c.land_id,
                p.title AS property_title,
                p.city AS property_city,
                p.type AS property_type,
                (
                    SELECT pi.path
                    FROM property_images pi
                    WHERE pi.property_id = p.id
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC
                    LIMIT 1
                ) AS property_image,
                l.title AS land_title,
                l.city AS land_city,
                l.land_type,
                (
                    SELECT li.path
                    FROM land_images li
                    WHERE li.land_id = l.id
                    ORDER BY li.is_primary DESC, li.sort_order ASC
                    LIMIT 1
                ) AS land_image,
            '
            : '
                NULL AS property_id,
                NULL AS land_id,
                NULL AS property_title,
                NULL AS property_city,
                NULL AS property_type,
                NULL AS property_image,
                NULL AS land_title,
                NULL AS land_city,
                NULL AS land_type,
                NULL AS land_image,
            ';

        $listingJoin = $this->hasListingColumns()
            ? '
                LEFT JOIN properties p ON p.id = c.property_id
                LEFT JOIN lands l ON l.id = c.land_id
            '
            : '';

        $sql = '
            SELECT
                c.id,
                c.subject,
                c.updated_at,
                ' . $listingSelect . '
                other.id AS other_id,
                other.first_name AS other_first_name,
                other.last_name AS other_last_name,
                other.avatar AS other_avatar,
                (
                    SELECT m.body
                    FROM messages m
                    WHERE m.conversation_id = c.id
                    ORDER BY m.id DESC
                    LIMIT 1
                ) AS last_body,
                (
                    SELECT m.created_at
                    FROM messages m
                    WHERE m.conversation_id = c.id
                    ORDER BY m.id DESC
                    LIMIT 1
                ) AS last_at,
                (
                    SELECT m.sender_id
                    FROM messages m
                    WHERE m.conversation_id = c.id
                    ORDER BY m.id DESC
                    LIMIT 1
                ) AS last_sender_id,
                (
                    SELECT COUNT(*)
                    FROM messages m
                    WHERE m.conversation_id = c.id
                      AND m.sender_id <> :unread_user
                      AND m.is_read = 0
                ) AS unread_count
            FROM conversations c
            INNER JOIN conversation_participants me
                ON me.conversation_id = c.id AND me.user_id = :user_id
            LEFT JOIN conversation_participants op
                ON op.conversation_id = c.id AND op.user_id <> :other_user
            LEFT JOIN users other
                ON other.id = op.user_id AND other.deleted_at IS NULL
            ' . $listingJoin . '
            ORDER BY COALESCE(
                (
                    SELECT m.created_at
                    FROM messages m
                    WHERE m.conversation_id = c.id
                    ORDER BY m.id DESC
                    LIMIT 1
                ),
                c.updated_at
            ) DESC, c.id DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':other_user' => $userId,
            ':unread_user' => $userId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForParticipant(int $conversationId, int $userId): ?array
    {
        $listingSelect = $this->hasListingColumns()
            ? 'c.property_id, c.land_id,'
            : 'NULL AS property_id, NULL AS land_id,';

        $stmt = $this->db->prepare('
            SELECT
                c.id,
                c.subject,
                ' . $listingSelect . '
                c.created_at,
                c.updated_at
            FROM conversations c
            INNER JOIN conversation_participants cp
                ON cp.conversation_id = c.id AND cp.user_id = :user_id
            WHERE c.id = :id
            LIMIT 1
        ');
        $stmt->execute([
            ':id' => $conversationId,
            ':user_id' => $userId,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function otherParticipant(int $conversationId, int $userId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.id, u.first_name, u.last_name, u.avatar, u.phone
            FROM conversation_participants cp
            INNER JOIN users u ON u.id = cp.user_id AND u.deleted_at IS NULL
            WHERE cp.conversation_id = :conversation_id
              AND cp.user_id <> :user_id
            LIMIT 1
        ');
        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':user_id' => $userId,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findOrCreateForListing(
        int $userId,
        int $otherUserId,
        ?int $propertyId,
        ?int $landId,
        string $subject
    ): int {
        $existing = $this->findBetween($userId, $otherUserId, $propertyId, $landId);
        if ($existing !== null) {
            return (int) $existing['id'];
        }

        return $this->createThread($subject, [$userId, $otherUserId], $propertyId, $landId);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function messages(int $conversationId, int $afterId = 0): array
    {
        $sql = '
            SELECT id, conversation_id, sender_id, body, is_read, created_at
            FROM messages
            WHERE conversation_id = :conversation_id
        ';
        $params = [':conversation_id' => $conversationId];

        if ($afterId > 0) {
            $sql .= ' AND id > :after_id';
            $params[':after_id'] = $afterId;
        }

        $sql .= ' ORDER BY id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>
     */
    public function send(int $conversationId, int $senderId, string $body): array
    {
        $stmt = $this->db->prepare('
            INSERT INTO messages (conversation_id, sender_id, body, is_read)
            VALUES (:conversation_id, :sender_id, :body, 0)
        ');
        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':sender_id' => $senderId,
            ':body' => $body,
        ]);

        $id = (int) $this->db->lastInsertId();

        $touch = $this->db->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = :id');
        $touch->execute([':id' => $conversationId]);

        $row = $this->db->prepare('
            SELECT id, conversation_id, sender_id, body, is_read, created_at
            FROM messages
            WHERE id = :id
            LIMIT 1
        ');
        $row->execute([':id' => $id]);
        $message = $row->fetch();

        return is_array($message) ? $message : [
            'id' => $id,
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $body,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function recipientUserId(int $conversationId, int $senderId): ?int
    {
        $stmt = $this->db->prepare('
            SELECT cp.user_id
            FROM conversation_participants cp
            WHERE cp.conversation_id = :conversation_id
              AND cp.user_id <> :sender_id
            LIMIT 1
        ');
        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':sender_id' => $senderId,
        ]);
        $row = $stmt->fetch();

        return $row ? (int) $row['user_id'] : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function latestIncomingMessage(int $userId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT
                m.id,
                m.conversation_id,
                m.body,
                m.created_at,
                u.first_name,
                u.last_name
            FROM messages m
            INNER JOIN conversation_participants cp
                ON cp.conversation_id = m.conversation_id AND cp.user_id = :user_id
            INNER JOIN users u
                ON u.id = m.sender_id AND u.deleted_at IS NULL
            WHERE m.sender_id <> :sender_id
            ORDER BY m.id DESC
            LIMIT 1
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':sender_id' => $userId,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function unreadConversationsSummary(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT
                c.id,
                (
                    SELECT COUNT(*)
                    FROM messages m
                    WHERE m.conversation_id = c.id
                      AND m.sender_id <> :user_id
                      AND m.is_read = 0
                ) AS unread_count,
                (
                    SELECT m.body
                    FROM messages m
                    WHERE m.conversation_id = c.id
                    ORDER BY m.id DESC
                    LIMIT 1
                ) AS last_body,
                (
                    SELECT m.created_at
                    FROM messages m
                    WHERE m.conversation_id = c.id
                    ORDER BY m.id DESC
                    LIMIT 1
                ) AS last_at,
                other.first_name AS other_first_name,
                other.last_name AS other_last_name
            FROM conversations c
            INNER JOIN conversation_participants me
                ON me.conversation_id = c.id AND me.user_id = :user_id_join
            LEFT JOIN conversation_participants op
                ON op.conversation_id = c.id AND op.user_id <> :other_user
            LEFT JOIN users other
                ON other.id = op.user_id AND other.deleted_at IS NULL
            WHERE (
                SELECT COUNT(*)
                FROM messages m
                WHERE m.conversation_id = c.id
                  AND m.sender_id <> :filter_user
                  AND m.is_read = 0
            ) > 0
            ORDER BY (
                SELECT m.created_at
                FROM messages m
                WHERE m.conversation_id = c.id
                ORDER BY m.id DESC
                LIMIT 1
            ) DESC
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':user_id_join' => $userId,
            ':other_user' => $userId,
            ':filter_user' => $userId,
        ]);

        $rows = $stmt->fetchAll();
        $items = [];

        foreach ($rows as $row) {
            $other = [
                'first_name' => $row['other_first_name'] ?? '',
                'last_name' => $row['other_last_name'] ?? '',
            ];
            $items[] = [
                'id' => (int) $row['id'],
                'unread_count' => (int) $row['unread_count'],
                'other_name' => AuthHelper::fullName($other) !== '' ? AuthHelper::fullName($other) : __('messages.unknown'),
                'last_body' => trim((string) ($row['last_body'] ?? '')),
                'last_at' => format_message_time((string) ($row['last_at'] ?? '')),
            ];
        }

        return $items;
    }

    public function markRead(int $conversationId, int $userId): void
    {
        $stmt = $this->db->prepare('
            UPDATE messages
            SET is_read = 1, read_at = NOW()
            WHERE conversation_id = :conversation_id
              AND sender_id <> :user_id
              AND is_read = 0
        ');
        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':user_id' => $userId,
        ]);
    }

    public function unreadCountForUser(int $userId): int
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM messages m
            INNER JOIN conversation_participants cp
                ON cp.conversation_id = m.conversation_id AND cp.user_id = :user_id
            WHERE m.sender_id <> :sender_id
              AND m.is_read = 0
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':sender_id' => $userId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param list<int> $userIds
     */
    private function createThread(string $subject, array $userIds, ?int $propertyId, ?int $landId): int
    {
        $this->db->beginTransaction();

        try {
            if ($this->hasListingColumns()) {
                $stmt = $this->db->prepare('
                    INSERT INTO conversations (subject, property_id, land_id)
                    VALUES (:subject, :property_id, :land_id)
                ');
                $stmt->execute([
                    ':subject' => $subject !== '' ? $subject : null,
                    ':property_id' => $propertyId,
                    ':land_id' => $landId,
                ]);
            } else {
                $stmt = $this->db->prepare('
                    INSERT INTO conversations (subject)
                    VALUES (:subject)
                ');
                $stmt->execute([
                    ':subject' => $subject !== '' ? $subject : null,
                ]);
            }

            $conversationId = (int) $this->db->lastInsertId();
            $participant = $this->db->prepare('
                INSERT INTO conversation_participants (conversation_id, user_id)
                VALUES (:conversation_id, :user_id)
            ');

            foreach (array_unique($userIds) as $id) {
                if ($id <= 0) {
                    continue;
                }
                $participant->execute([
                    ':conversation_id' => $conversationId,
                    ':user_id' => $id,
                ]);
            }

            $this->db->commit();

            return $conversationId;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findBetween(int $userId, int $otherUserId, ?int $propertyId, ?int $landId): ?array
    {
        $sql = '
            SELECT c.id
            FROM conversations c
            INNER JOIN conversation_participants p1
                ON p1.conversation_id = c.id AND p1.user_id = :user_id
            INNER JOIN conversation_participants p2
                ON p2.conversation_id = c.id AND p2.user_id = :other_id
            WHERE 1 = 1
        ';
        $params = [
            ':user_id' => $userId,
            ':other_id' => $otherUserId,
        ];

        if ($this->hasListingColumns()) {
            if ($propertyId !== null && $propertyId > 0) {
                $sql .= ' AND c.property_id = :property_id';
                $params[':property_id'] = $propertyId;
            } elseif ($landId !== null && $landId > 0) {
                $sql .= ' AND c.land_id = :land_id';
                $params[':land_id'] = $landId;
            }
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    private function hasListingColumns(): bool
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        try {
            $stmt = $this->db->query('SHOW COLUMNS FROM conversations LIKE \'property_id\'');
            $cached = $stmt !== false && $stmt->fetch() !== false;
        } catch (\Throwable) {
            $cached = false;
        }

        return $cached;
    }
}
