<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\NotFoundException;
use App\Core\Session;
use App\Helpers\AuthHelper;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyAvailability;
use App\Services\BookingNotificationService;

final class BookingController extends Controller
{
    public function store(string $id): never
    {
        $propertyId = (int) $id;
        $propertyPath = '/properties/' . $propertyId;
        $propertyUrl = url($propertyPath);

        if (!AuthHelper::check()) {
            $this->redirect(url('/login?redirect=' . rawurlencode($propertyPath)));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=' . rawurlencode($propertyPath)));
        }

        $propertyModel = new Property();
        $property = $propertyModel->findById($propertyId);

        if ($property === null) {
            throw new NotFoundException('Logement introuvable.');
        }

        if (!is_nightly_furnished_property($property)) {
            Session::flash('booking_error', __('booking.error.not_nightly_furnished'));
            $this->redirect($propertyUrl);
        }

        $checkIn = trim((string) $this->request->input('check_in', ''));
        $checkOut = trim((string) $this->request->input('check_out', ''));
        $guests = max(1, (int) $this->request->input('guests', 1));

        $error = $this->validateBooking($property, $checkIn, $checkOut, $guests);
        if ($error !== null) {
            $this->flashBookingError($error, $checkIn, $checkOut, $guests, $propertyUrl);
        }

        $bookingModel = new Booking();

        if ($bookingModel->hasConflict($propertyId, $checkIn, $checkOut)) {
            $this->flashBookingError(__('booking.error.unavailable'), $checkIn, $checkOut, $guests, $propertyUrl);
        }

        if ($bookingModel->hasBlockedDates($propertyId, $checkIn, $checkOut)) {
            $this->flashBookingError(__('booking.error.blocked'), $checkIn, $checkOut, $guests, $propertyUrl);
        }

        $checkInTs = strtotime($checkIn);
        $checkOutTs = strtotime($checkOut);
        $nights = (int) round(($checkOutTs - $checkInTs) / 86400);

        $pricePerNight = (float) ($property['price_per_night'] ?? 0);
        if ($pricePerNight <= 0) {
            $this->flashBookingError(__('booking.error.no_price'), $checkIn, $checkOut, $guests, $propertyUrl);
        }

        $subtotal = round($pricePerNight * $nights, 2);
        $commissionRate = 10.0;
        $commissionAmount = round($subtotal * ($commissionRate / 100), 2);
        $currency = (string) ($property['price_currency'] ?? 'XOF');

        $bookingId = $bookingModel->create([
            'property_id' => $propertyId,
            'user_id' => (int) $user['id'],
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $guests,
            'nights' => $nights,
            'rental_type' => 'nightly',
            'price_per_unit' => $pricePerNight,
            'subtotal' => $subtotal,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'total_amount' => $subtotal,
            'currency' => $currency,
            'status' => 'confirmed',
        ]);

        (new PropertyAvailability())->reserveForBooking($propertyId, $bookingId, $checkIn, $checkOut);

        $mailResult = (new BookingNotificationService())->notifyCreated($bookingId);
        if (!$mailResult['success'] || !($mailResult['mailed'] ?? false)) {
            Session::flash('booking_success', __('booking.success_email_pending'));
        } else {
            Session::flash('booking_success', __('booking.success'));
        }

        $this->redirect(url('/reservations'));
    }

    /**
     * @param array<string, mixed> $property
     */
    private function validateBooking(array $property, string $checkIn, string $checkOut, int $guests): ?string
    {
        if ($checkIn === '' || $checkOut === '') {
            return __('booking.error.dates_required');
        }

        $checkInTs = strtotime($checkIn);
        $checkOutTs = strtotime($checkOut);
        $today = strtotime('today');

        if ($checkInTs === false || $checkOutTs === false || $checkOutTs <= $checkInTs) {
            return __('booking.error.dates_invalid');
        }

        if ($checkInTs < $today) {
            return __('booking.error.check_in_past');
        }

        $capacity = max(1, (int) ($property['capacity'] ?? 1));
        if ($guests < 1 || $guests > $capacity) {
            return __('booking.error.guests');
        }

        return null;
    }

    private function flashBookingError(string $message, string $checkIn, string $checkOut, int $guests, string $propertyUrl): never
    {
        Session::flash('booking_error', $message);
        Session::flash('_old_input.check_in', $checkIn);
        Session::flash('_old_input.check_out', $checkOut);
        Session::flash('_old_input.guests', (string) $guests);
        $this->redirect($propertyUrl);
    }
}
