<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Model;

final class AdminUserService extends Model
{
    /**
     * @return array{total: int, active: int, inactive: int, banned: int}
     */
    public function stats(): array
    {
        $row = $this->db->query('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'active\' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = \'inactive\' THEN 1 ELSE 0 END) AS inactive,
                SUM(CASE WHEN status = \'banned\' THEN 1 ELSE 0 END) AS banned
            FROM users
            WHERE deleted_at IS NULL
        ')->fetch();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'active' => (int) ($row['active'] ?? 0),
            'inactive' => (int) ($row['inactive'] ?? 0),
            'banned' => (int) ($row['banned'] ?? 0),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allForAdmin(int $limit = 200): array
    {
        $limit = max(1, min($limit, 300));
        $stmt = $this->db->query('
            SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.status,
                   u.created_at, u.last_login_at,
                   GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR \', \') AS roles,
                   GROUP_CONCAT(DISTINCT r.slug ORDER BY r.slug SEPARATOR \',\') AS role_slugs,
                   (
                       SELECT COUNT(*)
                       FROM properties p
                       WHERE p.owner_id = u.id AND p.deleted_at IS NULL
                   ) AS property_count,
                   (
                       SELECT COUNT(*)
                       FROM lands l
                       WHERE l.seller_id = u.id AND l.deleted_at IS NULL
                   ) AS land_count,
                   (
                       SELECT e.name
                       FROM establishments e
                       WHERE e.owner_id = u.id
                       LIMIT 1
                   ) AS establishment_name
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            WHERE u.deleted_at IS NULL
            GROUP BY u.id
            ORDER BY u.created_at DESC
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
            SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.status,
                   u.created_at, u.last_login_at,
                   GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR \', \') AS roles,
                   GROUP_CONCAT(DISTINCT r.slug ORDER BY r.slug SEPARATOR \',\') AS role_slugs,
                   (
                       SELECT COUNT(*)
                       FROM properties p
                       WHERE p.owner_id = u.id AND p.deleted_at IS NULL
                   ) AS property_count,
                   (
                       SELECT COUNT(*)
                       FROM lands l
                       WHERE l.seller_id = u.id AND l.deleted_at IS NULL
                   ) AS land_count,
                   (
                       SELECT e.id
                       FROM establishments e
                       WHERE e.owner_id = u.id
                       LIMIT 1
                   ) AS establishment_id,
                   (
                       SELECT e.name
                       FROM establishments e
                       WHERE e.owner_id = u.id
                       LIMIT 1
                   ) AS establishment_name
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            WHERE u.id = :id AND u.deleted_at IS NULL
            GROUP BY u.id
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function hasAdminRole(array $user): bool
    {
        $slugs = explode(',', (string) ($user['role_slugs'] ?? ''));

        return in_array('admin', $slugs, true);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function propertiesForUser(int $userId, int $limit = 100): array
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
            WHERE p.deleted_at IS NULL AND p.owner_id = :user_id
            ORDER BY p.created_at DESC
            LIMIT ' . $limit . '
        ');
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function landsForUser(int $userId, int $limit = 100): array
    {
        $limit = max(1, min($limit, 200));
        $stmt = $this->db->prepare('
            SELECT l.id, l.title, l.land_type, l.city, l.price, l.currency, l.status, l.created_at,
                   l.area, l.area_unit,
                   u.first_name, u.last_name,
                   (
                       SELECT li.path
                       FROM land_images li
                       WHERE li.land_id = l.id
                       ORDER BY li.is_primary DESC, li.sort_order ASC
                       LIMIT 1
                   ) AS primary_image,
                   (
                       SELECT COUNT(*)
                       FROM land_images li2
                       WHERE li2.land_id = l.id
                   ) AS image_count
            FROM lands l
            INNER JOIN users u ON u.id = l.seller_id
            WHERE l.deleted_at IS NULL AND l.seller_id = :user_id
            ORDER BY l.created_at DESC
            LIMIT ' . $limit . '
        ');
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll() ?: [];
    }
}
