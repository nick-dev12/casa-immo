<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class HostLand extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function forOwner(int $sellerId): array
    {
        $stmt = $this->db->prepare('
            SELECT
                l.*,
                (
                    SELECT li.path
                    FROM land_images li
                    WHERE li.land_id = l.id
                    ORDER BY li.is_primary DESC, li.sort_order ASC
                    LIMIT 1
                ) AS primary_image
            FROM lands l
            WHERE l.seller_id = :seller_id
              AND l.deleted_at IS NULL
            ORDER BY l.updated_at DESC, l.created_at DESC
        ');
        $stmt->execute([':seller_id' => $sellerId]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findOwned(int $sellerId, int $landId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT l.*
            FROM lands l
            WHERE l.id = :id
              AND l.seller_id = :seller_id
              AND l.deleted_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([
            ':id' => $landId,
            ':seller_id' => $sellerId,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(int $sellerId, array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO lands (
                seller_id, title, description, area, area_unit, width_m, length_m, price, currency,
                city, district, address, latitude, longitude, land_type, paper_type, status
            ) VALUES (
                :seller_id, :title, :description, :area, :area_unit, :width_m, :length_m, :price, :currency,
                :city, :district, :address, :latitude, :longitude, :land_type, :paper_type, :status
            )
        ');
        $stmt->execute([
            ':seller_id' => $sellerId,
            ':title' => (string) $data['title'],
            ':description' => (string) ($data['description'] ?? ''),
            ':area' => (float) $data['area'],
            ':area_unit' => (string) ($data['area_unit'] ?? 'm2'),
            ':width_m' => $data['width_m'] !== null ? (float) $data['width_m'] : null,
            ':length_m' => $data['length_m'] !== null ? (float) $data['length_m'] : null,
            ':price' => (float) $data['price'],
            ':currency' => (string) ($data['currency'] ?? 'XOF'),
            ':city' => (string) $data['city'],
            ':district' => (string) ($data['district'] ?? ''),
            ':address' => (string) ($data['address'] ?? ''),
            ':latitude' => $data['latitude'] !== null ? (float) $data['latitude'] : null,
            ':longitude' => $data['longitude'] !== null ? (float) $data['longitude'] : null,
            ':land_type' => (string) ($data['land_type'] ?? 'residentiel'),
            ':paper_type' => !empty($data['paper_type']) ? (string) $data['paper_type'] : null,
            ':status' => (string) ($data['status'] ?? 'draft'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $landId, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE lands
            SET title = :title,
                description = :description,
                area = :area,
                area_unit = :area_unit,
                width_m = :width_m,
                length_m = :length_m,
                price = :price,
                currency = :currency,
                city = :city,
                district = :district,
                address = :address,
                latitude = :latitude,
                longitude = :longitude,
                land_type = :land_type,
                paper_type = :paper_type,
                updated_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            ':id' => $landId,
            ':title' => (string) $data['title'],
            ':description' => (string) ($data['description'] ?? ''),
            ':area' => (float) $data['area'],
            ':area_unit' => (string) ($data['area_unit'] ?? 'm2'),
            ':width_m' => $data['width_m'] !== null ? (float) $data['width_m'] : null,
            ':length_m' => $data['length_m'] !== null ? (float) $data['length_m'] : null,
            ':price' => (float) $data['price'],
            ':currency' => (string) ($data['currency'] ?? 'XOF'),
            ':city' => (string) $data['city'],
            ':district' => (string) ($data['district'] ?? ''),
            ':address' => (string) ($data['address'] ?? ''),
            ':latitude' => $data['latitude'] !== null ? (float) $data['latitude'] : null,
            ':longitude' => $data['longitude'] !== null ? (float) $data['longitude'] : null,
            ':land_type' => (string) ($data['land_type'] ?? 'residentiel'),
            ':paper_type' => !empty($data['paper_type']) ? (string) $data['paper_type'] : null,
        ]);
    }

    public function updateStatus(int $landId, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE lands SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':id' => $landId,
            ':status' => $status,
        ]);
    }

    public function softDelete(int $sellerId, int $landId): bool
    {
        $stmt = $this->db->prepare('
            UPDATE lands
            SET deleted_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
              AND seller_id = :seller_id
              AND deleted_at IS NULL
        ');
        $stmt->execute([
            ':id' => $landId,
            ':seller_id' => $sellerId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
