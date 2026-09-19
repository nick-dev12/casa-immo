<?php

declare(strict_types=1);

use App\Core\Env;
use App\Core\Session;
use App\Core\View;
use App\Helpers\LocaleHelper;
use App\Helpers\SecurityHelper;
use App\Services\TranslationService;

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('config')) {
    function config(string $file, ?string $key = null, mixed $default = null): mixed
    {
        static $cache = [];

        if (!isset($cache[$file])) {
            /** @var array<string, mixed> $loaded */
            $loaded = require base_path("config/{$file}.php");
            $cache[$file] = $loaded;
        }

        $config = $cache[$file];

        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? $default;
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__, 2);

        return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path($path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $url = rtrim(config('app', 'url'), '/') . '/assets/' . ltrim($path, '/');
        $file = base_path('assets/' . ltrim($path, '/'));

        if (is_file($file)) {
            $url .= '?v=' . filemtime($file);
        }

        return $url;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return rtrim(config('app', 'url'), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('view')) {
    /**
     * @param array<string, mixed> $data
     */
    function view(string $name, array $data = [], ?string $layout = 'layouts/main'): string
    {
        return View::render($name, $data, $layout);
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return SecurityHelper::escape($value);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Session::csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $name = config('app', 'csrf_token_name', '_token');

        return '<input type="hidden" name="' . e($name) . '" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::getFlash('_old_input.' . $key, $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $default = null): mixed
    {
        return Session::getFlash($key, $default);
    }
}

if (!function_exists('session')) {
    function session(string $key, mixed $default = null): mixed
    {
        return Session::get($key, $default);
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }

        exit;
    }
}

if (!function_exists('money')) {
    function money(float|int|string|null $amount, string $currency = 'XOF'): string
    {
        return \App\Helpers\FormatHelper::money($amount, $currency);
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replace = []): string
    {
        return LocaleHelper::translate($key, $replace);
    }
}

if (!function_exists('locale')) {
    function locale(): string
    {
        return LocaleHelper::current();
    }
}

if (!function_exists('lang_url')) {
    function lang_url(string $localeCode): string
    {
        return url('/lang/' . $localeCode);
    }
}

if (!function_exists('translated')) {
    function translated(?string $text, string $from = 'fr'): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        if (LocaleHelper::isDefault()) {
            return $text;
        }

        return TranslationService::instance()->translate($text, $from, LocaleHelper::current());
    }
}

if (!function_exists('localized_types')) {
    /**
     * @param array<string, string> $types
     * @return array<string, string>
     */
    function localized_types(array $types, string $prefix): array
    {
        $out = [];

        foreach ($types as $key => $label) {
            $translationKey = $prefix . '.' . $key;
            $translated = __($translationKey);

            $out[$key] = $translated !== $translationKey ? $translated : $label;
        }

        return $out;
    }
}

if (!function_exists('guests_summary')) {
    function guests_summary(int $adults, int $children, int $rooms, bool $hasPets = false): string
    {
        $parts = [
            $adults . ' ' . ($adults > 1 ? __('guests.adults') : __('guests.adult')),
            $children . ' ' . ($children > 1 ? __('guests.children') : __('guests.child')),
            $rooms . ' ' . ($rooms > 1 ? __('guests.rooms') : __('guests.room')),
        ];

        if ($hasPets) {
            $parts[] = __('guests.pets_suffix');
        }

        return implode(' · ', $parts);
    }
}

if (!function_exists('property_type')) {
    function property_type(string $type): string
    {
        return \App\Helpers\FormatHelper::propertyTypeLabel($type);
    }
}

if (!function_exists('land_type')) {
    function land_type(string $type): string
    {
        return \App\Helpers\FormatHelper::landTypeLabel($type);
    }
}

if (!function_exists('land_paper_type')) {
    function land_paper_type(?string $type): string
    {
        if ($type === null || $type === '') {
            return '';
        }

        return \App\Helpers\FormatHelper::landPaperTypeLabel($type);
    }
}

if (!function_exists('location_line')) {
    function location_line(array $item, string $region = 'Casamance'): string
    {
        return \App\Helpers\FormatHelper::locationLine($item, $region);
    }
}

if (!function_exists('listing_display_address')) {
    /**
     * @param array<string, mixed> $listing
     */
    function listing_display_address(array $listing): string
    {
        $address = trim((string) ($listing['address'] ?? ''));
        $district = trim((string) ($listing['district'] ?? ''));
        $city = trim((string) ($listing['city'] ?? ''));
        $country = trim((string) ($listing['country'] ?? 'Sénégal'));

        if ($address !== '' && (preg_match('/^(Commune de|Arrondissement|Région de|Department of)/iu', $address) || substr_count($address, ',') >= 3)) {
            $address = '';
        }

        $parts = [];
        foreach ([$address, $district, $city, $country] as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            $haystack = mb_strtolower(implode(' ', $parts));
            if (!str_contains($haystack, mb_strtolower($part))) {
                $parts[] = $part;
            }
        }

        return implode(', ', $parts);
    }
}

if (!function_exists('listing_resolve_coords')) {
    /**
     * @param array<string, mixed> $listing
     * @return array{lat: float, lng: float}|null
     */
    function listing_resolve_coords(array $listing): ?array
    {
        $lat = isset($listing['latitude']) && $listing['latitude'] !== null && $listing['latitude'] !== ''
            ? (float) $listing['latitude']
            : null;
        $lng = isset($listing['longitude']) && $listing['longitude'] !== null && $listing['longitude'] !== ''
            ? (float) $listing['longitude']
            : null;

        if ($lat !== null && $lng !== null && !($lat === 0.0 && $lng === 0.0)
            && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
            return ['lat' => $lat, 'lng' => $lng];
        }

        $estLat = isset($listing['establishment_latitude']) && $listing['establishment_latitude'] !== null
            ? (float) $listing['establishment_latitude']
            : null;
        $estLng = isset($listing['establishment_longitude']) && $listing['establishment_longitude'] !== null
            ? (float) $listing['establishment_longitude']
            : null;

        if ($estLat !== null && $estLng !== null && !($estLat === 0.0 && $estLng === 0.0)
            && $estLat >= -90 && $estLat <= 90 && $estLng >= -180 && $estLng <= 180) {
            return ['lat' => $estLat, 'lng' => $estLng];
        }

        $establishmentId = (int) ($listing['establishment_id'] ?? 0);
        if ($establishmentId <= 0) {
            return null;
        }

        try {
            $establishment = (new \App\Models\Establishment())->findById($establishmentId);
            if ($establishment === null) {
                return null;
            }

            $eLat = (float) ($establishment['latitude'] ?? 0);
            $eLng = (float) ($establishment['longitude'] ?? 0);
            if ($eLat === 0.0 && $eLng === 0.0) {
                return null;
            }
            if ($eLat < -90 || $eLat > 90 || $eLng < -180 || $eLng > 180) {
                return null;
            }

            return ['lat' => $eLat, 'lng' => $eLng];
        } catch (\Throwable) {
            return null;
        }
    }
}

if (!function_exists('listing_has_map_coords')) {
    /**
     * @param array<string, mixed> $listing
     */
    function listing_has_map_coords(array $listing): bool
    {
        return listing_resolve_coords($listing) !== null;
    }
}

if (!function_exists('listing_map_urls')) {
    /**
     * @param array<string, mixed> $listing
     * @return array{
     *   show: bool,
     *   has_coords: bool,
     *   latitude: float|null,
     *   longitude: float|null,
     *   zoom: int,
     *   query: string,
     *   embed: string,
     *   link: string
     * }
     */
    function listing_map_urls(array $listing): array
    {
        $coords = listing_resolve_coords($listing);
        $hasCoords = $coords !== null;
        $lat = $coords['lat'] ?? null;
        $lng = $coords['lng'] ?? null;
        $address = listing_display_address($listing);
        $title = trim((string) ($listing['title'] ?? ''));
        $zoom = 14;

        if ($hasCoords && $lat !== null && $lng !== null) {
            $coordsQuery = sprintf('%.8F,%.8F', $lat, $lng);
            $labeledQuery = $title !== ''
                ? sprintf('%.8F,%.8F+(%s)', $lat, $lng, $title)
                : $coordsQuery;
            $mapParams = 'q=' . rawurlencode($coordsQuery)
                . '&ll=' . rawurlencode($coordsQuery)
                . '&hl=fr&z=' . $zoom;

            return [
                'show' => true,
                'has_coords' => true,
                'latitude' => $lat,
                'longitude' => $lng,
                'zoom' => $zoom,
                'query' => $coordsQuery,
                'label' => $title !== '' ? $title : $address,
                'embed' => 'https://www.google.com/maps?' . $mapParams . '&output=embed',
                'link' => 'https://www.google.com/maps?q=' . rawurlencode($labeledQuery) . '&ll=' . rawurlencode($coordsQuery) . '&hl=fr&z=' . $zoom,
            ];
        }

        return [
            'show' => false,
            'has_coords' => false,
            'latitude' => null,
            'longitude' => null,
            'zoom' => $zoom,
            'query' => $address,
            'label' => $title !== '' ? $title : $address,
            'embed' => '',
            'link' => $address !== ''
                ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address)
                : '',
        ];
    }
}

if (!function_exists('rating_display')) {
    function rating_display(float|int|string|null $rating): string
    {
        return \App\Helpers\FormatHelper::ratingDisplay($rating);
    }
}

if (!function_exists('property_image')) {
    function property_image(?string $path, string $type = 'autre'): string
    {
        return \App\Helpers\FormatHelper::propertyImage($path, $type);
    }
}

if (!function_exists('land_image')) {
    function land_image(?string $path, string $type = 'autre'): string
    {
        return \App\Helpers\FormatHelper::landImage($path, $type);
    }
}

if (!function_exists('land_area')) {
    function land_area(float|int|string $area, string $unit): string
    {
        return \App\Helpers\FormatHelper::landArea($area, $unit);
    }
}

if (!function_exists('land_dimensions')) {
    function land_dimensions(float|int|string|null $length, float|int|string|null $width): string
    {
        return \App\Helpers\FormatHelper::landDimensions($length, $width);
    }
}

if (!function_exists('rating_label')) {
    function rating_label(float|int|string|null $rating): string
    {
        $value = (float) $rating;

        return match (true) {
            $value >= 9 => 'Exceptionnel',
            $value >= 8 => 'Très bien',
            $value >= 7 => 'Bien',
            $value >= 6 => 'Agréable',
            $value > 0 => 'Correct',
            default => 'Nouveau',
        };
    }
}

if (!function_exists('amenity_icon')) {
    function amenity_icon(string $icon): string
    {
        $map = [
            'wifi' => 'bi-wifi',
            'snow' => 'bi-snow',
            'car' => 'bi-car-front',
            'pool' => 'bi-water',
            'utensils' => 'bi-cup-hot',
            'washer' => 'bi-droplet',
            'tv' => 'bi-tv',
            'shield' => 'bi-shield-check',
            'door-open' => 'bi-door-open',
            'droplet' => 'bi-droplet-fill',
            'cup-hot' => 'bi-cup-hot-fill',
            'droplet-half' => 'bi-moisture',
            'door-closed' => 'bi-door-closed',
            'layers' => 'bi-layers',
            'water' => 'bi-tsunami',
            'sofa' => 'bi-lamp-fill',
        ];

        return $map[$icon] ?? 'bi-check-circle-fill';
    }
}

if (!function_exists('amenity_category')) {
    function amenity_category(string $category): string
    {
        $map = [
            'confort' => 'Confort',
            'cuisine' => 'Cuisine & Repas',
            'exterieur' => 'Extérieur',
            'securite' => 'Sécurité',
            'services' => 'Services',
        ];

        return $map[$category] ?? ucfirst($category);
    }
}

if (!function_exists('format_date')) {
    function format_date(string $date, string $format = 'd/m/Y'): string
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }

        if ($format === 'month_year') {
            $months = [
                1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
                5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
                9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
            ];
            $month = (int) date('n', $timestamp);

            return ($months[$month] ?? date('F', $timestamp)) . ' ' . date('Y', $timestamp);
        }

        if ($format === 'ticket') {
            $months = [
                1 => 'janv.', 2 => 'févr.', 3 => 'mars', 4 => 'avr.',
                5 => 'mai', 6 => 'juin', 7 => 'juil.', 8 => 'août',
                9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.',
            ];
            $month = (int) date('n', $timestamp);

            return (int) date('j', $timestamp) . ' ' . ($months[$month] ?? date('M', $timestamp)) . ' ' . date('Y', $timestamp);
        }

        if ($format === 'datetime') {
            return date('d/m/Y H:i', $timestamp);
        }

        if ($format === 'stay') {
            $weekdays = ['dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'];
            $months = [
                1 => 'janv.', 2 => 'févr.', 3 => 'mars', 4 => 'avr.',
                5 => 'mai', 6 => 'juin', 7 => 'juil.', 8 => 'août',
                9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.',
            ];
            $weekday = $weekdays[(int) date('w', $timestamp)] ?? '';
            $month = (int) date('n', $timestamp);

            return $weekday . ' ' . (int) date('j', $timestamp) . ' '
                . ($months[$month] ?? date('M', $timestamp)) . ' ' . date('Y', $timestamp);
        }

        return date($format, $timestamp);
    }
}

if (!function_exists('booking_date_range')) {
    function booking_date_range(string $checkIn, string $checkOut): string
    {
        $start = strtotime($checkIn);
        $end = strtotime($checkOut);
        if ($start === false || $end === false) {
            return format_date($checkIn, 'd/m/Y') . ' – ' . format_date($checkOut, 'd/m/Y');
        }

        $months = [
            1 => 'janv.', 2 => 'févr.', 3 => 'mars', 4 => 'avr.',
            5 => 'mai', 6 => 'juin', 7 => 'juil.', 8 => 'août',
            9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.',
        ];

        $startDay = (int) date('j', $start);
        $endDay = (int) date('j', $end);
        $startMonth = (int) date('n', $start);
        $endMonth = (int) date('n', $end);
        $startYear = date('Y', $start);
        $endYear = date('Y', $end);

        if ($startYear === $endYear && $startMonth === $endMonth) {
            return $startDay . '–' . $endDay . ' ' . ($months[$startMonth] ?? '') . ' ' . $startYear;
        }

        if ($startYear === $endYear) {
            return $startDay . ' ' . ($months[$startMonth] ?? '') . ' – '
                . $endDay . ' ' . ($months[$endMonth] ?? '') . ' ' . $startYear;
        }

        return format_date($checkIn, 'ticket') . ' – ' . format_date($checkOut, 'ticket');
    }
}

if (!function_exists('booking_reservation_tab')) {
    function booking_reservation_tab(array $booking): string
    {
        $status = (string) ($booking['status'] ?? 'pending');
        if (in_array($status, ['cancelled', 'rejected'], true)) {
            return 'cancelled';
        }

        if ($status === 'completed') {
            return 'past';
        }

        $checkOut = strtotime((string) ($booking['check_out'] ?? ''));
        if ($checkOut !== false && $checkOut < strtotime('today')) {
            return 'past';
        }

        return 'active';
    }
}

if (!function_exists('booking_can_cancel')) {
    function booking_can_cancel(array $booking): bool
    {
        return (new \App\Models\Booking())->canCancel($booking);
    }
}

if (!function_exists('booking_detail_options')) {
    /**
     * @return list<array<string, mixed>>
     */
    function booking_detail_options(array $booking): array
    {
        $bookingId = (int) ($booking['id'] ?? 0);
        $propertyId = (int) ($booking['property_id'] ?? 0);
        $city = (string) ($booking['city'] ?? '');
        $status = (string) ($booking['status'] ?? 'pending');
        $tab = booking_reservation_tab($booking);
        $isCancelled = in_array($status, ['cancelled', 'rejected'], true);
        $canCancel = booking_can_cancel($booking);
        $phone = trim((string) ($booking['establishment_phone'] ?? ''));
        $title = translated((string) ($booking['title'] ?? ''));
        $propertyUrl = url('/properties/' . $propertyId . '?city=' . urlencode($city));
        $detailUrl = url('/reservations/' . $bookingId);
        $shareReservationText = $title . ' · ' . booking_stay_range_label(
            (string) ($booking['check_in'] ?? ''),
            (string) ($booking['check_out'] ?? '')
        );
        $hasAddress = trim((string) ($booking['address'] ?? '')) !== ''
            || trim((string) ($booking['city'] ?? '')) !== '';

        $options = [];

        if (!$isCancelled) {
            $options[] = [
                'key' => 'share_booking',
                'label' => __('reservations.detail.share_booking'),
                'icon' => 'bi-send',
                'type' => 'share',
                'share_title' => $shareReservationText,
                'url' => $detailUrl,
            ];
        }

        $options[] = [
            'key' => 'share_property',
            'label' => __('reservations.detail.share_property'),
            'icon' => 'bi-box-arrow-up',
            'type' => 'share',
            'share_title' => $title,
            'url' => $propertyUrl,
        ];

        if (!$isCancelled && booking_invoice_is_available($booking)) {
            $options[] = [
                'key' => 'invoice',
                'label' => __('invoice.option_label'),
                'icon' => 'bi-file-earmark-pdf',
                'type' => 'link',
                'url' => url('/reservations/' . $bookingId . '/invoice'),
                'external' => true,
            ];
        }

        if (!$isCancelled && $tab === 'active') {
            $options[] = [
                'key' => 'view_ticket',
                'label' => __('reservations.detail.view_ticket'),
                'icon' => 'bi-qr-code',
                'type' => 'action',
                'action' => 'ticket',
            ];

            if ($hasAddress) {
                $options[] = [
                    'key' => 'directions',
                    'label' => __('reservations.detail.directions'),
                    'icon' => 'bi-geo-alt',
                    'type' => 'link',
                    'url' => maps_directions_url($booking),
                    'external' => true,
                ];
            }

            if ($phone !== '') {
                $options[] = [
                    'key' => 'call',
                    'label' => __('reservations.detail.call', ['phone' => $phone]),
                    'icon' => 'bi-telephone',
                    'type' => 'link',
                    'url' => 'tel:' . preg_replace('/\s+/', '', $phone),
                ];
            }
        }

        $options[] = [
            'key' => 'view_listing',
            'label' => __('reservations.view_listing'),
            'icon' => 'bi-house-door',
            'type' => 'link',
            'url' => $propertyUrl,
        ];

        if ($isCancelled || $tab === 'past') {
            $options[] = [
                'key' => 'book_again',
                'label' => __('reservations.detail.book_again'),
                'icon' => 'bi-calendar-plus',
                'type' => 'link',
                'url' => $propertyUrl,
            ];
        }

        $options[] = [
            'key' => 'share_app',
            'label' => __('reservations.detail.share_app'),
            'icon' => 'bi-stars',
            'type' => 'share',
            'share_title' => (string) config('app', 'name'),
            'url' => url('/'),
        ];

        if ($canCancel) {
            $options[] = [
                'key' => 'cancel',
                'label' => __('reservations.cancel.action'),
                'icon' => 'bi-x-circle',
                'type' => 'form',
                'form_action' => url('/reservations/' . $bookingId . '/cancel'),
                'confirm' => __('reservations.cancel.confirm'),
                'danger' => true,
            ];
        }

        return $options;
    }
}

if (!function_exists('booking_detail_safety_options')) {
    /**
     * @return list<array<string, mixed>>
     */
    function booking_detail_safety_options(array $booking): array
    {
        $tab = booking_reservation_tab($booking);
        $status = (string) ($booking['status'] ?? '');
        $options = [
            [
                'key' => 'safety_center',
                'label' => __('reservations.detail.safety_center'),
                'icon' => 'bi-globe2',
                'type' => 'link',
                'url' => url('/help'),
            ],
        ];

        if ($tab === 'active' && !in_array($status, ['cancelled', 'rejected'], true)) {
            $city = trim((string) ($booking['establishment_city'] ?? $booking['city'] ?? ''));
            $options[] = [
                'key' => 'emergency',
                'label' => __('reservations.detail.emergency'),
                'icon' => 'bi-exclamation-octagon',
                'type' => 'panel',
                'panel' => 'emergency',
                'city' => $city !== '' ? $city : 'Ziguinchor',
            ];
        }

        return $options;
    }
}

if (!function_exists('booking_local_emergency_services')) {
    /**
     * Numéros d'urgence locaux (Ziguinchor / Casamance).
     *
     * @return list<array<string, string>>
     */
    function booking_local_emergency_services(string $city = ''): array
    {
        $cityKey = mb_strtolower(trim($city));
        $isZiguinchor = $cityKey === ''
            || str_contains($cityKey, 'ziguinchor')
            || str_contains($cityKey, 'casamance');

        if (!$isZiguinchor) {
            return booking_local_emergency_services('Ziguinchor');
        }

        return [
            [
                'key' => 'police',
                'label' => __('reservations.emergency.police'),
                'number' => '17',
                'tel' => '17',
                'description' => __('reservations.emergency.police_desc'),
                'icon' => 'bi-shield-shaded',
                'tone' => 'police',
            ],
            [
                'key' => 'fire',
                'label' => __('reservations.emergency.fire'),
                'number' => '18',
                'tel' => '18',
                'description' => __('reservations.emergency.fire_desc'),
                'icon' => 'bi-fire',
                'tone' => 'fire',
            ],
            [
                'key' => 'samu',
                'label' => __('reservations.emergency.samu'),
                'number' => '1515',
                'tel' => '1515',
                'description' => __('reservations.emergency.samu_desc'),
                'icon' => 'bi-heart-pulse-fill',
                'tone' => 'medical',
            ],
            [
                'key' => 'gendarmerie',
                'label' => __('reservations.emergency.gendarmerie'),
                'number' => '800 00 20 20',
                'tel' => '800002020',
                'description' => __('reservations.emergency.gendarmerie_desc'),
                'icon' => 'bi-person-badge-fill',
                'tone' => 'gendarmerie',
            ],
            [
                'key' => 'hospital',
                'label' => __('reservations.emergency.hospital'),
                'number' => '33 991 12 62',
                'tel' => '+221339911262',
                'description' => __('reservations.emergency.hospital_desc'),
                'icon' => 'bi-hospital-fill',
                'tone' => 'hospital',
            ],
        ];
    }
}

if (!function_exists('booking_stay_range_label')) {
    function booking_stay_range_label(string $checkIn, string $checkOut): string
    {
        return format_date($checkIn, 'stay') . ' - ' . format_date($checkOut, 'stay');
    }
}

if (!function_exists('maps_directions_url')) {
    function maps_directions_url(array $item): string
    {
        $lat = $item['establishment_latitude'] ?? $item['latitude'] ?? null;
        $lng = $item['establishment_longitude'] ?? $item['longitude'] ?? null;
        if ($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
            return 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode((string) $lat . ',' . (string) $lng);
        }

        $address = trim((string) ($item['establishment_address'] ?? $item['address'] ?? ''));
        $city = trim((string) ($item['establishment_city'] ?? $item['city'] ?? ''));
        $query = $address !== '' ? ($city !== '' ? $address . ', ' . $city : $address) : $city;

        return 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($query);
    }
}

if (!function_exists('booking_reference')) {
    function booking_reference(int $bookingId): string
    {
        return strtoupper('ZIG' . str_pad(base_convert((string) max(1, $bookingId), 10, 36), 8, '0', STR_PAD_LEFT));
    }
}

if (!function_exists('booking_confirmation_number')) {
    function booking_confirmation_number(int $bookingId): string
    {
        $base = 6000000000 + ($bookingId % 3999999999);

        return str_pad((string) $base, 10, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('booking_email_date_long')) {
    function booking_email_date_long(string $date): string
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }

        $weekdays = [
            'dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi',
        ];
        $months = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
        ];
        $weekday = $weekdays[(int) date('w', $timestamp)] ?? '';
        $month = (int) date('n', $timestamp);

        return $weekday . ' ' . (int) date('j', $timestamp) . ' '
            . ($months[$month] ?? date('F', $timestamp)) . ' ' . date('Y', $timestamp);
    }
}

if (!function_exists('booking_email_room_label')) {
    /**
     * @param array<string, mixed> $booking
     */
    function booking_email_room_label(array $booking): string
    {
        $type = (string) ($booking['property_type'] ?? 'autre');
        $title = translated((string) ($booking['property_title'] ?? ''));
        $labels = [
            'appartement' => __('booking.email.room.apartment'),
            'maison' => __('booking.email.room.house'),
            'villa' => __('booking.email.room.villa'),
            'studio' => __('booking.email.room.studio'),
            'chambre' => __('booking.email.room.room'),
        ];
        $typeLabel = $labels[$type] ?? __('booking.email.room.default');

        if ($title !== '') {
            return $typeLabel . ' — ' . $title;
        }

        return $typeLabel;
    }
}

if (!function_exists('invoice_number')) {
    function invoice_number(int $bookingId): string
    {
        return 'FAC-' . booking_reference($bookingId);
    }
}

if (!function_exists('booking_invoice_is_available')) {
    /**
     * @param array<string, mixed> $booking
     */
    function booking_invoice_is_available(array $booking): bool
    {
        return (new \App\Services\BookingInvoiceService())->isAvailable($booking);
    }
}

if (!function_exists('city_abbrev')) {
    function city_abbrev(string $city): string
    {
        $word = preg_split('/\s+/u', trim($city))[0] ?? $city;
        $letters = preg_replace('/[^a-zA-ZÀ-ÿ]/u', '', $word);

        if ($letters === '') {
            return 'ZIG';
        }

        return strtoupper(substr($letters, 0, 3));
    }
}

if (!function_exists('booking_status_abbrev')) {
    function booking_status_abbrev(string $status): string
    {
        $map = [
            'pending' => 'ATT',
            'confirmed' => 'CNF',
            'cancelled' => 'ANL',
            'completed' => 'FIN',
            'rejected' => 'REF',
        ];

        return $map[$status] ?? strtoupper(substr($status, 0, 3));
    }
}

if (!function_exists('booking_qr_url')) {
    function booking_qr_url(string $reference): string
    {
        $payload = rtrim(url(''), '/') . '/reservations#' . $reference;

        return 'https://api.qrserver.com/v1/create-qr-code/?size=168x168&margin=10&data=' . rawurlencode($payload);
    }
}

if (!function_exists('nightly_furnished_types')) {
    /** @return list<string> */
    function nightly_furnished_types(): array
    {
        return ['appartement', 'studio'];
    }
}

if (!function_exists('is_nightly_furnished_property')) {
    /**
     * @param array<string, mixed> $property
     */
    function is_nightly_furnished_property(array $property): bool
    {
        $type = (string) ($property['type'] ?? '');
        $priceNight = (float) ($property['price_per_night'] ?? 0);

        return in_array($type, nightly_furnished_types(), true) && $priceNight > 0;
    }
}

if (!function_exists('is_nightly_furnished_booking')) {
    /**
     * @param array<string, mixed> $booking
     */
    function is_nightly_furnished_booking(array $booking): bool
    {
        $rentalType = (string) ($booking['rental_type'] ?? 'nightly');
        $type = (string) ($booking['type'] ?? '');

        return $rentalType === 'nightly' && in_array($type, nightly_furnished_types(), true);
    }
}

if (!function_exists('mask_email')) {
    function mask_email(string $email): string
    {
        if (!str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, 1);
        $maskedLocal = $visible . str_repeat('*', max(3, mb_strlen($local) - 1));

        return $maskedLocal . '@' . $domain;
    }
}

if (!function_exists('user_initials')) {
    /**
     * @param array<string, mixed>|null $user
     */
    function user_initials(?array $user): string
    {
        if ($user === null) {
            return '?';
        }

        return mb_strtoupper(
            mb_substr((string) ($user['first_name'] ?? ''), 0, 1)
            . mb_substr((string) ($user['last_name'] ?? ''), 0, 1)
        );
    }
}

if (!function_exists('user_avatar_url')) {
    /**
     * @param array<string, mixed>|null $user
     */
    function user_avatar_url(?array $user): ?string
    {
        $avatar = trim((string) ($user['avatar'] ?? ''));
        if ($avatar === '') {
            return null;
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        return asset(ltrim($avatar, '/'));
    }
}

if (!function_exists('user_preferences')) {
    /**
     * @return array<string, mixed>
     */
    function user_preferences(int $userId): array
    {
        $defaults = [
            'push_notifications' => true,
            'email_notifications' => true,
            'sms_notifications' => false,
            'large_text' => false,
            'high_contrast' => false,
            'marketing_emails' => true,
            'booking_updates' => true,
            'promotional_offers' => false,
        ];

        $stored = Session::get('user_preferences.' . $userId);
        if (!is_array($stored)) {
            return $defaults;
        }

        return array_merge($defaults, $stored);
    }
}

if (!function_exists('save_user_preferences')) {
    /**
     * @param array<string, mixed> $preferences
     */
    function save_user_preferences(int $userId, array $preferences): void
    {
        $current = user_preferences($userId);
        Session::set('user_preferences.' . $userId, array_merge($current, $preferences));
    }
}

if (!function_exists('upload_url')) {
    function upload_url(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return url(ltrim($path, '/'));
    }
}

if (!function_exists('rental_card_status')) {
    /**
     * @param array<string, mixed> $contract
     */
    function rental_card_status(array $contract): string
    {
        $status = (string) ($contract['status'] ?? 'draft');
        $endDate = (string) ($contract['end_date'] ?? '');

        if ($status === 'expired' || ($status === 'active' && $endDate !== '' && $endDate < date('Y-m-d'))) {
            return 'expired';
        }

        if ($status === 'active') {
            return 'active';
        }

        return 'inactive';
    }
}

if (!function_exists('rental_search_blob')) {
    /**
     * @param array<string, mixed> $contract
     */
    function rental_search_blob(array $contract): string
    {
        $parts = [
            $contract['property_title'] ?? '',
            $contract['property_city'] ?? '',
            $contract['tenant_first_name'] ?? '',
            $contract['tenant_last_name'] ?? '',
            $contract['tenant_email'] ?? '',
            $contract['tenant_phone'] ?? '',
            $contract['contract_type'] ?? '',
        ];

        return mb_strtolower(implode(' ', array_map('strval', $parts)));
    }
}

if (!function_exists('agency_search_blob')) {
    /**
     * @param array<string, mixed> $agency
     */
    function agency_search_blob(array $agency): string
    {
        $parts = [
            $agency['name'] ?? '',
            $agency['city'] ?? '',
            $agency['district'] ?? '',
            $agency['address'] ?? '',
            $agency['first_name'] ?? '',
            $agency['last_name'] ?? '',
            $agency['email'] ?? '',
            $agency['owner_phone'] ?? '',
            $agency['phone'] ?? '',
            $agency['status'] ?? '',
        ];

        return mb_strtolower(implode(' ', array_map('strval', $parts)));
    }
}

if (!function_exists('user_search_blob')) {
    /**
     * @param array<string, mixed> $user
     */
    function user_search_blob(array $user): string
    {
        $parts = [
            $user['first_name'] ?? '',
            $user['last_name'] ?? '',
            $user['email'] ?? '',
            $user['phone'] ?? '',
            $user['roles'] ?? '',
            $user['role_slugs'] ?? '',
            $user['status'] ?? '',
            $user['establishment_name'] ?? '',
        ];

        return mb_strtolower(implode(' ', array_map('strval', $parts)));
    }
}

if (!function_exists('user_primary_role_label')) {
    /**
     * @param array<string, mixed> $user
     */
    function user_primary_role_label(array $user): string
    {
        $roles = trim((string) ($user['roles'] ?? ''));
        if ($roles === '') {
            return __('admin.user.role_unknown');
        }

        $first = explode(',', $roles)[0] ?? $roles;

        return trim($first);
    }
}

if (!function_exists('report_reason_label')) {
    function report_reason_label(string $reason): string
    {
        $key = 'admin.report.reason.' . $reason;
        $translated = __($key);

        return $translated !== $key ? $translated : ucfirst(str_replace('_', ' ', $reason));
    }
}

if (!function_exists('report_search_blob')) {
    /**
     * @param array<string, mixed> $report
     */
    function report_search_blob(array $report): string
    {
        $parts = [
            $report['reportable_type'] ?? '',
            $report['reportable_id'] ?? '',
            $report['listing_title'] ?? '',
            $report['reason'] ?? '',
            $report['description'] ?? '',
            $report['status'] ?? '',
            $report['first_name'] ?? '',
            $report['last_name'] ?? '',
            $report['reporter_email'] ?? '',
        ];

        return mb_strtolower(implode(' ', array_map('strval', $parts)));
    }
}

if (!function_exists('report_type_label')) {
    /**
     * @param array<string, mixed> $report
     */
    function report_type_label(array $report): string
    {
        $type = (string) ($report['reportable_type'] ?? '');
        $key = 'admin.report.type.' . $type;
        $translated = __($key);

        return $translated !== $key ? $translated : ucfirst($type);
    }
}

if (!function_exists('unread_messages_count')) {
    function unread_messages_count(): int
    {
        static $cached = false;
        static $count = 0;

        if ($cached) {
            return $count;
        }

        $cached = true;
        $userId = \App\Helpers\AuthHelper::id();
        if ($userId === null) {
            return 0;
        }

        try {
            $count = (new \App\Models\Conversation())->unreadCountForUser($userId);
        } catch (\Throwable) {
            $count = 0;
        }

        return $count;
    }
}

if (!function_exists('unread_reservations_count')) {
    function unread_reservations_count(): int
    {
        static $cached = false;
        static $count = 0;

        if ($cached) {
            return $count;
        }

        $cached = true;
        $userId = \App\Helpers\AuthHelper::id();
        if ($userId === null) {
            return 0;
        }

        try {
            $count = (new \App\Models\Notification())->unreadCountByType($userId, 'booking');
        } catch (\Throwable) {
            $count = 0;
        }

        return $count;
    }
}

if (!function_exists('user_is_property_host')) {
    function user_is_property_host(?int $userId = null): bool
    {
        $userId ??= \App\Helpers\AuthHelper::id();
        if ($userId === null) {
            return false;
        }

        try {
            if ((new \App\Models\Establishment())->findByOwnerId($userId) !== null) {
                return true;
            }

            return (new \App\Models\Booking())->ownerHasBookings($userId);
        } catch (\Throwable) {
            return false;
        }
    }
}

if (!function_exists('booking_guest_name')) {
    function booking_guest_name(array $booking): string
    {
        $name = \App\Helpers\AuthHelper::fullName([
            'first_name' => $booking['guest_first_name'] ?? '',
            'last_name' => $booking['guest_last_name'] ?? '',
        ]);

        return $name !== '' ? $name : __('messages.unknown');
    }
}

if (!function_exists('format_message_time')) {
    function format_message_time(string $datetime, bool $timeOnlyToday = false): string
    {
        $datetime = trim($datetime);
        if ($datetime === '') {
            return '';
        }

        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return $datetime;
        }

        $today = date('Y-m-d');
        $day = date('Y-m-d', $timestamp);
        $clock = date('H:i', $timestamp);

        if ($day === $today) {
            return $timeOnlyToday ? $clock : $clock;
        }

        if ($day === date('Y-m-d', strtotime('-1 day'))) {
            return $timeOnlyToday ? __('messages.yesterday') . ' ' . $clock : __('messages.yesterday');
        }

        return $timeOnlyToday ? date('d/m H:i', $timestamp) : date('d/m/Y', $timestamp);
    }
}

if (!function_exists('messages_start_url')) {
    function messages_start_url(?int $propertyId = null, ?int $landId = null, ?int $withUserId = null): string
    {
        if ($propertyId !== null && $propertyId > 0) {
            $path = '/messages/start?property_id=' . $propertyId;
            if ($withUserId !== null && $withUserId > 0) {
                $path .= '&with_user=' . $withUserId;
            }
        } elseif ($landId !== null && $landId > 0) {
            $path = '/messages/start?land_id=' . $landId;
        } else {
            $path = '/messages';
        }

        if (!\App\Helpers\AuthHelper::check()) {
            return url('/login?redirect=' . rawurlencode($path));
        }

        return url($path);
    }
}
