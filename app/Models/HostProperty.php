<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class HostProperty extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function forOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare('
            SELECT
                p.*,
                pp.price_per_night,
                pp.currency AS price_currency,
                (
                    SELECT pi.path
                    FROM property_images pi
                    WHERE pi.property_id = p.id
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC
                    LIMIT 1
                ) AS primary_image
            FROM properties p
            LEFT JOIN property_prices pp ON pp.property_id = p.id
            WHERE p.owner_id = :owner_id
              AND p.deleted_at IS NULL
            ORDER BY p.updated_at DESC, p.created_at DESC
        ');
        $stmt->execute([':owner_id' => $ownerId]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findOwned(int $ownerId, int $propertyId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT
                p.*,
                pp.price_per_night,
                pp.price_per_week,
                pp.price_per_month,
                pp.currency AS price_currency
            FROM properties p
            LEFT JOIN property_prices pp ON pp.property_id = p.id
            WHERE p.id = :id
              AND p.owner_id = :owner_id
              AND p.deleted_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([
            ':id' => $propertyId,
            ':owner_id' => $ownerId,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(int $ownerId, ?int $establishmentId, array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO properties (
                owner_id, establishment_id, title, description, type, address, city, district,
                country, latitude, longitude, area_sqm, bedrooms, bathrooms, capacity, rules, status, currency
            ) VALUES (
                :owner_id, :establishment_id, :title, :description, :type, :address, :city, :district,
                :country, :latitude, :longitude, :area_sqm, :bedrooms, :bathrooms, :capacity, :rules, :status, :currency
            )
        ');
        $stmt->execute([
            ':owner_id' => $ownerId,
            ':establishment_id' => $establishmentId,
            ':title' => (string) $data['title'],
            ':description' => (string) $data['description'],
            ':type' => (string) $data['type'],
            ':address' => (string) $data['address'],
            ':city' => (string) $data['city'],
            ':district' => (string) ($data['district'] ?? ''),
            ':country' => (string) ($data['country'] ?? 'Sénégal'),
            ':latitude' => $data['latitude'] !== null ? (float) $data['latitude'] : null,
            ':longitude' => $data['longitude'] !== null ? (float) $data['longitude'] : null,
            ':area_sqm' => $data['area_sqm'] !== null ? (float) $data['area_sqm'] : null,
            ':bedrooms' => (int) $data['bedrooms'],
            ':bathrooms' => (int) $data['bathrooms'],
            ':capacity' => (int) $data['capacity'],
            ':rules' => (string) ($data['rules'] ?? ''),
            ':status' => (string) ($data['status'] ?? 'draft'),
            ':currency' => (string) ($data['currency'] ?? 'XOF'),
        ]);

        $propertyId = (int) $this->db->lastInsertId();

        $priceStmt = $this->db->prepare('
            INSERT INTO property_prices (property_id, price_per_night, price_per_week, price_per_month, currency)
            VALUES (:property_id, :price_per_night, :price_per_week, :price_per_month, :currency)
        ');
        $priceStmt->execute([
            ':property_id' => $propertyId,
            ':price_per_night' => $data['price_per_night'] !== null ? (float) $data['price_per_night'] : null,
            ':price_per_week' => $data['price_per_week'] !== null ? (float) $data['price_per_week'] : null,
            ':price_per_month' => $data['price_per_month'] !== null ? (float) $data['price_per_month'] : null,
            ':currency' => (string) ($data['currency'] ?? 'XOF'),
        ]);

        return $propertyId;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $propertyId, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE properties
            SET title = :title,
                description = :description,
                type = :type,
                address = :address,
                city = :city,
                district = :district,
                latitude = :latitude,
                longitude = :longitude,
                area_sqm = :area_sqm,
                bedrooms = :bedrooms,
                bathrooms = :bathrooms,
                capacity = :capacity,
                rules = :rules,
                updated_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            ':id' => $propertyId,
            ':title' => (string) $data['title'],
            ':description' => (string) $data['description'],
            ':type' => (string) $data['type'],
            ':address' => (string) $data['address'],
            ':city' => (string) $data['city'],
            ':district' => (string) ($data['district'] ?? ''),
            ':latitude' => $data['latitude'] !== null ? (float) $data['latitude'] : null,
            ':longitude' => $data['longitude'] !== null ? (float) $data['longitude'] : null,
            ':area_sqm' => $data['area_sqm'] !== null ? (float) $data['area_sqm'] : null,
            ':bedrooms' => (int) $data['bedrooms'],
            ':bathrooms' => (int) $data['bathrooms'],
            ':capacity' => (int) $data['capacity'],
            ':rules' => (string) ($data['rules'] ?? ''),
        ]);

        $priceStmt = $this->db->prepare('
            UPDATE property_prices
            SET price_per_night = :price_per_night,
                price_per_week = :price_per_week,
                price_per_month = :price_per_month,
                currency = :currency,
                updated_at = NOW()
            WHERE property_id = :property_id
        ');
        $priceStmt->execute([
            ':property_id' => $propertyId,
            ':price_per_night' => $data['price_per_night'] !== null ? (float) $data['price_per_night'] : null,
            ':price_per_week' => $data['price_per_week'] !== null ? (float) $data['price_per_week'] : null,
            ':price_per_month' => $data['price_per_month'] !== null ? (float) $data['price_per_month'] : null,
            ':currency' => (string) ($data['currency'] ?? 'XOF'),
        ]);
    }

    public function updateStatus(int $propertyId, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE properties SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':id' => $propertyId,
            ':status' => $status,
        ]);
    }

    public function videoPath(int $propertyId): ?string
    {
        $stmt = $this->db->prepare('SELECT video_path FROM properties WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $propertyId]);
        $path = $stmt->fetchColumn();

        return is_string($path) && $path !== '' ? $path : null;
    }

    public function setVideoPath(int $propertyId, ?string $path): void
    {
        $stmt = $this->db->prepare('UPDATE properties SET video_path = :video_path, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':id' => $propertyId,
            ':video_path' => $path !== null && $path !== '' ? $path : null,
        ]);
    }

    public function hasActiveBookings(int $propertyId): bool
    {
        $stmt = $this->db->prepare('
            SELECT 1
            FROM bookings
            WHERE property_id = :property_id
              AND status IN (\'pending\', \'confirmed\')
              AND check_out >= CURDATE()
            LIMIT 1
        ');
        $stmt->execute([':property_id' => $propertyId]);

        return (bool) $stmt->fetchColumn();
    }

    public function softDelete(int $ownerId, int $propertyId): bool
    {
        if ($this->hasActiveBookings($propertyId)) {
            return false;
        }

        $stmt = $this->db->prepare('
            UPDATE properties
            SET deleted_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
              AND owner_id = :owner_id
              AND deleted_at IS NULL
        ');
        $stmt->execute([
            ':id' => $propertyId,
            ':owner_id' => $ownerId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
