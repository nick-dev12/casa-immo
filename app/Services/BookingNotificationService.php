<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuthHelper;
use App\Models\Booking;
use App\Models\Notification;

final class BookingNotificationService
{
    /**
     * @return array{success: bool, message: string, mailed: bool}
     */
    public function notifyCreated(int $bookingId): array
    {
        $mailResult = ['success' => false, 'message' => 'no_guest', 'mailed' => false];
        $booking = (new Booking())->findForNotification($bookingId);
        if ($booking === null) {
            return $mailResult;
        }

        $propertyTitle = (string) ($booking['property_title'] ?? __('reservations.title'));
        $guestName = AuthHelper::fullName([
            'first_name' => $booking['guest_first_name'] ?? '',
            'last_name' => $booking['guest_last_name'] ?? '',
        ]);
        $dates = booking_date_range((string) $booking['check_in'], (string) $booking['check_out']);
        $ownerId = (int) ($booking['owner_id'] ?? 0);
        $guestId = (int) ($booking['user_id'] ?? 0);

        if ($ownerId > 0 && $ownerId !== $guestId) {
            (new Notification())->create(
                $ownerId,
                'booking',
                __('reservations.notify.host_new_title'),
                __('reservations.notify.host_new_body', [
                    'guest' => $guestName !== '' ? $guestName : __('messages.unknown'),
                    'property' => $propertyTitle,
                    'dates' => $dates,
                ]),
                [
                    'booking_id' => $bookingId,
                    'event' => 'created',
                    'role' => 'host',
                ]
            );
            (new NotificationEmailService())->sendToUser(
                $ownerId,
                __('reservations.notify.host_new_title'),
                __('reservations.notify.host_new_body', [
                    'guest' => $guestName !== '' ? $guestName : __('messages.unknown'),
                    'property' => $propertyTitle,
                    'dates' => $dates,
                ]),
                '/profile/notifications'
            );
        }

        if ($guestId > 0) {
            (new Notification())->create(
                $guestId,
                'booking',
                __('reservations.notify.guest_created_title'),
                __('reservations.notify.guest_created_body', [
                    'property' => $propertyTitle,
                    'dates' => $dates,
                ]),
                [
                    'booking_id' => $bookingId,
                    'event' => 'created',
                    'role' => 'guest',
                ]
            );
            $mailResult = (new BookingConfirmationEmailService())->sendForBooking($bookingId);
        }

        return $mailResult;
    }

    public function notifyRejected(int $bookingId): void
    {
        $booking = (new Booking())->findForNotification($bookingId);
        if ($booking === null) {
            return;
        }

        $propertyTitle = (string) ($booking['property_title'] ?? __('reservations.title'));
        $guestName = AuthHelper::fullName([
            'first_name' => $booking['guest_first_name'] ?? '',
            'last_name' => $booking['guest_last_name'] ?? '',
        ]);
        $dates = booking_date_range((string) $booking['check_in'], (string) $booking['check_out']);
        $ownerId = (int) ($booking['owner_id'] ?? 0);
        $guestId = (int) ($booking['user_id'] ?? 0);

        if ($guestId > 0) {
            (new Notification())->create(
                $guestId,
                'booking',
                __('reservations.notify.guest_rejected_title'),
                __('reservations.notify.guest_rejected_body', [
                    'property' => $propertyTitle,
                    'dates' => $dates,
                ]),
                [
                    'booking_id' => $bookingId,
                    'event' => 'rejected',
                    'role' => 'guest',
                ]
            );
            (new NotificationEmailService())->sendToUser(
                $guestId,
                __('reservations.notify.guest_rejected_title'),
                __('reservations.notify.guest_rejected_body', [
                    'property' => $propertyTitle,
                    'dates' => $dates,
                ]),
                '/reservations/' . $bookingId
            );
        }

        if ($ownerId > 0 && $ownerId !== $guestId) {
            (new Notification())->create(
                $ownerId,
                'booking',
                __('reservations.notify.host_rejected_title'),
                __('reservations.notify.host_rejected_body', [
                    'guest' => $guestName !== '' ? $guestName : __('messages.unknown'),
                    'property' => $propertyTitle,
                    'dates' => $dates,
                ]),
                [
                    'booking_id' => $bookingId,
                    'event' => 'rejected',
                    'role' => 'host',
                ]
            );
            (new NotificationEmailService())->sendToUser(
                $ownerId,
                __('reservations.notify.host_rejected_title'),
                __('reservations.notify.host_rejected_body', [
                    'guest' => $guestName !== '' ? $guestName : __('messages.unknown'),
                    'property' => $propertyTitle,
                    'dates' => $dates,
                ]),
                '/profile/notifications'
            );
        }
    }

    public function notifyCancelled(int $bookingId): void
    {
        $booking = (new Booking())->findForNotification($bookingId);
        if ($booking === null) {
            return;
        }

        $propertyTitle = (string) ($booking['property_title'] ?? __('reservations.title'));
        $guestName = AuthHelper::fullName([
            'first_name' => $booking['guest_first_name'] ?? '',
            'last_name' => $booking['guest_last_name'] ?? '',
        ]);
        $dates = booking_date_range((string) $booking['check_in'], (string) $booking['check_out']);
        $ownerId = (int) ($booking['owner_id'] ?? 0);
        $guestId = (int) ($booking['user_id'] ?? 0);

        if ($ownerId > 0) {
            (new Notification())->create(
                $ownerId,
                'booking',
                __('reservations.notify.host_cancelled_title'),
                __('reservations.notify.host_cancelled_body', [
                    'guest' => $guestName !== '' ? $guestName : __('messages.unknown'),
                    'property' => $propertyTitle,
                    'dates' => $dates,
                ]),
                [
                    'booking_id' => $bookingId,
                    'event' => 'cancelled',
                    'role' => 'host',
                ]
            );
            (new NotificationEmailService())->sendToUser(
                $ownerId,
                __('reservations.notify.host_cancelled_title'),
                __('reservations.notify.host_cancelled_body', [
                    'guest' => $guestName !== '' ? $guestName : __('messages.unknown'),
                    'property' => $propertyTitle,
                    'dates' => $dates,
                ]),
                '/profile/notifications'
            );
        }

        if ($guestId > 0) {
            (new Notification())->create(
                $guestId,
                'booking',
                __('reservations.notify.guest_cancelled_title'),
                __('reservations.notify.guest_cancelled_body', [
                    'property' => $propertyTitle,
                    'dates' => $dates,
                ]),
                [
                    'booking_id' => $bookingId,
                    'event' => 'cancelled',
                    'role' => 'guest',
                ]
            );
            (new NotificationEmailService())->sendToUser(
                $guestId,
                __('reservations.notify.guest_cancelled_title'),
                __('reservations.notify.guest_cancelled_body', [
                    'property' => $propertyTitle,
                    'dates' => $dates,
                ]),
                '/reservations/' . $bookingId
            );
        }
    }
}
