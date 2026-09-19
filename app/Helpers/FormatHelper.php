<?php

declare(strict_types=1);

namespace App\Helpers;

final class FormatHelper
{
    /** @var array<string, string> */
    private static array $propertyFallbackImages = [
        'appartement' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&h=400&fit=crop',
        'studio' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=600&h=400&fit=crop',
        'villa' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=600&h=400&fit=crop',
        'maison' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&h=400&fit=crop',
        'chambre' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&h=400&fit=crop',
        'residence' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&h=400&fit=crop',
        'autre' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&h=400&fit=crop',
    ];

    /** @var array<string, string> */
    private static array $landFallbackImages = [
        'residentiel' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600&h=400&fit=crop',
        'commercial' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&h=400&fit=crop',
        'agricole' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=600&h=400&fit=crop',
        'industriel' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&h=400&fit=crop',
        'autre' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600&h=400&fit=crop',
    ];

    public static function money(float|int|string|null $amount, string $currency = 'XOF'): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }

        $value = (float) $amount;
        $formatted = number_format($value, 0, ',', ' ');

        $code = strtoupper(trim($currency));
        $displayCurrency = match ($code) {
            'XOF', 'CFA', '' => 'FCFA',
            default => $currency,
        };

        return $formatted . ' ' . $displayCurrency;
    }

    public static function propertyTypeLabel(string $type): string
    {
        $key = 'property_type.' . $type;
        $label = __($key);

        if ($label !== $key) {
            return $label;
        }

        return \App\Models\Property::types()[$type] ?? ucfirst($type);
    }

    public static function landTypeLabel(string $type): string
    {
        $key = 'land_type.' . $type;
        $label = __($key);

        if ($label !== $key) {
            return $label;
        }

        return \App\Models\Land::types()[$type] ?? ucfirst($type);
    }

    public static function landPaperTypeLabel(string $type): string
    {
        $key = 'land_paper_type.' . $type;
        $label = __($key);

        if ($label !== $key) {
            return $label;
        }

        return \App\Models\Land::paperTypes()[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    public static function landArea(float|int|string $area, string $unit): string
    {
        $value = rtrim(rtrim(number_format((float) $area, 2, ',', ' '), '0'), ',');

        return match ($unit) {
            'ha' => $value . ' ha',
            'are' => $value . ' ares',
            default => $value . ' m²',
        };
    }

    public static function landDimensions(float|int|string|null $length, float|int|string|null $width): string
    {
        $lengthVal = (float) $length;
        $widthVal = (float) $width;
        if ($lengthVal <= 0 || $widthVal <= 0) {
            return '';
        }

        $format = static fn (float $value): string => rtrim(rtrim(number_format($value, 2, ',', ' '), '0'), ',');

        return __('land.dimensions', [
            'length' => $format($lengthVal),
            'width' => $format($widthVal),
        ]);
    }

    public static function locationLine(array $item, string $country = 'Casamance'): string
    {
        $parts = array_filter([
            $item['city'] ?? null,
            $item['district'] ?? null,
        ]);

        $line = implode(', ', $parts);

        if ($line !== '' && !str_contains(strtolower($line), strtolower($country))) {
            $line .= ', ' . $country;
        }

        return $line ?: ($item['country'] ?? $country);
    }

    public static function ratingDisplay(float|int|string|null $rating): string
    {
        $value = (float) $rating;

        return $value > 0 ? number_format($value, 1, ',', '') : '—';
    }

    public static function propertyImage(?string $path, string $type = 'autre'): string
    {
        return self::resolveImage($path, self::$propertyFallbackImages[$type] ?? self::$propertyFallbackImages['autre']);
    }

    public static function landImage(?string $path, string $type = 'autre'): string
    {
        return self::resolveImage($path, self::$landFallbackImages[$type] ?? self::$landFallbackImages['autre']);
    }

    /**
     * Pool de photos de secours pour compléter une galerie (4 à 10 images).
     *
     * @return list<string>
     */
    public static function propertyGalleryPool(string $type): array
    {
        /** @var array<string, list<string>> $pools */
        $pools = [
            'appartement' => [
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=1200&h=800&fit=crop',
                'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600210492494-03fe3c5fbf0a?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop',
            ],
            'studio' => [
                'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=1200&h=800&fit=crop',
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600210492494-03fe3c5fbf0a?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop',
            ],
            'villa' => [
                'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1200&h=800&fit=crop',
                'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600210492494-03fe3c5fbf0a?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop',
            ],
            'maison' => [
                'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200&h=800&fit=crop',
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop',
            ],
            'chambre' => [
                'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=1200&h=800&fit=crop',
                'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600210492494-03fe3c5fbf0a?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop',
            ],
            'residence' => [
                'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&h=800&fit=crop',
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop',
            ],
        ];

        $pool = $pools[$type] ?? $pools['appartement'];

        return array_slice($pool, 0, \App\Models\Property::MAX_IMAGES);
    }

    private static function resolveImage(?string $path, string $fallback): string
    {
        if ($path === null || $path === '') {
            return $fallback;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $localPath = public_path(ltrim($path, '/'));

        if (is_readable($localPath)) {
            return url(ltrim($path, '/'));
        }

        return $fallback;
    }
}
