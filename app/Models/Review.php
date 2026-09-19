<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Review extends Model
{
    public function countForUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare('
            SELECT
                r.id,
                r.rating,
                r.comment,
                r.created_at,
                p.id AS property_id,
                p.title,
                p.city,
                p.type
            FROM reviews r
            INNER JOIN properties p ON p.id = r.property_id
            WHERE r.user_id = :user_id
              AND p.deleted_at IS NULL
            ORDER BY r.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function existsForBooking(int $bookingId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM reviews WHERE booking_id = :booking_id LIMIT 1');
        $stmt->execute([':booking_id' => $bookingId]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * @param array{user_id: int, property_id: int, booking_id: int, rating: int, comment: ?string} $data
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO reviews (user_id, property_id, booking_id, rating, comment)
            VALUES (:user_id, :property_id, :booking_id, :rating, :comment)
        ');
        $stmt->execute([
            ':user_id' => (int) $data['user_id'],
            ':property_id' => (int) $data['property_id'],
            ':booking_id' => (int) $data['booking_id'],
            ':rating' => (int) $data['rating'],
            ':comment' => $data['comment'],
        ]);

        return (int) $this->db->lastInsertId();
    }
}
