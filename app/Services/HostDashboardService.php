<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Model;

final class HostDashboardService extends Model
{
    /**
     * @return array{
     *   stats: array<string, int|float>,
     *   recent_bookings: list<array<string, mixed>>
     * }
     */
    public function forOwner(int $ownerId): array
    {
        return [
            'stats' => $this->stats($ownerId),
            'recent_bookings' => $this->recentBookings($ownerId, 3),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    private function stats(int $ownerId): array
    {
        $propertyStmt = $this->db->prepare('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'approved\' THEN 1 ELSE 0 END) AS published,
                SUM(CASE WHEN status = \'draft\' THEN 1 ELSE 0 END) AS drafts,
                SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) AS pending_review
            FROM properties
            WHERE owner_id = :owner_id
              AND deleted_at IS NULL
        ');
        $propertyStmt->execute([':owner_id' => $ownerId]);
        /** @var array<string, mixed>|false $propertyRow */
        $propertyRow = $propertyStmt->fetch();

        $landStmt = $this->db->prepare('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'approved\' THEN 1 ELSE 0 END) AS published,
                SUM(CASE WHEN status = \'draft\' THEN 1 ELSE 0 END) AS drafts
            FROM lands
            WHERE seller_id = :owner_id
              AND deleted_at IS NULL
        ');
        $landStmt->execute([':owner_id' => $ownerId]);
        /** @var array<string, mixed>|false $landRow */
        $landRow = $landStmt->fetch();

        $bookingStmt = $this->db->prepare('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN b.status IN (\'confirmed\', \'pending\') AND b.check_out >= CURDATE() THEN 1 ELSE 0 END) AS upcoming,
                SUM(CASE WHEN b.status = \'confirmed\' THEN 1 ELSE 0 END) AS confirmed,
                COALESCE(SUM(CASE WHEN b.status IN (\'confirmed\', \'completed\') THEN b.total_amount ELSE 0 END), 0) AS revenue
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            WHERE p.owner_id = :owner_id
        ');
        $bookingStmt->execute([':owner_id' => $ownerId]);
        /** @var array<string, mixed>|false $bookingRow */
        $bookingRow = $bookingStmt->fetch();

        $visitStmt = $this->db->prepare('
            SELECT COUNT(*) AS pending
            FROM land_visits lv
            INNER JOIN lands l ON l.id = lv.land_id
            WHERE l.seller_id = :owner_id
              AND lv.status = \'pending\'
        ');
        $visitPending = 0;
        try {
            $visitStmt->execute([':owner_id' => $ownerId]);
            /** @var array<string, mixed>|false $visitRow */
            $visitRow = $visitStmt->fetch();
            $visitPending = (int) ($visitRow['pending'] ?? 0);
        } catch (\Throwable) {
            $visitPending = 0;
        }

        $propertiesTotal = (int) ($propertyRow['total'] ?? 0);
        $landsTotal = (int) ($landRow['total'] ?? 0);

        return [
            'listings_total' => $propertiesTotal + $landsTotal,
            'listings_published' => (int) ($propertyRow['published'] ?? 0) + (int) ($landRow['published'] ?? 0),
            'listings_draft' => (int) ($propertyRow['drafts'] ?? 0) + (int) ($landRow['drafts'] ?? 0),
            'listings_pending' => (int) ($propertyRow['pending_review'] ?? 0),
            'properties_total' => $propertiesTotal,
            'lands_total' => $landsTotal,
            'bookings_total' => (int) ($bookingRow['total'] ?? 0),
            'bookings_upcoming' => (int) ($bookingRow['upcoming'] ?? 0),
            'bookings_confirmed' => (int) ($bookingRow['confirmed'] ?? 0),
            'revenue' => (float) ($bookingRow['revenue'] ?? 0),
            'visits_pending' => $visitPending,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentBookings(int $ownerId, int $limit = 5): array
    {
        $stmt = $this->db->prepare('
            SELECT
                b.id,
                b.property_id,
                b.check_in,
                b.check_out,
                b.guests,
                b.total_amount,
                b.currency,
                b.status,
                b.created_at,
                p.title,
                p.city,
                p.type,
                (
                    SELECT pi.path
                    FROM property_images pi
                    WHERE pi.property_id = p.id
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC
                    LIMIT 1
                ) AS primary_image,
                u.first_name AS guest_first_name,
                u.last_name AS guest_last_name
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            INNER JOIN users u ON u.id = b.user_id
            WHERE p.owner_id = :owner_id
            ORDER BY b.created_at DESC
            LIMIT ' . max(1, min($limit, 20)) . '
        ');
        $stmt->execute([':owner_id' => $ownerId]);

        return $stmt->fetchAll();
    }
}
