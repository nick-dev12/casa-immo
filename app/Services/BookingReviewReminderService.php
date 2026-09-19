<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\Notification;

final class BookingReviewReminderService
{
    public function sendForBooking(int $bookingId): bool
    {
        $booking = (new Booking())->findForNotification($bookingId);
        if ($booking === null) {
            return false;
        }

        $guestId = (int) ($booking['user_id'] ?? 0);
        if ($guestId <= 0) {
            return false;
        }

        $propertyTitle = (string) ($booking['property_title'] ?? __('reservations.title'));
        $dates = booking_date_range((string) $booking['check_in'], (string) $booking['check_out']);
        $reviewUrl = '/reservations/' . $bookingId . '/review';

        (new Notification())->create(
            $guestId,
            'booking',
            __('reviews.notify.title'),
            __('reviews.notify.body', [
                'property' => $propertyTitle,
                'dates' => $dates,
            ]),
            [
                'booking_id' => $bookingId,
                'event' => 'review_request',
                'role' => 'guest',
            ]
        );

        (new NotificationEmailService())->sendToUser(
            $guestId,
            __('reviews.notify.title'),
            __('reviews.notify.body', [
                'property' => $propertyTitle,
                'dates' => $dates,
            ]),
            $reviewUrl,
            __('reviews.notify.cta')
        );

        return (new Booking())->markReviewReminderSent($bookingId);
    }
}
