<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Report extends Model
{
    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT r.*, u.first_name, u.last_name, u.email AS reporter_email
            FROM reports r
            INNER JOIN users u ON u.id = r.reporter_id
            WHERE r.id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allRecent(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->query('
            SELECT r.*, u.first_name, u.last_name, u.email AS reporter_email
            FROM reports r
            INNER JOIN users u ON u.id = r.reporter_id
            ORDER BY
                CASE r.status WHEN \'pending\' THEN 0 WHEN \'reviewed\' THEN 1 ELSE 2 END,
                r.created_at DESC
            LIMIT ' . $limit
        );

        return $stmt->fetchAll() ?: [];
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['pending', 'reviewed', 'resolved', 'dismissed'], true)) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE reports SET status = :status WHERE id = :id');

        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }
}
