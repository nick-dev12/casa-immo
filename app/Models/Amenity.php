<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Amenity extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        $stmt = $this->db->query('
            SELECT id, name, icon, category
            FROM amenities
            ORDER BY category ASC, name ASC
        ');

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function groupedByCategory(): array
    {
        $grouped = [];
        foreach ($this->all() as $amenity) {
            $category = (string) $amenity['category'];
            $grouped[$category][] = $amenity;
        }

        return $grouped;
    }

    /**
     * @return list<int>
     */
    public function idsForProperty(int $propertyId): array
    {
        $stmt = $this->db->prepare('
            SELECT amenity_id
            FROM property_amenities
            WHERE property_id = :property_id
        ');
        $stmt->execute([':property_id' => $propertyId]);

        return array_map(static fn (array $row): int => (int) $row['amenity_id'], $stmt->fetchAll());
    }

    /**
     * @param list<int> $amenityIds
     */
    public function syncForProperty(int $propertyId, array $amenityIds): void
    {
        $delete = $this->db->prepare('DELETE FROM property_amenities WHERE property_id = :property_id');
        $delete->execute([':property_id' => $propertyId]);

        if ($amenityIds === []) {
            return;
        }

        $insert = $this->db->prepare('
            INSERT INTO property_amenities (property_id, amenity_id)
            VALUES (:property_id, :amenity_id)
        ');

        foreach ($amenityIds as $amenityId) {
            $insert->execute([
                ':property_id' => $propertyId,
                ':amenity_id' => $amenityId,
            ]);
        }
    }
}
