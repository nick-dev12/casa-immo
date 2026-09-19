<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;

final class BookingReviewService
{
    public function guestCanReview(array $booking, int $userId): bool
    {
        if ((int) ($booking['user_id'] ?? 0) !== $userId) {
            return false;
        }

        $status = (string) ($booking['status'] ?? '');
        if (!in_array($status, ['confirmed', 'completed'], true)) {
            return false;
        }

        $checkOut = (string) ($booking['check_out'] ?? '');
        if ($checkOut === '') {
            return false;
        }

        $minReviewDate = date('Y-m-d', strtotime($checkOut . ' +' . BookingLifecycleService::REVIEW_REMINDER_DAYS_AFTER_CHECKOUT . ' days'));
        if (date('Y-m-d') < $minReviewDate) {
            return false;
        }

        $bookingId = (int) ($booking['id'] ?? 0);

        return $bookingId > 0 && !(new Review())->existsForBooking($bookingId);
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public function submit(int $bookingId, int $userId, int $rating, string $comment): array
    {
        $booking = (new Booking())->findForUser($bookingId, $userId);
        if ($booking === null) {
            return ['ok' => false, 'error' => __('reviews.error.not_found')];
        }

        if (!$this->guestCanReview($booking, $userId)) {
            return ['ok' => false, 'error' => __('reviews.error.not_allowed')];
        }

        if ($rating < 1 || $rating > 5) {
            return ['ok' => false, 'error' => __('reviews.error.rating')];
        }

        $comment = trim($comment);
        if (mb_strlen($comment) > 2000) {
            return ['ok' => false, 'error' => __('reviews.error.comment_length')];
        }

        (new Review())->create([
            'user_id' => $userId,
            'property_id' => (int) $booking['property_id'],
            'booking_id' => $bookingId,
            'rating' => $rating,
            'comment' => $comment !== '' ? $comment : null,
        ]);

        return ['ok' => true];
    }
}
