<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\DestinationHelper;
use App\Models\Land;
use App\Models\Property;

final class SearchController extends Controller
{
    public function suggest(): never
    {
        $query = trim((string) $this->request->input('q', ''));
        $type = (string) $this->request->input('type', 'properties');
        $city = trim((string) $this->request->input('city', ''));
        $scope = (string) $this->request->input('scope', '');

        if ($scope === 'districts') {
            $districts = DestinationHelper::searchDistricts($query, $city, 12);
            $this->json([
                'success' => true,
                'message' => 'OK',
                'data' => [
                    'districts' => $districts,
                ],
            ]);
        }

        if (mb_strlen($query) < 2) {
            $this->json([
                'success' => true,
                'message' => 'OK',
                'data' => [
                    'results' => [],
                    'places' => [],
                    'cities' => [],
                    'districts' => [],
                ],
            ]);
        }

        $propertyModel = new Property();
        $landModel = new Land();

        $raw = $type === 'lands'
            ? $landModel->suggest($query, $city, 6)
            : $propertyModel->suggest($query, $city, 6);

        $results = array_map(
            fn (array $row): array => $this->formatSuggestion($row, $type),
            $raw
        );

        $cities = $this->mergePlacesByKind(
            DestinationHelper::searchCities($query, 6),
            $propertyModel->suggestCities($query, 6),
            'city',
            6
        );

        $publishedDistricts = $propertyModel->suggestDistricts($query, 15, $city);
        $catalogDistricts = array_values(array_filter(
            DestinationHelper::searchDistricts($query, $city, 10),
            static fn (array $place): bool => ($place['kind'] ?? 'district') === 'district'
                && ($place['name'] ?? '') !== ($place['city'] ?? '')
        ));

        $districts = $this->mergePlacesByKind($publishedDistricts, $catalogDistricts, 'district', 15);

        $this->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'results' => $results,
                'places' => array_merge($cities, $districts),
                'cities' => $cities,
                'districts' => $districts,
            ],
        ]);
    }

    /**
     * @param list<array<string, mixed>> $primary
     * @param list<array<string, mixed>> $secondary
     * @return list<array<string, mixed>>
     */
    private function mergePlacesByKind(array $primary, array $secondary, string $kind, int $limit): array
    {
        $merged = [];

        foreach (array_merge($primary, $secondary) as $place) {
            $place['kind'] = $place['kind'] ?? $kind;
            $key = mb_strtolower(($place['kind'] ?? '') . '|' . ($place['name'] ?? '') . '|' . ($place['city'] ?? ''));
            $merged[$key] = $place;
        }

        return array_slice(array_values($merged), 0, $limit);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function formatSuggestion(array $row, string $type): array
    {
        $id = (int) $row['id'];
        $isLand = $type === 'lands';

        $rowType = $isLand
            ? (string) ($row['land_type'] ?? 'autre')
            : (string) ($row['type'] ?? 'autre');

        $location = location_line($row);
        $subtitle = $location;

        if (!$isLand && !empty($row['price_per_night'])) {
            $subtitle = money($row['price_per_night'], (string) ($row['price_currency'] ?? 'XOF')) . ' / nuit · ' . $location;
        } elseif ($isLand && !empty($row['price'])) {
            $subtitle = money($row['price'], (string) ($row['currency'] ?? 'XOF')) . ' · ' . $location;
        }

        return [
            'id' => $id,
            'title' => (string) $row['title'],
            'subtitle' => $subtitle,
            'city' => (string) ($row['city'] ?? ''),
            'district' => (string) ($row['district'] ?? ''),
            'type' => $rowType,
            'type_label' => $isLand ? land_type($rowType) : property_type($rowType),
            'url' => url($isLand ? '/lands/' . $id : '/properties/' . $id),
            'image' => $isLand
                ? land_image($row['primary_image'] ?? null, $rowType)
                : property_image($row['primary_image'] ?? null, $rowType),
        ];
    }
}
