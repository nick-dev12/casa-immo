<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ModerationAction extends Model
{
  /**
     * @param list<string> $motives
     */
    public function create(int $adminId, string $targetType, int $targetId, string $action, array $motives, string $notes = ''): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO moderation_actions (admin_id, target_type, target_id, action, motives, notes)
            VALUES (:admin_id, :target_type, :target_id, :action, :motives, :notes)
        ');
        $stmt->execute([
            ':admin_id' => $adminId,
            ':target_type' => $targetType,
            ':target_id' => $targetId,
            ':action' => $action,
            ':motives' => json_encode(array_values($motives), JSON_THROW_ON_ERROR),
            ':notes' => $notes !== '' ? $notes : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forTarget(string $targetType, int $targetId, int $limit = 20): array
    {
        $limit = max(1, min($limit, 50));
        $stmt = $this->db->prepare('
            SELECT ma.*, u.first_name, u.last_name
            FROM moderation_actions ma
            INNER JOIN users u ON u.id = ma.admin_id
            WHERE ma.target_type = :target_type
              AND ma.target_id = :target_id
            ORDER BY ma.created_at DESC
            LIMIT ' . $limit . '
        ');
        $stmt->execute([
            ':target_type' => $targetType,
            ':target_id' => $targetId,
        ]);

        $rows = $stmt->fetchAll() ?: [];
        foreach ($rows as &$row) {
            $decoded = json_decode((string) ($row['motives'] ?? '[]'), true);
            $row['motives'] = is_array($decoded) ? $decoded : [];
        }
        unset($row);

        return $rows;
    }
}
