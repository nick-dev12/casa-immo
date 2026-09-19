<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ConstructionProject extends Model
{
    /** @var list<string> */
    public const TYPES = ['maison_fini', 'maison_finition', 'villa', 'immeuble', 'autre'];

    /** @var list<string> */
    public const STATUSES = ['devis', 'planning', 'chantier', 'livraison', 'termine', 'annule'];

    /**
     * @return array<string, int>
     */
    public function statsForOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'devis\' THEN 1 ELSE 0 END) AS devis,
                SUM(CASE WHEN status IN (\'planning\', \'chantier\') THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = \'livraison\' THEN 1 ELSE 0 END) AS delivery,
                SUM(CASE WHEN status = \'termine\' THEN 1 ELSE 0 END) AS completed
            FROM construction_projects
            WHERE owner_id = :owner_id
        ');
        $stmt->execute([':owner_id' => $ownerId]);
        $row = $stmt->fetch();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'devis' => (int) ($row['devis'] ?? 0),
            'active' => (int) ($row['active'] ?? 0),
            'delivery' => (int) ($row['delivery'] ?? 0),
            'completed' => (int) ($row['completed'] ?? 0),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare('
            SELECT cp.*, p.title AS property_title
            FROM construction_projects cp
            LEFT JOIN properties p ON p.id = cp.property_id
            WHERE cp.owner_id = :owner_id
            ORDER BY
                CASE cp.status
                    WHEN \'livraison\' THEN 0
                    WHEN \'chantier\' THEN 1
                    WHEN \'planning\' THEN 2
                    WHEN \'devis\' THEN 3
                    WHEN \'termine\' THEN 4
                    ELSE 5
                END,
                cp.delivery_date ASC,
                cp.created_at DESC
        ');
        $stmt->execute([':owner_id' => $ownerId]);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForOwner(int $id, int $ownerId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT cp.*, p.title AS property_title
            FROM construction_projects cp
            LEFT JOIN properties p ON p.id = cp.property_id
            WHERE cp.id = :id AND cp.owner_id = :owner_id
            LIMIT 1
        ');
        $stmt->execute([':id' => $id, ':owner_id' => $ownerId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function linkableProperties(int $ownerId): array
    {
        $stmt = $this->db->prepare('
            SELECT id, title, city, type, status
            FROM properties
            WHERE owner_id = :owner_id
              AND deleted_at IS NULL
              AND type IN (\'maison\', \'villa\', \'residence\', \'appartement\')
            ORDER BY title ASC
        ');
        $stmt->execute([':owner_id' => $ownerId]);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(int $ownerId, ?int $establishmentId, array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO construction_projects (
                owner_id, establishment_id, property_id, title, description, project_type, status,
                city, district, address, quote_amount, currency, start_date, delivery_date,
                progress, client_name, client_phone, notes
            ) VALUES (
                :owner_id, :establishment_id, :property_id, :title, :description, :project_type, :status,
                :city, :district, :address, :quote_amount, :currency, :start_date, :delivery_date,
                :progress, :client_name, :client_phone, :notes
            )
        ');
        $stmt->execute([
            ':owner_id' => $ownerId,
            ':establishment_id' => $establishmentId,
            ':property_id' => $data['property_id'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':project_type' => $data['project_type'],
            ':status' => $data['status'],
            ':city' => $data['city'],
            ':district' => $data['district'],
            ':address' => $data['address'],
            ':quote_amount' => $data['quote_amount'],
            ':currency' => $data['currency'],
            ':start_date' => $data['start_date'],
            ':delivery_date' => $data['delivery_date'],
            ':progress' => $data['progress'],
            ':client_name' => $data['client_name'],
            ':client_phone' => $data['client_phone'],
            ':notes' => $data['notes'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, int $ownerId, array $data): bool
    {
        $stmt = $this->db->prepare('
            UPDATE construction_projects SET
                property_id = :property_id,
                title = :title,
                description = :description,
                project_type = :project_type,
                status = :status,
                city = :city,
                district = :district,
                address = :address,
                quote_amount = :quote_amount,
                currency = :currency,
                start_date = :start_date,
                delivery_date = :delivery_date,
                progress = :progress,
                client_name = :client_name,
                client_phone = :client_phone,
                notes = :notes
            WHERE id = :id AND owner_id = :owner_id
        ');

        return $stmt->execute([
            ':property_id' => $data['property_id'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':project_type' => $data['project_type'],
            ':status' => $data['status'],
            ':city' => $data['city'],
            ':district' => $data['district'],
            ':address' => $data['address'],
            ':quote_amount' => $data['quote_amount'],
            ':currency' => $data['currency'],
            ':start_date' => $data['start_date'],
            ':delivery_date' => $data['delivery_date'],
            ':progress' => $data['progress'],
            ':client_name' => $data['client_name'],
            ':client_phone' => $data['client_phone'],
            ':notes' => $data['notes'],
            ':id' => $id,
            ':owner_id' => $ownerId,
        ]);
    }

    public function delete(int $id, int $ownerId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM construction_projects WHERE id = :id AND owner_id = :owner_id');

        return $stmt->execute([':id' => $id, ':owner_id' => $ownerId]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function milestones(int $projectId): array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM construction_milestones
            WHERE project_id = :project_id
            ORDER BY sort_order ASC, due_date ASC, id ASC
        ');
        $stmt->execute([':project_id' => $projectId]);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @param list<array{title: string, due_date: ?string, status: string}> $milestones
     */
    public function syncMilestones(int $projectId, array $milestones): void
    {
        $this->db->prepare('DELETE FROM construction_milestones WHERE project_id = :project_id')
            ->execute([':project_id' => $projectId]);

        $stmt = $this->db->prepare('
            INSERT INTO construction_milestones (project_id, title, due_date, status, sort_order, completed_at)
            VALUES (:project_id, :title, :due_date, :status, :sort_order, :completed_at)
        ');

        foreach ($milestones as $index => $milestone) {
            $status = $milestone['status'];
            $completedAt = $status === 'done' ? ($milestone['due_date'] ?? date('Y-m-d')) : null;
            $stmt->execute([
                ':project_id' => $projectId,
                ':title' => $milestone['title'],
                ':due_date' => $milestone['due_date'],
                ':status' => $status,
                ':sort_order' => $index,
                ':completed_at' => $completedAt,
            ]);
        }
    }
}
