<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;

final class BookingConfirmationEmailService
{
    public function sendForBooking(int $bookingId): array
    {
        $booking = (new Booking())->findForGuestConfirmation($bookingId);
        if ($booking === null) {
            return ['success' => false, 'message' => 'Réservation introuvable.', 'mailed' => false];
        }

        $email = trim((string) ($booking['guest_email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'E-mail voyageur invalide.', 'mailed' => false];
        }

        $payload = $this->buildPayload($booking);
        $subject = __('booking.email.subject', [
            'property' => $payload['property_title'],
            'city' => $payload['city'],
        ]);

        $result = $this->deliver($email, $subject, $payload);
        if ($result['success'] && ($result['mailed'] ?? false)) {
            (new Booking())->markConfirmationEmailSent($bookingId);
        }

        return $result;
    }

    public function sendPendingBatch(int $limit = 15): int
    {
        $sent = 0;
        foreach ((new Booking())->idsPendingConfirmationEmail($limit) as $bookingId) {
            $result = $this->sendForBooking($bookingId);
            if ($result['success'] && ($result['mailed'] ?? false)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * E-mail de démonstration (test).
     *
     * @return array{success: bool, message: string, mailed: bool}
     */
    public function sendDemoTo(string $email, string $firstName = 'Vroling'): array
    {
        $checkIn = date('Y-m-d', strtotime('+14 days'));
        $checkOut = date('Y-m-d', strtotime($checkIn . ' +4 days'));
        $bookingId = 90042;

        $booking = [
            'id' => $bookingId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => 2,
            'nights' => 4,
            'total_amount' => 185000,
            'currency' => 'XOF',
            'confirmation_pin' => (string) random_int(1000, 9999),
            'property_title' => 'Appartement meublé vue mangrove',
            'property_type' => 'appartement',
            'city' => 'Tilène',
            'district' => 'Tilène',
            'establishment_name' => '',
            'guest_first_name' => $firstName,
            'guest_last_name' => '',
            'guest_email' => $email,
        ];

        $payload = $this->buildPayload($booking);
        $subject = __('booking.email.subject', [
            'property' => $payload['property_title'],
            'city' => $payload['city'],
        ]);

        return $this->deliver($email, $subject, $payload);
    }

    /**
     * @param array<string, mixed> $booking
     * @return array<string, mixed>
     */
    private function buildPayload(array $booking): array
    {
        $bookingId = (int) ($booking['id'] ?? 0);
        $firstName = trim((string) ($booking['guest_first_name'] ?? ''));
        $establishment = trim((string) ($booking['establishment_name'] ?? ''));
        $title = translated((string) ($booking['property_title'] ?? __('reservations.title')));
        $propertyTitle = $establishment !== '' ? $establishment : $title;
        $city = trim((string) ($booking['city'] ?? config('app', 'default_city', 'Ziguinchor')));
        $nights = (int) ($booking['nights'] ?? 0);
        $guests = (int) ($booking['guests'] ?? 1);
        $pin = (string) ($booking['confirmation_pin'] ?? '');

        if ($pin === '' && $bookingId > 0) {
            $pin = str_pad((string) (($bookingId * 7919 + 1000) % 10000), 4, '0', STR_PAD_LEFT);
        }

        return [
            'booking_id' => $bookingId,
            'first_name' => $firstName,
            'confirmation_number' => booking_confirmation_number($bookingId),
            'confirmation_pin' => $pin,
            'property_title' => $propertyTitle,
            'listing_title' => $title,
            'city' => $city,
            'check_in' => (string) ($booking['check_in'] ?? ''),
            'check_out' => (string) ($booking['check_out'] ?? ''),
            'check_in_long' => booking_email_date_long((string) ($booking['check_in'] ?? '')),
            'check_out_long' => booking_email_date_long((string) ($booking['check_out'] ?? '')),
            'check_in_window' => __('booking.email.checkin_window'),
            'check_out_window' => __('booking.email.checkout_window'),
            'nights' => $nights,
            'nights_label' => $nights === 1
                ? __('booking.email.one_night')
                : __('booking.email.nights_count', ['count' => $nights]),
            'room_label' => booking_email_room_label($booking),
            'guests_label' => $guests === 1
                ? __('booking.email.one_guest')
                : __('booking.email.guests_count', ['count' => $guests]),
            'total_amount' => money($booking['total_amount'] ?? 0, (string) ($booking['currency'] ?? 'XOF')),
            'manage_url' => url('/reservations/' . $bookingId),
            'app_name' => (string) config('app', 'name', 'Casa-blog Immo'),
            'emergency_city' => $city,
            'emergency_services' => array_values(array_filter(
                booking_local_emergency_services($city),
                static fn (array $service): bool => in_array($service['key'] ?? '', ['police', 'gendarmerie', 'fire'], true)
            )),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{success: bool, message: string, mailed: bool}
     */
    private function deliver(string $email, string $subject, array $payload): array
    {
        $appName = (string) ($payload['app_name'] ?? config('app', 'name'));
        $firstName = (string) ($payload['first_name'] ?? '');
        $preheader = __('booking.email.preheader', [
            'city' => (string) ($payload['city'] ?? ''),
        ]);

        ob_start();
        include base_path('views/emails/booking-confirmation.php');
        $html = (string) ob_get_clean();

        $text = $this->buildPlainText($payload);

        return (new MailService())->send($email, $subject, $html, $text, true);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildPlainText(array $payload): string
    {
        $lines = [
            __('booking.email.confirmation_line', ['number' => (string) $payload['confirmation_number']]),
            __('booking.email.pin_line', ['pin' => (string) $payload['confirmation_pin']]),
            '',
            __('booking.email.thanks', [
                'name' => (string) ($payload['first_name'] !== '' ? $payload['first_name'] : __('booking.email.guest_fallback')),
                'city' => (string) $payload['city'],
            ]),
            '',
            (string) $payload['property_title'],
            __('booking.email.arrival') . ': ' . (string) $payload['check_in_long'] . ' (' . (string) $payload['check_in_window'] . ')',
            __('booking.email.departure') . ': ' . (string) $payload['check_out_long'] . ' (' . (string) $payload['check_out_window'] . ')',
            __('booking.email.booking_line') . ': ' . (string) $payload['nights_label'] . ', ' . (string) $payload['room_label'],
            __('booking.email.guests_line') . ': ' . (string) $payload['guests_label'],
            __('booking.email.total_line') . ': ' . (string) $payload['total_amount'],
            '',
            __('booking.email.pin_warning'),
            '',
            __('booking.email.emergency_title'),
            __('booking.email.emergency_lead', ['city' => (string) ($payload['emergency_city'] ?? '')]),
        ];

        foreach ($payload['emergency_services'] ?? [] as $service) {
            if (!is_array($service)) {
                continue;
            }
            $lines[] = (string) ($service['label'] ?? '') . ' : ' . (string) ($service['number'] ?? '');
        }

        $lines[] = '';
        $lines[] = (string) $payload['manage_url'];

        return implode("\n", $lines);
    }
}
