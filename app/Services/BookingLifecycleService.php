<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;

final class BookingLifecycleService
{
    public const REVIEW_REMINDER_DAYS_AFTER_CHECKOUT = 2;

    /**
     * @return array{completed: int, review_reminders: int}
     */
    public function runDaily(): array
    {
        $bookingModel = new Booking();

        return [
            'completed' => $bookingModel->markPastStaysCompleted(),
            'review_reminders' => $this->sendDueReviewReminders(),
        ];
    }

    public function sendDueReviewReminders(): int
    {
        $bookingModel = new Booking();
        $reminderService = new BookingReviewReminderService();
        $sent = 0;

        foreach ($bookingModel->idsDueForReviewReminder(self::REVIEW_REMINDER_DAYS_AFTER_CHECKOUT) as $bookingId) {
            if ($reminderService->sendForBooking($bookingId)) {
                $sent++;
            }
        }

        return $sent;
    }
}
