<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Favorite extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function forUser(int $userId): array
    {
        $properties = $this->fetchProperties($userId);
        $lands = $this->fetchLands($userId);

        $items = array_merge($properties, $lands);

        usort($items, static function (array $a, array $b): int {
            return strcmp((string) ($b['favorited_at'] ?? ''), (string) ($a['favorited_at'] ?? ''));
        });

        return $items;
    }

    public function countForUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM favorites WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<int>
     */
    public function propertyIdsForUser(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT favoritable_id
            FROM favorites
            WHERE user_id = :user_id AND favoritable_type = \'property\'
        ');
        $stmt->execute([':user_id' => $userId]);

        return array_map('intval', array_column($stmt->fetchAll(), 'favoritable_id'));
    }

    /**
     * @return list<int>
     */
    public function landIdsForUser(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT favoritable_id
            FROM favorites
            WHERE user_id = :user_id AND favoritable_type = \'land\'
        ');
        $stmt->execute([':user_id' => $userId]);

        return array_map('intval', array_column($stmt->fetchAll(), 'favoritable_id'));
    }

    public function isFavorite(int $userId, string $type, int $id): bool
    {
        $stmt = $this->db->prepare('
            SELECT 1 FROM favorites
            WHERE user_id = :user_id
              AND favoritable_type = :type
              AND favoritable_id = :id
            LIMIT 1
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':id' => $id,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function toggle(int $userId, string $type, int $id): bool
    {
        if (!in_array($type, ['property', 'land'], true) || $id <= 0) {
            return false;
        }

        if ($this->isFavorite($userId, $type, $id)) {
            $stmt = $this->db->prepare('
                DELETE FROM favorites
                WHERE user_id = :user_id
                  AND favoritable_type = :type
                  AND favoritable_id = :id
            ');
            $stmt->execute([
                ':user_id' => $userId,
                ':type' => $type,
                ':id' => $id,
            ]);

            return false;
        }

        $stmt = $this->db->prepare('
            INSERT INTO favorites (user_id, favoritable_type, favoritable_id)
            VALUES (:user_id, :type, :id)
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':id' => $id,
        ]);

        return true;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchProperties(int $userId): array
    {
        $sql = '
            SELECT
                \'property\' AS favorite_type,
                f.created_at AS favorited_at,
                p.id,
                p.title,
                p.type,
                p.city,
                p.district,
                p.address,
                p.country,
                pp.price_per_night,
                pp.currency AS price_currency,
                COALESCE(ROUND(AVG(r.rating), 1), 0) AS avg_rating,
                (
                    SELECT pi.path
                    FROM property_images pi
                    WHERE pi.property_id = p.id
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC
                    LIMIT 1
                ) AS primary_image
            FROM favorites f
            INNER JOIN properties p ON p.id = f.favoritable_id
            LEFT JOIN property_prices pp ON pp.property_id = p.id
            LEFT JOIN reviews r ON r.property_id = p.id
            WHERE f.user_id = :user_id
              AND f.favoritable_type = \'property\'
              AND p.status = \'approved\'
              AND p.deleted_at IS NULL
            GROUP BY f.id, p.id, pp.price_per_night, pp.currency, f.created_at
            ORDER BY f.created_at DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchLands(int $userId): array
    {
        $sql = '
            SELECT
                \'land\' AS favorite_type,
                f.created_at AS favorited_at,
                l.id,
                l.title,
                l.land_type,
                l.city,
                l.district,
                l.address,
                l.price,
                l.currency,
                l.area,
                l.area_unit,
                (
                    SELECT li.path
                    FROM land_images li
                    WHERE li.land_id = l.id
                    ORDER BY li.is_primary DESC, li.sort_order ASC
                    LIMIT 1
                ) AS primary_image
            FROM favorites f
            INNER JOIN lands l ON l.id = f.favoritable_id
            WHERE f.user_id = :user_id
              AND f.favoritable_type = \'land\'
              AND l.status = \'approved\'
              AND l.deleted_at IS NULL
            ORDER BY f.created_at DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
