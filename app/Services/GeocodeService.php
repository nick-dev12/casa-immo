<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\DestinationHelper;

final class GeocodeService
{
    /**
     * @return array{
     *   address: string,
     *   full_address: string,
     *   street: string,
     *   district: string,
     *   city: string,
     *   country: string,
     *   postcode: string,
     *   latitude: float,
     *   longitude: float
     * }|null
     */
    public function reverse(float $latitude, float $longitude): ?array
    {
        $url = sprintf(
            'https://nominatim.openstreetmap.org/reverse?lat=%F&lon=%F&format=json&addressdetails=1&accept-language=fr&zoom=18',
            $latitude,
            $longitude
        );

        $body = $this->fetch($url);
        if ($body === null) {
            return null;
        }

        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($body, true);
        if (!is_array($payload)) {
            return null;
        }

        /** @var array<string, string> $parts */
        $parts = is_array($payload['address'] ?? null) ? $payload['address'] : [];

        $street = trim(($parts['house_number'] ?? '') . ' ' . ($parts['road'] ?? $parts['pedestrian'] ?? $parts['footway'] ?? $parts['residential'] ?? ''));
        $district = trim((string) (
            $parts['suburb']
            ?? $parts['neighbourhood']
            ?? $parts['quarter']
            ?? $parts['city_district']
            ?? ''
        ));
        $city = trim((string) (
            $parts['city']
            ?? $parts['town']
            ?? $parts['village']
            ?? $parts['municipality']
            ?? $parts['county']
            ?? ''
        ));

        $addressParts = array_values(array_filter([$street, $district, $city], static fn (string $v): bool => $v !== ''));
        $builtAddress = $addressParts !== [] ? implode(', ', $addressParts) : '';
        $displayName = trim((string) ($payload['display_name'] ?? ''));
        $country = trim((string) ($parts['country'] ?? 'Sénégal'));
        $postcode = trim((string) ($parts['postcode'] ?? ''));

        $fullAddress = $displayName !== ''
            ? $displayName
            : ($builtAddress !== ''
                ? ($builtAddress . ($country !== '' ? ', ' . $country : ''))
                : sprintf('%.8F, %.8F', $latitude, $longitude));

        if ($fullAddress === '') {
            $fullAddress = sprintf('%.8F, %.8F', $latitude, $longitude);
        }

        $matchedCity = $this->matchCasamanceCity($city, $parts);
        $resolvedCity = $matchedCity !== '' ? $matchedCity : $city;

        $shortParts = array_values(array_filter([$street, $district, $resolvedCity], static fn (string $v): bool => $v !== ''));
        $shortAddress = $shortParts !== [] ? implode(', ', $shortParts) : '';
        if ($shortAddress !== '' && $country !== '') {
            $shortAddress .= ', ' . $country;
        }

        if ($shortAddress === '') {
            $shortAddress = sprintf('%.8F, %.8F', $latitude, $longitude);
        }

        return [
            'address' => $shortAddress,
            'full_address' => $fullAddress,
            'street' => $street,
            'district' => $district,
            'city' => $resolvedCity,
            'country' => $country,
            'postcode' => $postcode,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    /**
     * @return array{
     *   address: string,
     *   full_address: string,
     *   street: string,
     *   district: string,
     *   city: string,
     *   country: string,
     *   postcode: string,
     *   latitude: float,
     *   longitude: float
     * }|null
     */
    public function forward(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $url = 'https://nominatim.openstreetmap.org/search?q=' . rawurlencode($query)
            . '&format=json&addressdetails=1&accept-language=fr&limit=1';

        $body = $this->fetch($url);
        if ($body === null) {
            return null;
        }

        /** @var list<array<string, mixed>>|null $results */
        $results = json_decode($body, true);
        if (!is_array($results) || $results === []) {
            return null;
        }

        /** @var array<string, mixed> $payload */
        $payload = $results[0];
        $latitude = (float) ($payload['lat'] ?? 0);
        $longitude = (float) ($payload['lon'] ?? 0);

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        /** @var array<string, string> $parts */
        $parts = is_array($payload['address'] ?? null) ? $payload['address'] : [];

        $street = trim(($parts['house_number'] ?? '') . ' ' . ($parts['road'] ?? $parts['pedestrian'] ?? $parts['footway'] ?? $parts['residential'] ?? ''));
        $district = trim((string) (
            $parts['suburb']
            ?? $parts['neighbourhood']
            ?? $parts['quarter']
            ?? $parts['city_district']
            ?? ''
        ));
        $city = trim((string) (
            $parts['city']
            ?? $parts['town']
            ?? $parts['village']
            ?? $parts['municipality']
            ?? $parts['county']
            ?? ''
        ));

        $displayName = trim((string) ($payload['display_name'] ?? ''));
        $country = trim((string) ($parts['country'] ?? 'Sénégal'));
        $postcode = trim((string) ($parts['postcode'] ?? ''));
        $matchedCity = $this->matchCasamanceCity($city, $parts);

        return [
            'address' => $displayName !== '' ? $displayName : $query,
            'full_address' => $displayName !== '' ? $displayName : $query,
            'street' => $street,
            'district' => $district,
            'city' => $matchedCity !== '' ? $matchedCity : $city,
            'country' => $country,
            'postcode' => $postcode,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    private function fetch(string $url): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return null;
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_HTTPHEADER => [
                    'User-Agent: ZigImobilier/1.0 (https://zig-imobilier.local)',
                    'Accept: application/json',
                    'Accept-Language: fr',
                ],
            ]);

            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($body !== false && $status >= 200 && $status < 300) {
                return $body;
            }
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: ZigImobilier/1.0 (https://zig-imobilier.local)\r\nAccept: application/json\r\nAccept-Language: fr\r\n",
                'timeout' => 15,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);

        return $body !== false ? $body : null;
    }

    /**
     * @param array<string, string> $parts
     */
    private function matchCasamanceCity(string $city, array $parts): string
    {
        $candidates = array_filter([
            $city,
            $parts['city'] ?? '',
            $parts['town'] ?? '',
            $parts['village'] ?? '',
            $parts['municipality'] ?? '',
            $parts['county'] ?? '',
        ], static fn (string $value): bool => trim($value) !== '');

        foreach ($candidates as $candidate) {
            foreach (DestinationHelper::allCities() as $known) {
                if (mb_strtolower(trim($candidate)) === mb_strtolower($known)) {
                    return $known;
                }
            }
        }

        return '';
    }
}
