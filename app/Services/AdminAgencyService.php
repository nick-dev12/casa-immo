<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Model;

final class AdminAgencyService extends Model
{
    /**
     * @return array{total: int, active: int, suspended: int, draft: int}
     */
    public function stats(): array
    {
        $row = $this->db->query('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'active\' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = \'suspended\' THEN 1 ELSE 0 END) AS suspended,
                SUM(CASE WHEN status = \'draft\' THEN 1 ELSE 0 END) AS draft
            FROM establishments
        ')->fetch();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'active' => (int) ($row['active'] ?? 0),
            'suspended' => (int) ($row['suspended'] ?? 0),
            'draft' => (int) ($row['draft'] ?? 0),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allForAdmin(int $limit = 100): array
    {
        $limit = max(1, min($limit, 200));
        $stmt = $this->db->query('
            SELECT e.*,
                   u.first_name, u.last_name, u.email, u.phone AS owner_phone, u.status AS owner_status,
                   (
                       SELECT COUNT(*)
                       FROM properties p
                       WHERE p.establishment_id = e.id AND p.deleted_at IS NULL
                   ) AS property_count,
                   (
                       SELECT COUNT(*)
                       FROM properties p
                       WHERE p.establishment_id = e.id
                         AND p.deleted_at IS NULL
                         AND p.status = \'approved\'
                   ) AS property_published_count
            FROM establishments e
            INNER JOIN users u ON u.id = e.owner_id
            ORDER BY e.created_at DESC
            LIMIT ' . $limit . '
        ');

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForAdmin(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT e.*,
                   u.first_name, u.last_name, u.email, u.phone AS owner_phone, u.status AS owner_status,
                   (
                       SELECT COUNT(*)
                       FROM properties p
                       WHERE p.establishment_id = e.id AND p.deleted_at IS NULL
                   ) AS property_count,
                   (
                       SELECT COUNT(*)
                       FROM properties p
                       WHERE p.establishment_id = e.id
                         AND p.deleted_at IS NULL
                         AND p.status = \'approved\'
                   ) AS property_published_count
            FROM establishments e
            INNER JOIN users u ON u.id = e.owner_id
            WHERE e.id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function propertiesForAgency(int $establishmentId, int $limit = 100): array
    {
        $limit = max(1, min($limit, 200));
        $stmt = $this->db->prepare('
            SELECT p.id, p.title, p.type, p.city, p.status, p.created_at,
                   u.first_name, u.last_name, e.name AS establishment_name,
                   pp.price_per_night, pp.price_per_month, pp.currency AS price_currency,
                   (
                       SELECT pi.path
                       FROM property_images pi
                       WHERE pi.property_id = p.id
                       ORDER BY pi.is_primary DESC, pi.sort_order ASC
                       LIMIT 1
                   ) AS primary_image,
                   (
                       SELECT COUNT(*)
                       FROM property_images pi2
                       WHERE pi2.property_id = p.id
                   ) AS image_count
            FROM properties p
            INNER JOIN users u ON u.id = p.owner_id
            LEFT JOIN establishments e ON e.id = p.establishment_id
            LEFT JOIN property_prices pp ON pp.property_id = p.id
            WHERE p.deleted_at IS NULL
              AND p.establishment_id = :establishment_id
            ORDER BY p.created_at DESC
            LIMIT ' . $limit . '
        ');
        $stmt->execute([':establishment_id' => $establishmentId]);

        return $stmt->fetchAll() ?: [];
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['active', 'suspended', 'draft'], true)) {
            return false;
        }

        $stmt = $this->db->prepare('
            UPDATE establishments
            SET status = :status, updated_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([':id' => $id, ':status' => $status]);

        return $stmt->rowCount() > 0;
    }

    public function deleteAgency(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM establishments WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
