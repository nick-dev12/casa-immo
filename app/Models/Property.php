<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Helpers\FormatHelper;

final class Property extends Model
{
    public const MIN_IMAGES = 4;
    public const MAX_IMAGES = 10;

    /** @return array<int, string> */
    public static function types(): array
    {
        return [
            'appartement' => 'Appartement',
            'studio' => 'Studio',
            'villa' => 'Villa',
            'maison' => 'Maison',
            'chambre' => 'Chambre',
            'residence' => 'Résidence',
            'autre' => 'Autre',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecommended(int $limit = 6): array
    {
        $sql = $this->baseSelectSql() . '
            GROUP BY p.id
            ORDER BY avg_rating DESC, review_count DESC, p.created_at DESC
            LIMIT :limit
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param array<int, int> $excludeIds
     * @return array<int, array<string, mixed>>
     */
    public function getNearby(string $city, int $limit = 6, array $excludeIds = []): array
    {
        $excludeClause = '';
        $params = [
            ':city' => $city,
            ':limit' => $limit,
        ];

        if ($excludeIds !== []) {
            $placeholders = [];
            foreach ($excludeIds as $i => $id) {
                $key = ':exclude' . $i;
                $placeholders[] = $key;
                $params[$key] = $id;
            }
            $excludeClause = ' AND p.id NOT IN (' . implode(',', $placeholders) . ')';
        }

        $sql = $this->baseSelectSql() . "
            AND p.city = :city{$excludeClause}
            GROUP BY p.id
            ORDER BY avg_rating DESC, review_count DESC, p.created_at DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(
                $key,
                $value,
                $key === ':limit' ? \PDO::PARAM_INT : \PDO::PARAM_STR
            );
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Logements par ville pour l'accueil (une rangée par destination).
     *
     * @param list<string>         $cities
     * @param array<int, int>      $excludeIds
     * @return list<array{city: string, properties: array<int, array<string, mixed>>}>
     */
    public function getPropertiesByCities(array $cities, int $perCity = 8, array $excludeIds = []): array
    {
        $cities = array_values(array_filter(array_map(static fn ($c) => trim((string) $c), $cities)));
        if ($cities === []) {
            return [];
        }

        if ($excludeIds !== []) {
            return $this->getPropertiesByCitiesSequential($cities, $perCity, $excludeIds);
        }

        $placeholders = [];
        $params = [':per_city' => $perCity];

        foreach ($cities as $i => $city) {
            $key = ':city' . $i;
            $placeholders[] = $key;
            $params[$key] = $city;
        }

        $orderParts = [];
        foreach ($cities as $i => $city) {
            $orderKey = ':order_city' . $i;
            $orderParts[] = $orderKey;
            $params[$orderKey] = $city;
        }

        $sql = '
            SELECT * FROM (
                SELECT ranked.*,
                    ROW_NUMBER() OVER (
                        PARTITION BY ranked.city
                        ORDER BY ranked.avg_rating DESC, ranked.review_count DESC, ranked.created_at DESC
                    ) AS city_rank
                FROM (
                    ' . trim($this->baseSelectSql()) . '
                    AND p.city IN (' . implode(', ', $placeholders) . ')
                    GROUP BY p.id
                ) ranked
            ) filtered
            WHERE filtered.city_rank <= :per_city
            ORDER BY FIELD(filtered.city, ' . implode(', ', $orderParts) . '), filtered.city_rank ASC
        ';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(
                $key,
                $value,
                $key === ':per_city' ? \PDO::PARAM_INT : \PDO::PARAM_STR
            );
        }
        $stmt->execute();

        $grouped = [];
        foreach ($stmt->fetchAll() as $property) {
            $city = (string) $property['city'];
            unset($property['city_rank']);
            $grouped[$city][] = $property;
        }

        $rows = [];
        foreach ($cities as $city) {
            if (empty($grouped[$city])) {
                continue;
            }

            $rows[] = [
                'city' => $city,
                'properties' => $grouped[$city],
            ];
        }

        return $rows;
    }

    /**
     * @param list<string>    $cities
     * @param array<int, int> $excludeIds
     * @return list<array{city: string, properties: array<int, array<string, mixed>>}>
     */
    private function getPropertiesByCitiesSequential(array $cities, int $perCity, array $excludeIds): array
    {
        $rows = [];
        $usedIds = $excludeIds;

        foreach ($cities as $city) {
            $properties = $this->getNearby($city, $perCity, $usedIds);
            if ($properties === []) {
                continue;
            }

            $rows[] = [
                'city' => $city,
                'properties' => $properties,
            ];

            foreach ($properties as $property) {
                $usedIds[] = (int) $property['id'];
            }
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function search(array $filters = [], int $limit = 24, int $offset = 0): array
    {
        [$where, $params] = $this->buildFilters($filters);

        $sql = $this->baseSelectSql() . $where . '
            GROUP BY p.id
            ORDER BY avg_rating DESC, p.created_at DESC
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
            SELECT COUNT(DISTINCT p.id)
            FROM properties p
            LEFT JOIN property_prices pp ON pp.property_id = p.id
            WHERE p.status = \'approved\' AND p.deleted_at IS NULL
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
        $sql = $this->baseSelectSql() . '
            AND p.id = :id
            GROUP BY p.id
            LIMIT 1
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAmenities(int $propertyId): array
    {
        $stmt = $this->db->prepare('
            SELECT a.id, a.name, a.icon, a.category
            FROM amenities a
            INNER JOIN property_amenities pa ON pa.amenity_id = a.id
            WHERE pa.property_id = :id
            ORDER BY a.category ASC, a.name ASC
        ');
        $stmt->execute([':id' => $propertyId]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getReviews(int $propertyId, int $limit = 10): array
    {
        $stmt = $this->db->prepare('
            SELECT r.id, r.rating, r.comment, r.created_at,
                   u.first_name, u.last_name
            FROM reviews r
            INNER JOIN users u ON u.id = r.user_id
            WHERE r.property_id = :id
            ORDER BY r.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':id', $propertyId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getOwner(int $ownerId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT id, first_name, last_name, email, phone, avatar
            FROM users
            WHERE id = :id AND deleted_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([':id' => $ownerId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return array<int, string>
     */
    public function getImages(int $propertyId): array
    {
        $stmt = $this->db->prepare('
            SELECT path, is_primary, sort_order
            FROM property_images
            WHERE property_id = :id
            ORDER BY is_primary DESC, sort_order ASC
        ');
        $stmt->execute([':id' => $propertyId]);

        return $stmt->fetchAll();
    }

    /**
     * Galerie normalisée : entre MIN_IMAGES et MAX_IMAGES photos.
     *
     * @return array<int, array{path: string, is_primary: int, sort_order: int}>
     */
    public function getGalleryImages(int $propertyId, string $type): array
    {
        return self::normalizeGalleryImages($this->getImages($propertyId), $type);
    }

    /**
     * @param array<int, array<string, mixed>> $images
     * @return array<int, array{path: string, is_primary: int, sort_order: int}>
     */
    public static function normalizeGalleryImages(array $images, string $type): array
    {
        $min = (int) config('app', 'property_images_min', self::MIN_IMAGES);
        $max = (int) config('app', 'property_images_max', self::MAX_IMAGES);
        $pool = FormatHelper::propertyGalleryPool($type);

        $normalized = [];
        $seen = [];

        foreach ($images as $index => $image) {
            if (count($normalized) >= $max) {
                break;
            }

            $path = (string) ($image['path'] ?? '');
            $resolved = FormatHelper::propertyImage($path !== '' ? $path : null, $type);

            if (isset($seen[$resolved])) {
                continue;
            }

            $seen[$resolved] = true;
            $normalized[] = [
                'path' => $path !== '' ? $path : $resolved,
                'is_primary' => (int) ($image['is_primary'] ?? ($index === 0 ? 1 : 0)),
                'sort_order' => (int) ($image['sort_order'] ?? $index),
            ];
        }

        foreach ($pool as $fallback) {
            if (count($normalized) >= $min) {
                break;
            }

            if (isset($seen[$fallback])) {
                continue;
            }

            $seen[$fallback] = true;
            $normalized[] = [
                'path' => $fallback,
                'is_primary' => count($normalized) === 0 ? 1 : 0,
                'sort_order' => count($normalized),
            ];
        }

        while (count($normalized) < $min) {
            $fallback = $pool[count($normalized) % count($pool)];
            if (!isset($seen[$fallback])) {
                $seen[$fallback] = true;
            }
            $normalized[] = [
                'path' => $fallback,
                'is_primary' => 0,
                'sort_order' => count($normalized),
            ];
        }

        if ($normalized !== [] && !array_filter(array_column($normalized, 'is_primary'))) {
            $normalized[0]['is_primary'] = 1;
        }

        return array_slice($normalized, 0, $max);
    }

    /**
     * @return array<int, string>
     */
    public function getCities(): array
    {
        $stmt = $this->db->query('
            SELECT DISTINCT city
            FROM properties
            WHERE status = \'approved\' AND deleted_at IS NULL
            ORDER BY city ASC
        ');

        return array_column($stmt->fetchAll(), 'city');
    }

    public function getDefaultCity(): string
    {
        $cities = $this->getCities();

        if (in_array('Ziguinchor', $cities, true)) {
            return 'Ziguinchor';
        }

        return $cities[0] ?? config('app', 'default_city', 'Ziguinchor');
    }

    /**
     * Quartiers issus des annonces publiées (prioritaires pour l'autocomplétion).
     *
     * @return array<int, array{name: string, city: string, subtitle: string, label: string, kind: string, listings: int}>
     */
    public function suggestDistricts(string $query, int $limit = 15, string $city = ''): array
    {
        $query = trim($query);
        $city = trim($city);

        if ($query === '' && $city === '') {
            return [];
        }

        $like = $query !== '' ? '%' . $query . '%' : '%';
        $startsWith = $query !== '' ? $query . '%' : '%';
        $cityFilter = $city !== '' ? ' AND city = :filter_city' : '';

        $stmt = $this->db->prepare('
            SELECT district, city, SUM(listing_count) AS listing_count
            FROM (
                SELECT p.district, p.city, COUNT(*) AS listing_count
                FROM properties p
                WHERE p.status = \'approved\'
                  AND p.deleted_at IS NULL
                  AND p.district IS NOT NULL
                  AND TRIM(p.district) != \'\'
                  AND p.district LIKE :q_district
                  ' . ($city !== '' ? 'AND p.city = :city_p' : '') . '
                GROUP BY p.district, p.city
                UNION ALL
                SELECT l.district, l.city, COUNT(*) AS listing_count
                FROM lands l
                WHERE l.status = \'approved\'
                  AND l.deleted_at IS NULL
                  AND l.district IS NOT NULL
                  AND TRIM(l.district) != \'\'
                  AND l.district LIKE :q_district2
                  ' . ($city !== '' ? 'AND l.city = :city_l' : '') . '
                GROUP BY l.district, l.city
            ) AS locations
            WHERE 1=1' . $cityFilter . '
            GROUP BY district, city
            ORDER BY
                CASE WHEN district LIKE :starts_with THEN 0 ELSE 1 END ASC,
                listing_count DESC,
                district ASC,
                city ASC
            LIMIT :limit
        ');
        $stmt->bindValue(':q_district', $like);
        $stmt->bindValue(':q_district2', $like);
        if ($city !== '') {
            $stmt->bindValue(':city_p', $city);
            $stmt->bindValue(':city_l', $city);
            $stmt->bindValue(':filter_city', $city);
        }
        $stmt->bindValue(':starts_with', $startsWith);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $district = trim((string) ($row['district'] ?? ''));
            $city = trim((string) ($row['city'] ?? ''));
            if ($district === '' || $city === '' || mb_strtolower($district) === mb_strtolower($city)) {
                continue;
            }

            if (!\App\Helpers\DestinationHelper::isKnownCity($city)) {
                continue;
            }

            $listings = max(1, (int) ($row['listing_count'] ?? 1));
            $key = mb_strtolower($district . '|' . $city);
            if (isset($results[$key])) {
                continue;
            }

            $subtitle = $city . ', Casamance';
            if ($listings > 1) {
                $subtitle .= ' · ' . $listings . ' annonces';
            }

            $results[$key] = [
                'name' => $district,
                'city' => $city,
                'subtitle' => $subtitle,
                'label' => $district . ', ' . $city,
                'kind' => 'district',
                'listings' => $listings,
            ];
        }

        return array_values($results);
    }

    /**
     * @return array{name: string, city: string, label: string}|null
     */
    public function findPublishedDistrict(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $stmt = $this->db->prepare('
            SELECT district, city, SUM(listing_count) AS listing_count
            FROM (
                SELECT p.district, p.city, COUNT(*) AS listing_count
                FROM properties p
                WHERE p.status = \'approved\'
                  AND p.deleted_at IS NULL
                  AND p.district IS NOT NULL
                  AND TRIM(p.district) != \'\'
                  AND LOWER(TRIM(p.district)) = LOWER(:exact)
                GROUP BY p.district, p.city
                UNION ALL
                SELECT l.district, l.city, COUNT(*) AS listing_count
                FROM lands l
                WHERE l.status = \'approved\'
                  AND l.deleted_at IS NULL
                  AND l.district IS NOT NULL
                  AND TRIM(l.district) != \'\'
                  AND LOWER(TRIM(l.district)) = LOWER(:exact2)
                GROUP BY l.district, l.city
            ) AS locations
            GROUP BY district, city
            ORDER BY listing_count DESC, city ASC
            LIMIT 1
        ');
        $stmt->execute([
            ':exact' => $query,
            ':exact2' => $query,
        ]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $district = trim((string) ($row['district'] ?? ''));
        $city = trim((string) ($row['city'] ?? ''));
        if ($district === '' || $city === '') {
            return null;
        }

        return [
            'name' => $district,
            'city' => $city,
            'label' => $district . ', ' . $city,
        ];
    }

    /**
     * @return array<int, array{name: string, city: string, subtitle: string, label: string}>
     */
    public function suggestLocations(string $query, int $limit = 8): array
    {
        return $this->suggestDistricts($query, $limit);
    }

    /**
     * @return array<int, array{name: string, city: string, subtitle: string, label: string, kind: string}>
     */
    public function suggestCities(string $query, int $limit = 6): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 2) {
            return [];
        }

        $like = '%' . $query . '%';
        $stmt = $this->db->prepare('
            SELECT city FROM (
                SELECT DISTINCT p.city
                FROM properties p
                WHERE p.status = \'approved\' AND p.deleted_at IS NULL AND p.city LIKE :q_city
                UNION
                SELECT DISTINCT l.city
                FROM lands l
                WHERE l.status = \'approved\' AND l.deleted_at IS NULL AND l.city LIKE :q_city2
            ) AS cities
            ORDER BY city ASC
            LIMIT :limit
        ');
        $stmt->bindValue(':q_city', $like);
        $stmt->bindValue(':q_city2', $like);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $city = trim((string) ($row['city'] ?? ''));
            if ($city === '') {
                continue;
            }
            $key = mb_strtolower($city);
            $results[$key] = [
                'name' => $city,
                'city' => $city,
                'subtitle' => 'Casamance, Sénégal',
                'label' => $city,
                'kind' => 'city',
            ];
        }

        return array_values($results);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function suggest(string $query, string $city = '', int $limit = 8): array
    {
        $sql = $this->baseSelectSql() . '
            AND (
                p.title LIKE :q_title OR p.city LIKE :q_city OR p.district LIKE :q_district
            )
        ';
        $like = '%' . $query . '%';
        $params = [
            ':q_title' => $like,
            ':q_city' => $like,
            ':q_district' => $like,
        ];

        if ($city !== '') {
            $sql .= ' AND p.city = :city';
            $params[':city'] = $city;
        }

        $sql .= ' GROUP BY p.id ORDER BY p.title ASC LIMIT :limit';

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
            SELECT p.*,
                pp.price_per_night,
                pp.price_per_week,
                pp.price_per_month,
                pp.currency AS price_currency,
                COALESCE(ROUND(AVG(r.rating), 1), 0) AS avg_rating,
                COUNT(DISTINCT r.id) AS review_count,
                (
                    SELECT pi.path
                    FROM property_images pi
                    WHERE pi.property_id = p.id
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC
                    LIMIT 1
                ) AS primary_image
            FROM properties p
            LEFT JOIN property_prices pp ON pp.property_id = p.id
            LEFT JOIN reviews r ON r.property_id = p.id
            WHERE p.status = \'approved\' AND p.deleted_at IS NULL
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
            $term = trim((string) $filters['q']);
            if ($term !== '') {
                $like = '%' . $term . '%';
                $where .= ' AND (p.title LIKE :q_title OR p.city LIKE :q_city OR p.district LIKE :q_district OR p.description LIKE :q_desc OR p.address LIKE :q_address)';
                $params[':q_title'] = $like;
                $params[':q_city'] = $like;
                $params[':q_district'] = $like;
                $params[':q_desc'] = $like;
                $params[':q_address'] = $like;
            }
        }

        if (!empty($filters['city'])) {
            $where .= ' AND p.city = :city';
            $params[':city'] = (string) $filters['city'];
        }

        if (!empty($filters['district'])) {
            $where .= ' AND p.district LIKE :district';
            $params[':district'] = '%' . (string) $filters['district'] . '%';
        }

        if (!empty($filters['type'])) {
            $where .= ' AND p.type = :type';
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['guests'])) {
            $where .= ' AND p.capacity >= :guests';
            $params[':guests'] = (int) $filters['guests'];
        }

        if (isset($filters['price_min']) && $filters['price_min'] !== '') {
            $where .= ' AND pp.price_per_night >= :price_min';
            $params[':price_min'] = (float) $filters['price_min'];
        }

        if (isset($filters['price_max']) && $filters['price_max'] !== '') {
            $where .= ' AND pp.price_per_night <= :price_max';
            $params[':price_max'] = (float) $filters['price_max'];
        }

        if (isset($filters['bedrooms']) && $filters['bedrooms'] !== '') {
            $where .= ' AND p.bedrooms >= :bedrooms';
            $params[':bedrooms'] = (int) $filters['bedrooms'];
        }

        $this->appendAvailabilityFilter($filters, $where, $params);

        return [$where, $params];
    }

    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $params
     */
    private function appendAvailabilityFilter(array $filters, string &$where, array &$params): void
    {
        $checkIn = trim((string) ($filters['check_in'] ?? ''));
        $checkOut = trim((string) ($filters['check_out'] ?? ''));

        if ($checkIn === '' || $checkOut === '') {
            return;
        }

        $checkInTs = strtotime($checkIn);
        $checkOutTs = strtotime($checkOut);
        if ($checkInTs === false || $checkOutTs === false || $checkOutTs <= $checkInTs) {
            return;
        }

        $where .= '
            AND NOT EXISTS (
                SELECT 1
                FROM bookings b
                WHERE b.property_id = p.id
                  AND b.status IN (\'pending\', \'confirmed\')
                  AND b.check_in < :avail_check_out
                  AND b.check_out > :avail_check_in
            )
            AND NOT EXISTS (
                SELECT 1
                FROM property_availability pa
                WHERE pa.property_id = p.id
                  AND pa.date >= :avail_check_in_pa
                  AND pa.date < :avail_check_out_pa
                  AND pa.status IN (\'blocked\', \'reserved\', \'maintenance\')
            )
        ';

        $params[':avail_check_in'] = $checkIn;
        $params[':avail_check_out'] = $checkOut;
        $params[':avail_check_in_pa'] = $checkIn;
        $params[':avail_check_out_pa'] = $checkOut;
    }
}
