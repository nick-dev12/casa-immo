<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Model;

final class AdminReportService extends Model
{
    /**
     * @return array{total: int, pending: int, reviewed: int, resolved: int, dismissed: int}
     */
    public function stats(): array
    {
        $row = $this->db->query('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = \'reviewed\' THEN 1 ELSE 0 END) AS reviewed,
                SUM(CASE WHEN status = \'resolved\' THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN status = \'dismissed\' THEN 1 ELSE 0 END) AS dismissed
            FROM reports
        ')->fetch();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'pending' => (int) ($row['pending'] ?? 0),
            'reviewed' => (int) ($row['reviewed'] ?? 0),
            'resolved' => (int) ($row['resolved'] ?? 0),
            'dismissed' => (int) ($row['dismissed'] ?? 0),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allForAdmin(int $limit = 200): array
    {
        $limit = max(1, min($limit, 300));
        $stmt = $this->db->query('
            SELECT r.*,
                   u.first_name, u.last_name, u.email AS reporter_email,
                   CASE r.reportable_type
                       WHEN \'property\' THEN (
                           SELECT p.title FROM properties p WHERE p.id = r.reportable_id LIMIT 1
                       )
                       WHEN \'land\' THEN (
                           SELECT l.title FROM lands l WHERE l.id = r.reportable_id LIMIT 1
                       )
                       ELSE NULL
                   END AS listing_title
            FROM reports r
            INNER JOIN users u ON u.id = r.reporter_id
            ORDER BY
                CASE r.status WHEN \'pending\' THEN 0 WHEN \'reviewed\' THEN 1 ELSE 2 END,
                r.created_at DESC
            LIMIT ' . $limit . '
        ');

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForAdmin(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT r.*,
                   u.first_name, u.last_name, u.email AS reporter_email, u.phone AS reporter_phone,
                   CASE r.reportable_type
                       WHEN \'property\' THEN (
                           SELECT p.title FROM properties p WHERE p.id = r.reportable_id LIMIT 1
                       )
                       WHEN \'land\' THEN (
                           SELECT l.title FROM lands l WHERE l.id = r.reportable_id LIMIT 1
                       )
                       ELSE NULL
                   END AS listing_title
            FROM reports r
            INNER JOIN users u ON u.id = r.reporter_id
            WHERE r.id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function listingAdminUrl(array $report): ?string
    {
        $type = (string) ($report['reportable_type'] ?? '');
        $targetId = (int) ($report['reportable_id'] ?? 0);

        if ($targetId <= 0) {
            return null;
        }

        return match ($type) {
            'property' => url('/admin/properties/' . $targetId),
            'land' => url('/admin/lands/' . $targetId),
            'user' => url('/admin/users/' . $targetId),
            default => null,
        };
    }
}
