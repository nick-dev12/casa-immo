<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Land extends Model
{
    /** @return array<int, string> */
    public static function types(): array
    {
        return [
            'residentiel' => 'Résidentiel',
            'commercial' => 'Commercial',
            'agricole' => 'Agricole',
            'industriel' => 'Industriel',
            'autre' => 'Autre',
        ];
    }

    /** @return array<int, string> */
    public static function paperTypes(): array
    {
        return [
            'titre_foncier' => 'Titre foncier',
            'deliberation' => 'Délibération',
            'bail_emphyteotique' => 'Bail emphytéotique',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPopular(int $limit = 6): array
    {
        $sql = $this->baseSelectSql() . '
            ORDER BY l.created_at DESC
            LIMIT :limit
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function search(array $filters = [], int $limit = 24, int $offset = 0): array
    {
        [$where, $params] = $this->buildFilters($filters);

        $sql = $this->baseSelectSql() . $where . '
            ORDER BY l.created_at DESC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countSearch(array $filters = []): int
    {
        [$where, $params] = $this->buildFilters($filters);

        $sql = '
            SELECT COUNT(*)
            FROM lands l
            WHERE l.status = \'approved\' AND l.deleted_at IS NULL
        ' . $where;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = $this->baseSelectSql() . ' AND l.id = :id LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getImages(int $landId): array
    {
        $stmt = $this->db->prepare('
            SELECT path, is_primary, sort_order
            FROM land_images
            WHERE land_id = :id
            ORDER BY is_primary DESC, sort_order ASC
        ');
        $stmt->execute([':id' => $landId]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<int, string>
     */
    public function getCities(): array
    {
        $stmt = $this->db->query('
            SELECT DISTINCT city
            FROM lands
            WHERE status = \'approved\' AND deleted_at IS NULL
            ORDER BY city ASC
        ');

        return array_column($stmt->fetchAll(), 'city');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function suggest(string $query, string $city = '', int $limit = 8): array
    {
        $sql = $this->baseSelectSql() . '
            AND (
                l.title LIKE :q_title OR l.city LIKE :q_city OR l.district LIKE :q_district
            )
        ';
        $like = '%' . $query . '%';
        $params = [
            ':q_title' => $like,
            ':q_city' => $like,
            ':q_district' => $like,
        ];

        if ($city !== '') {
            $sql .= ' AND l.city = :city';
            $params[':city'] = $city;
        }

        $sql .= ' ORDER BY l.title ASC LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function baseSelectSql(): string
    {
        return '
            SELECT l.*,
                (
                    SELECT li.path
                    FROM land_images li
                    WHERE li.land_id = l.id
                    ORDER BY li.is_primary DESC, li.sort_order ASC
                    LIMIT 1
                ) AS primary_image
            FROM lands l
            WHERE l.status = \'approved\' AND l.deleted_at IS NULL
        ';
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildFilters(array $filters): array
    {
        $where = '';
        $params = [];

        if (!empty($filters['q'])) {
            $like = '%' . $filters['q'] . '%';
            $where .= ' AND (l.title LIKE :q_title OR l.city LIKE :q_city OR l.district LIKE :q_district OR l.description LIKE :q_desc)';
            $params[':q_title'] = $like;
            $params[':q_city'] = $like;
            $params[':q_district'] = $like;
            $params[':q_desc'] = $like;
        }

        if (!empty($filters['city'])) {
            $where .= ' AND l.city = :city';
            $params[':city'] = $filters['city'];
        }

        if (!empty($filters['district'])) {
            $where .= ' AND l.district LIKE :district';
            $params[':district'] = '%' . (string) $filters['district'] . '%';
        }

        if (!empty($filters['type'])) {
            $where .= ' AND l.land_type = :type';
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['paper_type'])) {
            $where .= ' AND l.paper_type = :paper_type';
            $params[':paper_type'] = $filters['paper_type'];
        }

        if (isset($filters['price_min']) && $filters['price_min'] !== '') {
            $where .= ' AND l.price >= :price_min';
            $params[':price_min'] = (float) $filters['price_min'];
        }

        if (isset($filters['price_max']) && $filters['price_max'] !== '') {
            $where .= ' AND l.price <= :price_max';
            $params[':price_max'] = (float) $filters['price_max'];
        }

        if (isset($filters['area_min']) && $filters['area_min'] !== '') {
            $where .= ' AND l.area >= :area_min';
            $params[':area_min'] = (float) $filters['area_min'];
        }

        if (isset($filters['area_max']) && $filters['area_max'] !== '') {
            $where .= ' AND l.area <= :area_max';
            $params[':area_max'] = (float) $filters['area_max'];
        }

        return [$where, $params];
    }
}
