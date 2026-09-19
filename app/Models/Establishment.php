<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Establishment extends Model
{
    /**
     * @return array<string, mixed>|null
     */
    public function findByOwnerId(int $ownerId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT *
            FROM establishments
            WHERE owner_id = :owner_id
            LIMIT 1
        ');
        $stmt->execute([':owner_id' => $ownerId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM establishments WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO establishments (
                owner_id, name, description, city, district, address, latitude, longitude, phone, status
            ) VALUES (
                :owner_id, :name, :description, :city, :district, :address, :latitude, :longitude, :phone, :status
            )
        ');
        $stmt->execute([
            ':owner_id' => (int) $data['owner_id'],
            ':name' => (string) $data['name'],
            ':description' => (string) ($data['description'] ?? ''),
            ':city' => (string) $data['city'],
            ':district' => (string) ($data['district'] ?? ''),
            ':address' => (string) ($data['address'] ?? ''),
            ':latitude' => isset($data['latitude']) && $data['latitude'] !== null && $data['latitude'] !== ''
                ? (float) $data['latitude']
                : null,
            ':longitude' => isset($data['longitude']) && $data['longitude'] !== null && $data['longitude'] !== ''
                ? (float) $data['longitude']
                : null,
            ':phone' => (string) ($data['phone'] ?? ''),
            ':status' => (string) ($data['status'] ?? 'active'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE establishments
            SET name = :name,
                description = :description,
                city = :city,
                district = :district,
                address = :address,
                phone = :phone,
                updated_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            ':id' => $id,
            ':name' => (string) $data['name'],
            ':description' => (string) ($data['description'] ?? ''),
            ':city' => (string) $data['city'],
            ':district' => (string) ($data['district'] ?? ''),
            ':address' => (string) ($data['address'] ?? ''),
            ':phone' => (string) ($data['phone'] ?? ''),
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['active', 'suspended', 'draft'], true)) {
            return false;
        }

        $stmt = $this->db->prepare('
            UPDATE establishments SET status = :status, updated_at = NOW() WHERE id = :id
        ');
        $stmt->execute([':id' => $id, ':status' => $status]);

        return $stmt->rowCount() > 0;
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM establishments WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
