<?php

declare(strict_types=1);

namespace App\Services;

final class BookingInvoiceService
{
    /**
     * @param array<string, mixed> $booking
     * @return array<string, mixed>
     */
    public function build(array $booking): array
    {
        $bookingId = (int) ($booking['id'] ?? 0);
        $nights = max(1, (int) ($booking['nights'] ?? 1));
        $pricePerUnit = (float) ($booking['price_per_unit'] ?? 0);
        $subtotal = (float) ($booking['subtotal'] ?? 0);

        if ($subtotal <= 0 && $pricePerUnit > 0) {
            $subtotal = $pricePerUnit * $nights;
        }
        if ($pricePerUnit <= 0 && $nights > 0 && $subtotal > 0) {
            $pricePerUnit = $subtotal / $nights;
        }
        if ($subtotal <= 0) {
            $subtotal = (float) ($booking['total_amount'] ?? 0);
        }

        $currency = strtoupper((string) ($booking['currency'] ?? 'XOF'));
        $taxRate = 0.0;
        $taxAmount = 0.0;
        $total = (float) ($booking['total_amount'] ?? $subtotal);

        $guestName = trim(
            ((string) ($booking['guest_first_name'] ?? '')) . ' '
            . ((string) ($booking['guest_last_name'] ?? ''))
        );
        if ($guestName === '') {
            $guestName = trim(
                ((string) ($booking['first_name'] ?? '')) . ' '
                . ((string) ($booking['last_name'] ?? ''))
            );
        }

        $establishmentName = trim((string) ($booking['establishment_name'] ?? ''));
        $propertyTitle = translated((string) ($booking['title'] ?? ''));
        $projectLabel = $establishmentName !== ''
            ? $establishmentName . ' — ' . $propertyTitle
            : $propertyTitle;

        $addressParts = array_filter([
            trim((string) ($booking['establishment_address'] ?? $booking['address'] ?? '')),
            trim((string) ($booking['establishment_district'] ?? $booking['district'] ?? '')),
            trim((string) ($booking['establishment_city'] ?? $booking['city'] ?? '')),
            trim((string) ($booking['country'] ?? config('app', 'country', 'Sénégal'))),
        ]);

        return [
            'invoice_number' => invoice_number($bookingId),
            'reference' => booking_reference($bookingId),
            'issued_at' => (string) ($booking['created_at'] ?? ''),
            'status' => (string) ($booking['status'] ?? 'pending'),
            'guest_name' => $guestName,
            'guest_email' => trim((string) ($booking['guest_email'] ?? $booking['email'] ?? '')),
            'guest_phone' => trim((string) ($booking['guest_phone'] ?? $booking['phone'] ?? '')),
            'guest_address' => '',
            'project_label' => $projectLabel,
            'property_title' => $propertyTitle,
            'establishment_name' => $establishmentName,
            'property_address' => implode(', ', $addressParts),
            'check_in' => (string) ($booking['check_in'] ?? ''),
            'check_out' => (string) ($booking['check_out'] ?? ''),
            'guests' => (int) ($booking['guests'] ?? 0),
            'nights' => $nights,
            'line_label' => __('invoice.line.accommodation'),
            'line_description' => booking_stay_range_label(
                (string) ($booking['check_in'] ?? ''),
                (string) ($booking['check_out'] ?? '')
            ),
            'quantity' => $nights,
            'unit_price' => $pricePerUnit,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'currency' => $currency,
            'company' => [
                'name' => (string) config('app', 'invoice.company_name', config('app', 'name', 'Zig Imobilier')),
                'address' => (string) config('app', 'invoice.address', ''),
                'city' => (string) config('app', 'invoice.city', config('app', 'default_city', 'Ziguinchor')),
                'region' => (string) config('app', 'region', 'Casamance'),
                'country' => (string) config('app', 'country', 'Sénégal'),
                'email' => (string) config('app', 'invoice.email', ''),
                'phone' => (string) config('app', 'invoice.phone', ''),
                'website' => rtrim((string) config('app', 'url', ''), '/'),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $booking
     */
    public function isAvailable(array $booking): bool
    {
        $status = (string) ($booking['status'] ?? '');

        return in_array($status, ['pending', 'confirmed', 'completed'], true);
    }
}
