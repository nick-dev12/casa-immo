<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PropertyAvailability extends Model
{
    /**
     * @return array<string, string> date => status
     */
    public function statusMap(int $propertyId, int $monthsAhead = 18): array
    {
        $stmt = $this->db->prepare('
            SELECT date, status
            FROM property_availability
            WHERE property_id = :property_id
              AND date >= CURDATE()
              AND date <= DATE_ADD(CURDATE(), INTERVAL :months MONTH)
        ');
        $stmt->bindValue(':property_id', $propertyId, \PDO::PARAM_INT);
        $stmt->bindValue(':months', $monthsAhead, \PDO::PARAM_INT);
        $stmt->execute();

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $date = (string) ($row['date'] ?? '');
            if ($date !== '') {
                $map[$date] = (string) ($row['status'] ?? 'blocked');
            }
        }

        return $map;
    }

    /**
     * @param list<string> $dates
     */
    public function blockDates(int $propertyId, array $dates): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO property_availability (property_id, date, status, note)
            VALUES (:property_id, :date, \'blocked\', NULL)
            ON DUPLICATE KEY UPDATE status = IF(status = \'reserved\', status, \'blocked\'), note = NULL
        ');

        $count = 0;
        foreach ($dates as $date) {
            $date = trim($date);
            if ($date === '' || strtotime($date) === false) {
                continue;
            }
            if (strtotime($date) < strtotime('today')) {
                continue;
            }
            $stmt->execute([
                ':property_id' => $propertyId,
                ':date' => date('Y-m-d', strtotime($date)),
            ]);
            $count++;
        }

        return $count;
    }

    public function forProperty(int $propertyId, int $daysAhead = 90): array
    {
        $stmt = $this->db->prepare('
            SELECT id, date, status, note
            FROM property_availability
            WHERE property_id = :property_id
              AND date >= CURDATE()
              AND date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY date ASC
        ');
        $stmt->bindValue(':property_id', $propertyId, \PDO::PARAM_INT);
        $stmt->bindValue(':days', $daysAhead, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function reserveForBooking(int $propertyId, int $bookingId, string $checkIn, string $checkOut): void
    {
        $start = strtotime($checkIn);
        $end = strtotime($checkOut);
        if ($start === false || $end === false || $end <= $start) {
            return;
        }

        $stmt = $this->db->prepare('
            INSERT INTO property_availability (property_id, date, status, booking_id, note)
            VALUES (:property_id, :date, \'reserved\', :booking_id, NULL)
            ON DUPLICATE KEY UPDATE
                status = \'reserved\',
                booking_id = :booking_id_update,
                note = NULL
        ');

        for ($ts = $start; $ts < $end; $ts += 86400) {
            $stmt->execute([
                ':property_id' => $propertyId,
                ':date' => date('Y-m-d', $ts),
                ':booking_id' => $bookingId,
                ':booking_id_update' => $bookingId,
            ]);
        }
    }

    public function releaseForBooking(int $bookingId): void
    {
        $stmt = $this->db->prepare('
            DELETE FROM property_availability
            WHERE booking_id = :booking_id
              AND status = \'reserved\'
        ');
        $stmt->execute([':booking_id' => $bookingId]);
    }

    public function blockRange(int $propertyId, string $from, string $to, string $status = 'blocked', ?string $note = null): void
    {
        $start = strtotime($from);
        $end = strtotime($to);
        if ($start === false || $end === false || $end < $start) {
            return;
        }

        $stmt = $this->db->prepare('
            INSERT INTO property_availability (property_id, date, status, note)
            VALUES (:property_id, :date, :status, :note)
            ON DUPLICATE KEY UPDATE status = VALUES(status), note = VALUES(note)
        ');

        for ($ts = $start; $ts <= $end; $ts += 86400) {
            $stmt->execute([
                ':property_id' => $propertyId,
                ':date' => date('Y-m-d', $ts),
                ':status' => $status,
                ':note' => $note,
            ]);
        }
    }

    public function unblockDate(int $propertyId, string $date): void
    {
        $stmt = $this->db->prepare('
            DELETE FROM property_availability
            WHERE property_id = :property_id
              AND date = :date
              AND status IN (\'blocked\', \'maintenance\')
        ');
        $stmt->execute([
            ':property_id' => $propertyId,
            ':date' => $date,
        ]);
    }
}
