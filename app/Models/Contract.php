<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Contract extends Model
{
    /** @var list<string> */
    public const TYPES = ['habitation', 'commercial', 'saisonnier', 'autre'];

    /** @var list<string> */
    public const DURATIONS = ['court', 'moyen', 'long'];

    /** @var list<int> */
    public const DEPOSIT_MONTHS = [2, 3];

    /** @var list<string> */
    public const STATUSES = ['draft', 'active', 'terminated', 'expired'];

    /**
     * @return list<array<string, mixed>>
     */
    public function allForAdmin(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->query('
            SELECT c.id, c.start_date, c.end_date, c.monthly_amount, c.deposit_amount,
                   c.status, c.contract_type, c.pdf_path, c.tenant_photo_path,
                   (
                       SELECT ci.path
                       FROM contract_images ci
                       WHERE ci.contract_id = c.id
                       ORDER BY ci.is_primary DESC, ci.sort_order ASC
                       LIMIT 1
                   ) AS cover_image,
                   p.title AS property_title, p.city AS property_city,
                   tenant.first_name AS tenant_first_name,
                   tenant.last_name AS tenant_last_name,
                   tenant.email AS tenant_email,
                   tenant.phone AS tenant_phone
            FROM contracts c
            INNER JOIN properties p ON p.id = c.property_id
            INNER JOIN users tenant ON tenant.id = c.tenant_id
            ORDER BY c.created_at DESC
            LIMIT ' . $limit . '
        ');

        return $stmt->fetchAll() ?: [];
    }

    public function markExpiredContracts(): void
    {
        $this->db->exec('
            UPDATE contracts
            SET status = \'expired\', updated_at = NOW()
            WHERE status = \'active\'
              AND end_date < CURDATE()
        ');
    }

    /**
     * @return array{total: int, active: int, expired: int, inactive: int}
     */
    public function adminStats(): array
    {
        $this->markExpiredContracts();
        $row = $this->db->query('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'active\' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = \'expired\' THEN 1 ELSE 0 END) AS expired,
                SUM(CASE WHEN status IN (\'terminated\', \'draft\') THEN 1 ELSE 0 END) AS inactive
            FROM contracts
        ')->fetch();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'active' => (int) ($row['active'] ?? 0),
            'expired' => (int) ($row['expired'] ?? 0),
            'inactive' => (int) ($row['inactive'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForAdmin(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT c.*,
                   p.title AS property_title, p.city AS property_city, p.type AS property_type,
                   (
                       SELECT pi.path
                       FROM property_images pi
                       WHERE pi.property_id = p.id
                       ORDER BY pi.is_primary DESC, pi.sort_order ASC
                       LIMIT 1
                   ) AS property_image,
                   tenant.first_name AS tenant_first_name,
                   tenant.last_name AS tenant_last_name,
                   tenant.email AS tenant_email,
                   tenant.phone AS tenant_phone,
                   owner.first_name AS owner_first_name,
                   owner.last_name AS owner_last_name
            FROM contracts c
            INNER JOIN properties p ON p.id = c.property_id
            INNER JOIN users tenant ON tenant.id = c.tenant_id
            INNER JOIN users owner ON owner.id = c.owner_id
            WHERE c.id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }

        $stmt = $this->db->prepare('
            UPDATE contracts SET status = :status, updated_at = NOW() WHERE id = :id
        ');
        $stmt->execute([':id' => $id, ':status' => $status]);

        return $stmt->rowCount() > 0;
    }

    public function deleteContract(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM contracts WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function linkableProperties(): array
    {
        $stmt = $this->db->query('
            SELECT p.id, p.title, p.city, p.type, p.owner_id,
                   u.first_name AS owner_first_name, u.last_name AS owner_last_name
            FROM properties p
            INNER JOIN users u ON u.id = p.owner_id
            WHERE p.deleted_at IS NULL
            ORDER BY p.title ASC
        ');

        return $stmt->fetchAll() ?: [];
    }

  /**
     * @return array<string, mixed>|null
     */
    public function findProperty(int $propertyId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT id, owner_id, title, city
            FROM properties
            WHERE id = :id AND deleted_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([':id' => $propertyId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO contracts (
                property_id, owner_id, tenant_id, start_date, end_date,
                monthly_amount, deposit_amount, deposit_months, terms, contract_type,
                rental_duration, status, pdf_path, tenant_photo_path
            ) VALUES (
                :property_id, :owner_id, :tenant_id, :start_date, :end_date,
                :monthly_amount, :deposit_amount, :deposit_months, :terms, :contract_type,
                :rental_duration, :status, :pdf_path, :tenant_photo_path
            )
        ');
        $stmt->execute([
            ':property_id' => (int) $data['property_id'],
            ':owner_id' => (int) $data['owner_id'],
            ':tenant_id' => (int) $data['tenant_id'],
            ':start_date' => (string) $data['start_date'],
            ':end_date' => (string) $data['end_date'],
            ':monthly_amount' => (float) $data['monthly_amount'],
            ':deposit_amount' => (float) ($data['deposit_amount'] ?? 0),
            ':deposit_months' => (int) ($data['deposit_months'] ?? 2),
            ':terms' => $data['terms'] ?? null,
            ':contract_type' => (string) ($data['contract_type'] ?? 'habitation'),
            ':rental_duration' => (string) ($data['rental_duration'] ?? 'long'),
            ':status' => (string) ($data['status'] ?? 'active'),
            ':pdf_path' => (string) $data['pdf_path'],
            ':tenant_photo_path' => $data['tenant_photo_path'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function finalize(int $contractId, string $pdfPath, ?string $photoPath = null): void
    {
        $stmt = $this->db->prepare('
            UPDATE contracts
            SET pdf_path = :pdf_path,
                tenant_photo_path = :tenant_photo_path,
                status = \'active\',
                updated_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            ':id' => $contractId,
            ':pdf_path' => $pdfPath,
            ':tenant_photo_path' => $photoPath,
        ]);
    }

    public function updateDocuments(int $contractId, string $pdfPath, ?string $photoPath = null): void
    {
        $stmt = $this->db->prepare('
            UPDATE contracts
            SET pdf_path = :pdf_path,
                tenant_photo_path = COALESCE(:tenant_photo_path, tenant_photo_path),
                updated_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            ':id' => $contractId,
            ':pdf_path' => $pdfPath,
            ':tenant_photo_path' => $photoPath,
        ]);
    }
}
