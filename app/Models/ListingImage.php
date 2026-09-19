<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ListingImage extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function forProperty(int $propertyId): array
    {
        $stmt = $this->db->prepare('
            SELECT id, path, is_primary, sort_order
            FROM property_images
            WHERE property_id = :property_id
            ORDER BY is_primary DESC, sort_order ASC, id ASC
        ');
        $stmt->execute([':property_id' => $propertyId]);

        return $stmt->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forLand(int $landId): array
    {
        $stmt = $this->db->prepare('
            SELECT id, path, is_primary, sort_order
            FROM land_images
            WHERE land_id = :land_id
            ORDER BY is_primary DESC, sort_order ASC, id ASC
        ');
        $stmt->execute([':land_id' => $landId]);

        return $stmt->fetchAll();
    }

    public function countForProperty(int $propertyId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM property_images WHERE property_id = :property_id');
        $stmt->execute([':property_id' => $propertyId]);

        return (int) $stmt->fetchColumn();
    }

    public function countForLand(int $landId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM land_images WHERE land_id = :land_id');
        $stmt->execute([':land_id' => $landId]);

        return (int) $stmt->fetchColumn();
    }

    public function addProperty(int $propertyId, string $path, int $sortOrder, bool $isPrimary = false): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO property_images (property_id, path, is_primary, sort_order)
            VALUES (:property_id, :path, :is_primary, :sort_order)
        ');
        $stmt->execute([
            ':property_id' => $propertyId,
            ':path' => $path,
            ':is_primary' => $isPrimary ? 1 : 0,
            ':sort_order' => $sortOrder,
        ]);
    }

    public function addLand(int $landId, string $path, int $sortOrder, bool $isPrimary = false): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO land_images (land_id, path, is_primary, sort_order)
            VALUES (:land_id, :path, :is_primary, :sort_order)
        ');
        $stmt->execute([
            ':land_id' => $landId,
            ':path' => $path,
            ':is_primary' => $isPrimary ? 1 : 0,
            ':sort_order' => $sortOrder,
        ]);
    }

    public function deleteProperty(int $imageId, int $propertyId): ?string
    {
        $stmt = $this->db->prepare('
            SELECT path FROM property_images
            WHERE id = :id AND property_id = :property_id
            LIMIT 1
        ');
        $stmt->execute([
            ':id' => $imageId,
            ':property_id' => $propertyId,
        ]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $delete = $this->db->prepare('DELETE FROM property_images WHERE id = :id AND property_id = :property_id');
        $delete->execute([
            ':id' => $imageId,
            ':property_id' => $propertyId,
        ]);

        return (string) $row['path'];
    }

    public function deleteLand(int $imageId, int $landId): ?string
    {
        $stmt = $this->db->prepare('
            SELECT path FROM land_images
            WHERE id = :id AND land_id = :land_id
            LIMIT 1
        ');
        $stmt->execute([
            ':id' => $imageId,
            ':land_id' => $landId,
        ]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $delete = $this->db->prepare('DELETE FROM land_images WHERE id = :id AND land_id = :land_id');
        $delete->execute([
            ':id' => $imageId,
            ':land_id' => $landId,
        ]);

        return (string) $row['path'];
    }

    public function ensurePrimaryProperty(int $propertyId): void
    {
        $stmt = $this->db->prepare('
            SELECT id FROM property_images
            WHERE property_id = :property_id
            ORDER BY is_primary DESC, sort_order ASC, id ASC
        ');
        $stmt->execute([':property_id' => $propertyId]);
        $rows = $stmt->fetchAll();
        if ($rows === []) {
            return;
        }

        $this->db->prepare('UPDATE property_images SET is_primary = 0 WHERE property_id = :property_id')
            ->execute([':property_id' => $propertyId]);
        $this->db->prepare('UPDATE property_images SET is_primary = 1 WHERE id = :id')
            ->execute([':id' => (int) $rows[0]['id']]);
    }

    public function ensurePrimaryLand(int $landId): void
    {
        $stmt = $this->db->prepare('
            SELECT id FROM land_images
            WHERE land_id = :land_id
            ORDER BY is_primary DESC, sort_order ASC, id ASC
        ');
        $stmt->execute([':land_id' => $landId]);
        $rows = $stmt->fetchAll();
        if ($rows === []) {
            return;
        }

        $this->db->prepare('UPDATE land_images SET is_primary = 0 WHERE land_id = :land_id')
            ->execute([':land_id' => $landId]);
        $this->db->prepare('UPDATE land_images SET is_primary = 1 WHERE id = :id')
            ->execute([':id' => (int) $rows[0]['id']]);
    }
}
