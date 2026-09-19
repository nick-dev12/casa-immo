<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Booking extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function forUser(int $userId): array
    {
        $sql = '
            SELECT
                b.id,
                b.property_id,
                b.check_in,
                b.check_out,
                b.guests,
                b.nights,
                b.rental_type,
                b.total_amount,
                b.currency,
                b.status,
                b.created_at,
                p.title,
                p.city,
                p.district,
                p.type,
                (
                    SELECT pi.path
                    FROM property_images pi
                    WHERE pi.property_id = p.id
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC
                    LIMIT 1
                ) AS primary_image
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            WHERE b.user_id = :user_id
              AND p.deleted_at IS NULL
            ORDER BY b.check_in DESC, b.created_at DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * Réservations reçues sur les logements du propriétaire.
     *
     * @return list<array<string, mixed>>
     */
    public function forOwner(int $ownerId): array
    {
        $sql = '
            SELECT
                b.id,
                b.property_id,
                b.user_id,
                b.check_in,
                b.check_out,
                b.guests,
                b.nights,
                b.rental_type,
                b.total_amount,
                b.currency,
                b.status,
                b.created_at,
                p.title,
                p.city,
                p.district,
                p.type,
                u.first_name AS guest_first_name,
                u.last_name AS guest_last_name,
                (
                    SELECT pi.path
                    FROM property_images pi
                    WHERE pi.property_id = p.id
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC
                    LIMIT 1
                ) AS primary_image
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            INNER JOIN users u ON u.id = b.user_id
            WHERE p.owner_id = :owner_id
              AND p.deleted_at IS NULL
            ORDER BY b.created_at DESC, b.check_in DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':owner_id' => $ownerId]);

        return $stmt->fetchAll();
    }

    public function ownerHasBookings(int $ownerId): bool
    {
        $stmt = $this->db->prepare('
            SELECT 1
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            WHERE p.owner_id = :owner_id
              AND p.deleted_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([':owner_id' => $ownerId]);

        return (bool) $stmt->fetchColumn();
    }

    public function userHasActiveBookingForProperty(int $userId, int $propertyId): bool
    {
        $stmt = $this->db->prepare('
            SELECT 1
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            WHERE b.user_id = :user_id
              AND b.property_id = :property_id
              AND b.status IN (\'pending\', \'confirmed\', \'completed\')
              AND p.deleted_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':property_id' => $propertyId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForOwner(int $bookingId, int $ownerId): ?array
    {
        $sql = '
            SELECT
                b.id,
                b.property_id,
                b.user_id,
                b.check_in,
                b.check_out,
                b.guests,
                b.nights,
                b.rental_type,
                b.price_per_unit,
                b.subtotal,
                b.commission_rate,
                b.commission_amount,
                b.total_amount,
                b.currency,
                b.status,
                b.created_at,
                p.title,
                p.description,
                p.type,
                p.address,
                p.city,
                p.district,
                p.country,
                p.latitude,
                p.longitude,
                p.rules,
                p.owner_id,
                u.first_name AS guest_first_name,
                u.last_name AS guest_last_name,
                u.email AS guest_email,
                u.phone AS guest_phone,
                e.name AS establishment_name,
                e.phone AS establishment_phone,
                e.address AS establishment_address,
                e.city AS establishment_city,
                e.district AS establishment_district,
                e.latitude AS establishment_latitude,
                e.longitude AS establishment_longitude,
                (
                    SELECT pi.path
                    FROM property_images pi
                    WHERE pi.property_id = p.id
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC
                    LIMIT 1
                ) AS primary_image
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            INNER JOIN users u ON u.id = b.user_id
            LEFT JOIN establishments e ON e.id = p.establishment_id
            WHERE b.id = :booking_id
              AND p.owner_id = :owner_id
              AND p.deleted_at IS NULL
            LIMIT 1
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':booking_id' => $bookingId,
            ':owner_id' => $ownerId,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForUser(int $bookingId, int $userId): ?array
    {
        $sql = '
            SELECT
                b.id,
                b.property_id,
                b.user_id,
                b.check_in,
                b.check_out,
                b.guests,
                b.nights,
                b.rental_type,
                b.price_per_unit,
                b.subtotal,
                b.total_amount,
                b.currency,
                b.status,
                b.created_at,
                p.title,
                p.description,
                p.type,
                p.address,
                p.city,
                p.district,
                p.country,
                p.latitude,
                p.longitude,
                p.rules,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                e.name AS establishment_name,
                e.phone AS establishment_phone,
                e.address AS establishment_address,
                e.city AS establishment_city,
                e.district AS establishment_district,
                e.latitude AS establishment_latitude,
                e.longitude AS establishment_longitude,
                (
                    SELECT pi.path
                    FROM property_images pi
                    WHERE pi.property_id = p.id
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC
                    LIMIT 1
                ) AS primary_image
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            INNER JOIN users u ON u.id = b.user_id
            LEFT JOIN establishments e ON e.id = p.establishment_id
            WHERE b.id = :booking_id
              AND b.user_id = :user_id
              AND p.deleted_at IS NULL
            LIMIT 1
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':booking_id' => $bookingId,
            ':user_id' => $userId,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function canCancel(array $booking): bool
    {
        $status = (string) ($booking['status'] ?? '');
        if (!in_array($status, ['pending', 'confirmed'], true)) {
            return false;
        }

        $checkIn = strtotime((string) ($booking['check_in'] ?? ''));

        return $checkIn !== false && $checkIn >= strtotime('today');
    }

    public function cancelForUser(int $bookingId, int $userId): bool
    {
        $booking = $this->findForUser($bookingId, $userId);
        if ($booking === null || !$this->canCancel($booking)) {
            return false;
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('
                UPDATE bookings
                SET status = \'cancelled\',
                    cancelled_at = NOW(),
                    updated_at = NOW()
                WHERE id = :booking_id
                  AND user_id = :user_id
                  AND status IN (\'pending\', \'confirmed\')
            ');
            $stmt->execute([
                ':booking_id' => $bookingId,
                ':user_id' => $userId,
            ]);

            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();

                return false;
            }

            $release = $this->db->prepare('
                DELETE FROM property_availability
                WHERE booking_id = :booking_id
                  AND status = \'reserved\'
            ');
            $release->execute([':booking_id' => $bookingId]);

            $this->db->commit();

            return true;
        } catch (\Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return false;
        }
    }

    public function canRejectForHost(array $booking): bool
    {
        $status = (string) ($booking['status'] ?? '');
        if (!in_array($status, ['pending', 'confirmed'], true)) {
            return false;
        }

        $checkIn = strtotime((string) ($booking['check_in'] ?? ''));

        return $checkIn !== false && $checkIn >= strtotime('today');
    }

    public function rejectForHost(int $bookingId, int $ownerId): bool
    {
        $booking = $this->findForHost($bookingId, $ownerId);
        if ($booking === null || !$this->canRejectForHost($booking)) {
            return false;
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('
                UPDATE bookings
                SET status = \'rejected\',
                    cancelled_at = NOW(),
                    updated_at = NOW()
                WHERE id = :booking_id
                  AND status IN (\'pending\', \'confirmed\')
            ');
            $stmt->execute([':booking_id' => $bookingId]);

            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();

                return false;
            }

            $release = $this->db->prepare('
                DELETE FROM property_availability
                WHERE booking_id = :booking_id
                  AND status = \'reserved\'
            ');
            $release->execute([':booking_id' => $bookingId]);

            $this->db->commit();

            return true;
        } catch (\Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return false;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO bookings (
                property_id,
                user_id,
                check_in,
                check_out,
                guests,
                nights,
                rental_type,
                price_per_unit,
                subtotal,
                commission_rate,
                commission_amount,
                total_amount,
                currency,
                status,
                confirmation_pin
            ) VALUES (
                :property_id,
                :user_id,
                :check_in,
                :check_out,
                :guests,
                :nights,
                :rental_type,
                :price_per_unit,
                :subtotal,
                :commission_rate,
                :commission_amount,
                :total_amount,
                :currency,
                :status,
                :confirmation_pin
            )
        ');

        $confirmationPin = (string) ($data['confirmation_pin'] ?? '');
        if ($confirmationPin === '') {
            $confirmationPin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        }

        $stmt->execute([
            ':property_id' => (int) $data['property_id'],
            ':user_id' => (int) $data['user_id'],
            ':check_in' => (string) $data['check_in'],
            ':check_out' => (string) $data['check_out'],
            ':guests' => (int) $data['guests'],
            ':nights' => (int) $data['nights'],
            ':rental_type' => (string) $data['rental_type'],
            ':price_per_unit' => (float) $data['price_per_unit'],
            ':subtotal' => (float) $data['subtotal'],
            ':commission_rate' => (float) $data['commission_rate'],
            ':commission_amount' => (float) $data['commission_amount'],
            ':total_amount' => (float) $data['total_amount'],
            ':currency' => (string) $data['currency'],
            ':status' => (string) $data['status'],
            ':confirmation_pin' => $confirmationPin,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForGuestConfirmation(int $bookingId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT
                b.id,
                b.user_id,
                b.property_id,
                b.check_in,
                b.check_out,
                b.guests,
                b.nights,
                b.status,
                b.total_amount,
                b.currency,
                b.confirmation_pin,
                p.title AS property_title,
                p.type AS property_type,
                p.city,
                p.district,
                e.name AS establishment_name,
                u.first_name AS guest_first_name,
                u.last_name AS guest_last_name,
                u.email AS guest_email
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            INNER JOIN users u ON u.id = b.user_id
            LEFT JOIN establishments e ON e.id = p.establishment_id
            WHERE b.id = :booking_id
            LIMIT 1
        ');
        $stmt->execute([':booking_id' => $bookingId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForNotification(int $bookingId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT
                b.id,
                b.user_id,
                b.property_id,
                b.check_in,
                b.check_out,
                b.guests,
                b.status,
                b.total_amount,
                b.currency,
                p.title AS property_title,
                p.owner_id,
                u.first_name AS guest_first_name,
                u.last_name AS guest_last_name
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            INNER JOIN users u ON u.id = b.user_id
            WHERE b.id = :booking_id
            LIMIT 1
        ');
        $stmt->execute([':booking_id' => $bookingId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function hasConflict(int $propertyId, string $checkIn, string $checkOut): bool
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM bookings
            WHERE property_id = :property_id
              AND status IN (\'pending\', \'confirmed\')
              AND check_in < :check_out
              AND check_out > :check_in
        ');

        $stmt->execute([
            ':property_id' => $propertyId,
            ':check_in' => $checkIn,
            ':check_out' => $checkOut,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function hasBlockedDates(int $propertyId, string $checkIn, string $checkOut): bool
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM property_availability
            WHERE property_id = :property_id
              AND date >= :check_in
              AND date < :check_out
              AND status IN (\'blocked\', \'reserved\', \'maintenance\')
        ');

        $stmt->execute([
            ':property_id' => $propertyId,
            ':check_in' => $checkIn,
            ':check_out' => $checkOut,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function countForUser(int $userId): int
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            WHERE b.user_id = :user_id
              AND p.deleted_at IS NULL
        ');
        $stmt->execute([':user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function completedCountForUser(int $userId): int
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            WHERE b.user_id = :user_id
              AND b.status IN (\'confirmed\', \'completed\')
              AND p.deleted_at IS NULL
        ');
        $stmt->execute([':user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function travelerCountForUser(int $userId): int
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM booking_guests bg
            INNER JOIN bookings b ON b.id = bg.booking_id
            WHERE b.user_id = :user_id
        ');
        $stmt->execute([':user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function travelersForUser(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT
                bg.id,
                bg.first_name,
                bg.last_name,
                bg.email,
                bg.phone,
                b.check_in,
                b.check_out
            FROM booking_guests bg
            INNER JOIN bookings b ON b.id = bg.booking_id
            WHERE b.user_id = :user_id
            ORDER BY b.check_in DESC, bg.last_name ASC
        ');
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * Dates couvertes par des réservations actives (pending / confirmed).
     *
     * @return list<string>
     */
    public function reservedDatesForProperty(int $propertyId, int $monthsAhead = 18): array
    {
        $stmt = $this->db->prepare('
            SELECT check_in, check_out
            FROM bookings
            WHERE property_id = :property_id
              AND status IN (\'pending\', \'confirmed\')
              AND check_out >= CURDATE()
              AND check_in <= DATE_ADD(CURDATE(), INTERVAL :months MONTH)
        ');
        $stmt->bindValue(':property_id', $propertyId, \PDO::PARAM_INT);
        $stmt->bindValue(':months', $monthsAhead, \PDO::PARAM_INT);
        $stmt->execute();

        $dates = [];
        foreach ($stmt->fetchAll() as $row) {
            $start = strtotime((string) ($row['check_in'] ?? ''));
            $end = strtotime((string) ($row['check_out'] ?? ''));
            if ($start === false || $end === false || $end <= $start) {
                continue;
            }
            for ($ts = $start; $ts < $end; $ts += 86400) {
                $iso = date('Y-m-d', $ts);
                if ($iso >= date('Y-m-d')) {
                    $dates[$iso] = true;
                }
            }
        }

        return array_keys($dates);
    }

    public function markPastStaysCompleted(): int
    {
        $stmt = $this->db->prepare('
            UPDATE bookings
            SET status = \'completed\',
                updated_at = NOW()
            WHERE status = \'confirmed\'
              AND check_out < CURDATE()
        ');
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * @return list<int>
     */
    public function idsDueForReviewReminder(int $daysAfterCheckout): array
    {
        $stmt = $this->db->prepare('
            SELECT b.id
            FROM bookings b
            LEFT JOIN reviews r ON r.booking_id = b.id
            WHERE b.status IN (\'confirmed\', \'completed\')
              AND b.check_out = DATE_SUB(CURDATE(), INTERVAL :days DAY)
              AND b.review_reminder_sent_at IS NULL
              AND r.id IS NULL
        ');
        $stmt->bindValue(':days', $daysAfterCheckout, \PDO::PARAM_INT);
        $stmt->execute();

        $ids = [];
        foreach ($stmt->fetchAll() as $row) {
            $ids[] = (int) ($row['id'] ?? 0);
        }

        return array_values(array_filter($ids, static fn (int $id): bool => $id > 0));
    }

    /**
     * @return list<int>
     */
    public function idsPendingConfirmationEmail(int $limit = 20): array
    {
        $stmt = $this->db->prepare('
            SELECT b.id
            FROM bookings b
            INNER JOIN users u ON u.id = b.user_id
            WHERE b.status IN (\'pending\', \'confirmed\')
              AND b.confirmation_email_sent_at IS NULL
              AND b.created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
              AND u.email IS NOT NULL
              AND TRIM(u.email) <> \'\'
            ORDER BY b.id ASC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $ids = [];
        foreach ($stmt->fetchAll() as $row) {
            $ids[] = (int) ($row['id'] ?? 0);
        }

        return array_values(array_filter($ids, static fn (int $id): bool => $id > 0));
    }

    public function markConfirmationEmailSent(int $bookingId): bool
    {
        $stmt = $this->db->prepare('
            UPDATE bookings
            SET confirmation_email_sent_at = NOW(),
                updated_at = NOW()
            WHERE id = :booking_id
              AND confirmation_email_sent_at IS NULL
        ');
        $stmt->execute([':booking_id' => $bookingId]);

        return $stmt->rowCount() > 0;
    }

    public function markReviewReminderSent(int $bookingId): bool
    {
        $stmt = $this->db->prepare('
            UPDATE bookings
            SET review_reminder_sent_at = NOW(),
                updated_at = NOW()
            WHERE id = :booking_id
              AND review_reminder_sent_at IS NULL
        ');
        $stmt->execute([':booking_id' => $bookingId]);

        return $stmt->rowCount() > 0;
    }
}
