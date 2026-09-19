<?php

declare(strict_types=1);

namespace App\Helpers;

final class DestinationHelper
{
    /**
     * @return array<string, array{image: string, tagline: string, credit: string}>
     */
    public static function casamance(): array
    {
        return [
            'Ziguinchor' => [
                'image' => 'images/destinations/ziguinchor-800.jpg',
                'tagline' => 'Port fluvial & marchés colorés',
                'credit' => 'Wiki Loves Africa — pêcheurs de Ziguinchor',
            ],
            'Cap Skirring' => [
                'image' => 'images/destinations/cap-skirring-800.jpg',
                'tagline' => 'Plages paradisiaques de l\'Atlantique',
                'credit' => 'Plage de Cap Skirring — Wikimedia Commons',
            ],
            'Oussouye' => [
                'image' => 'images/destinations/oussouye-800.jpg',
                'tagline' => 'Culture diola & nature préservée',
                'credit' => 'Diembéreng, Oussouye — Wikimedia Commons',
            ],
            'Bignona' => [
                'image' => 'images/destinations/bignona-800.jpg',
                'tagline' => 'Porte d\'entrée de la Casamance',
                'credit' => 'Bignona, Casamance — Emile Badiane',
            ],
            'Sedhiou' => [
                'image' => 'images/destinations/sedhiou-800.jpg',
                'tagline' => 'Nature luxuriante & fleuve Casamance',
                'credit' => 'Casamance — Wikimedia Commons',
            ],
        ];
    }

    public static function image(string $city): string
    {
        $destinations = self::casamance();
        $fallback = 'images/destinations/cap-skirring-800.jpg';

        return asset($destinations[$city]['image'] ?? $fallback);
    }

    /**
     * @return list<string>
     */
    public static function priorityOrder(): array
    {
        return [
            'Cap Skirring',
            'Ziguinchor',
            'Oussouye',
            'Bignona',
            'Sedhiou',
            'Kafountine',
            'Diouloulou',
        ];
    }

    /**
     * @param array<int, string> $dbCities
     * @return array<int, array{city: string, image: string, tagline: string}>
     */
    public static function forGrid(array $dbCities): array
    {
        $catalog = self::casamance();
        $fallback = asset('images/destinations/cap-skirring-800.jpg');
        $dbCities = array_values(array_unique(array_filter(array_map(
            static fn ($city): string => trim((string) $city),
            $dbCities
        ))));
        $ordered = [];

        // Uniquement les villes qui ont réellement des annonces en base.
        foreach (self::priorityOrder() as $city) {
            if (!in_array($city, $dbCities, true)) {
                continue;
            }
            $meta = $catalog[$city] ?? null;
            $ordered[] = [
                'city' => $city,
                'image' => $meta ? asset($meta['image']) : $fallback,
                'tagline' => self::tagline($city),
            ];
        }

        $known = array_column($ordered, 'city');

        foreach ($dbCities as $city) {
            if (in_array($city, $known, true)) {
                continue;
            }
            $meta = $catalog[$city] ?? null;
            $ordered[] = [
                'city' => $city,
                'image' => $meta ? asset($meta['image']) : $fallback,
                'tagline' => self::tagline($city),
            ];
        }

        return $ordered;
    }

    /**
     * Grille complète des villes (accueil, /properties, /lands) — toujours la même liste.
     *
     * @return array<int, array{city: string, image: string, tagline: string}>
     */
    public static function forGridCatalog(): array
    {
        return self::forGrid(self::priorityOrder());
    }

    /**
     * Villes pour les listes déroulantes (même périmètre que le carrousel villes).
     *
     * @param array<int, string> $dbCities
     * @return list<string>
     */
    public static function cityFilterOptions(array $dbCities): array
    {
        $dbCities = array_values(array_unique(array_filter(array_map(
            static fn ($city): string => trim((string) $city),
            $dbCities
        ))));

        $ordered = self::priorityOrder();
        foreach ($dbCities as $city) {
            if (!in_array($city, $ordered, true)) {
                $ordered[] = $city;
            }
        }

        return $ordered;
    }

    public static function tagline(string $city): string
    {
        $key = self::taglineKey($city);
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return self::casamance()[$city]['tagline'] ?? __('city.tagline.default');
    }

    private static function taglineKey(string $city): string
    {
        $map = [
            'Ziguinchor' => 'city.tagline.ziguinchor',
            'Cap Skirring' => 'city.tagline.cap_skirring',
            'Oussouye' => 'city.tagline.oussouye',
            'Bignona' => 'city.tagline.bignona',
            'Sedhiou' => 'city.tagline.sedhiou',
            'Kafountine' => 'city.tagline.kafountine',
            'Diouloulou' => 'city.tagline.diouloulou',
        ];

        return $map[$city] ?? 'city.tagline.default';
    }

    /**
     * Quartiers et lieux recherchables en Casamance.
     *
     * @return list<array{name: string, city: string, keywords: list<string>}>
     */
    public static function placesCatalog(): array
    {
        return [
            ['name' => 'Centre-ville', 'city' => 'Ziguinchor', 'keywords' => ['centre', 'ville', 'saint maur', 'gaulle']],
            ['name' => 'Kandialang', 'city' => 'Ziguinchor', 'keywords' => ['kandialang', 'kandi']],
            ['name' => 'Nagor', 'city' => 'Ziguinchor', 'keywords' => ['nagor', 'lotissement']],
            ['name' => 'Santhiaba', 'city' => 'Ziguinchor', 'keywords' => ['santhiaba', 'santiaba']],
            ['name' => 'Boudody', 'city' => 'Ziguinchor', 'keywords' => ['boudody', 'boudodi']],
            ['name' => 'Tilène', 'city' => 'Ziguinchor', 'keywords' => ['tilene', 'tilene']],
            ['name' => 'Coobane', 'city' => 'Ziguinchor', 'keywords' => ['coobane', 'cobane']],
            ['name' => 'Lyndiane', 'city' => 'Ziguinchor', 'keywords' => ['lyndiane', 'lindiane', 'port']],
            ['name' => 'Bord de mer', 'city' => 'Cap Skirring', 'keywords' => ['plage', 'mer', 'ocean', 'skirring']],
            ['name' => 'Kabrousse', 'city' => 'Cap Skirring', 'keywords' => ['kabrousse', 'kabrous']],
            ['name' => 'Boucott-Diamaguène', 'city' => 'Cap Skirring', 'keywords' => ['boucott', 'diamaguene']],
            ['name' => 'Hôtel zone', 'city' => 'Cap Skirring', 'keywords' => ['hotel', 'resort', 'club']],
            ['name' => 'Centre', 'city' => 'Oussouye', 'keywords' => ['oussouye', 'centre']],
            ['name' => 'Diembéreng', 'city' => 'Oussouye', 'keywords' => ['diembereng', 'diembéreng']],
            ['name' => 'Mlomp', 'city' => 'Oussouye', 'keywords' => ['mlomp']],
            ['name' => 'Escale', 'city' => 'Bignona', 'keywords' => ['escale', 'bignona']],
            ['name' => 'Zone résidentielle', 'city' => 'Bignona', 'keywords' => ['residentiel', 'résidentiel']],
            ['name' => 'Diouloulou', 'city' => 'Bignona', 'keywords' => ['diouloulou', 'dioulou']],
            ['name' => 'Centre-ville', 'city' => 'Sedhiou', 'keywords' => ['sedhiou', 'centre']],
            ['name' => 'Boutoupa', 'city' => 'Sedhiou', 'keywords' => ['boutoupa', 'boutou']],
            ['name' => 'Tanaff', 'city' => 'Sedhiou', 'keywords' => ['tanaff']],
            ['name' => 'Kafountine', 'city' => 'Kafountine', 'keywords' => ['kafountine', 'kaf', 'peche', 'pêche']],
            ['name' => 'Abéné', 'city' => 'Kafountine', 'keywords' => ['abene', 'abéné', 'braise']],
            ['name' => 'Diouloulou', 'city' => 'Diouloulou', 'keywords' => ['diouloulou village']],
            ['name' => 'Centre', 'city' => 'Diouloulou', 'keywords' => ['diouloulou centre']],
        ];
    }

    /**
     * @return list<string>
     */
    public static function allCities(): array
    {
        return array_values(array_unique(array_merge(
            self::priorityOrder(),
            array_keys(self::casamance())
        )));
    }

    public static function isKnownCity(string $city): bool
    {
        $city = trim($city);
        if ($city === '') {
            return false;
        }

        foreach (self::allCities() as $known) {
            if (mb_strtolower($known) === mb_strtolower($city)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{q: string, city: string, district: string}
     */
    public static function parseSearchQuery(string $query, string $city = '', string $district = ''): array
    {
        $query = trim($query);
        $city = trim($city);
        $district = trim($district);

        if ($city !== '' || $district !== '') {
            return [
                'q' => $query,
                'city' => $city,
                'district' => $district,
            ];
        }

        if ($query === '') {
            return ['q' => '', 'city' => '', 'district' => ''];
        }

        if (preg_match('/^(.+),\s*(.+)$/u', $query, $matches)) {
            $first = trim($matches[1]);
            $second = trim($matches[2]);
            if (self::isKnownCity($second)) {
                return [
                    'q' => $first,
                    'city' => $second,
                    'district' => $first,
                ];
            }
            if (self::isKnownCity($first)) {
                return [
                    'q' => $second,
                    'city' => $first,
                    'district' => $second,
                ];
            }
        }

        if (self::isKnownCity($query)) {
            return [
                'q' => '',
                'city' => $query,
                'district' => '',
            ];
        }

        foreach (self::placesCatalog() as $place) {
            $name = (string) $place['name'];
            if (mb_strtolower($name) === mb_strtolower($query) && $name !== (string) $place['city']) {
                return [
                    'q' => $name,
                    'city' => (string) $place['city'],
                    'district' => $name,
                ];
            }
        }

        return [
            'q' => $query,
            'city' => '',
            'district' => '',
        ];
    }

    /**
     * Applique un quartier publié en base lorsque la requête correspond exactement.
     *
     * @param array{q: string, city: string, district: string} $parsed
     * @param array{name: string, city: string, label?: string}|null $published
     * @return array{q: string, city: string, district: string}
     */
    public static function applyPublishedDistrict(array $parsed, ?array $published): array
    {
        if ($published === null || $parsed['district'] !== '') {
            return $parsed;
        }

        $query = trim($parsed['q']);
        if ($query === '') {
            return $parsed;
        }

        $queryLower = mb_strtolower($query);
        $nameLower = mb_strtolower($published['name']);
        $labelLower = mb_strtolower($published['label'] ?? ($published['name'] . ', ' . $published['city']));

        if ($queryLower !== $nameLower && $queryLower !== $labelLower) {
            return $parsed;
        }

        return [
            'q' => '',
            'city' => $published['city'],
            'district' => $published['name'],
        ];
    }

    /**
     * @return list<array{name: string, city: string, subtitle: string, label: string, kind: string}>
     */
    public static function searchCities(string $query, int $limit = 6): array
    {
        $query = mb_strtolower(trim($query));
        if (mb_strlen($query) < 2) {
            return [];
        }

        $matches = [];
        foreach (self::allCities() as $city) {
            $cityLower = mb_strtolower($city);
            if (
                $cityLower === $query
                || str_starts_with($cityLower, $query)
                || mb_strpos($cityLower, $query) !== false
            ) {
                $matches[mb_strtolower($city)] = [
                    'name' => $city,
                    'city' => $city,
                    'subtitle' => 'Casamance, Sénégal',
                    'label' => $city,
                    'kind' => 'city',
                ];
            }
        }

        usort($matches, static function (array $a, array $b) use ($query): int {
            $aStarts = str_starts_with(mb_strtolower($a['name']), $query) ? 0 : 1;
            $bStarts = str_starts_with(mb_strtolower($b['name']), $query) ? 0 : 1;
            if ($aStarts !== $bStarts) {
                return $aStarts <=> $bStarts;
            }

            return strcmp($a['name'], $b['name']);
        });

        return array_slice(array_values($matches), 0, $limit);
    }

    /**
     * @return list<array{name: string, city: string, subtitle: string, label: string, kind: string}>
     */
    public static function searchPlaces(string $query, int $limit = 8): array
    {
        $query = mb_strtolower(trim($query));
        if (mb_strlen($query) < 2) {
            return [];
        }

        $matches = [];

        foreach (self::placesCatalog() as $place) {
            $name = (string) $place['name'];
            $city = (string) $place['city'];
            $keywords = $place['keywords'] ?? [];
            $terms = array_merge([$name, $city], $keywords);
            $matched = false;

            foreach ($terms as $term) {
                $termLower = mb_strtolower($term);
                if ($termLower === $query || str_starts_with($termLower, $query) || mb_strpos($termLower, $query) !== false) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                continue;
            }

            $key = mb_strtolower($name . '|' . $city);
            if (isset($matches[$key])) {
                continue;
            }

            $matches[$key] = [
                'name' => $name,
                'city' => $city,
                'subtitle' => $city . ', Casamance',
                'label' => $name === $city ? $city : $name . ', ' . $city,
                'kind' => $name === $city ? 'city' : 'district',
            ];
        }

        $results = array_values($matches);
        usort($results, static function (array $a, array $b) use ($query): int {
            $aName = mb_strtolower($a['name']);
            $bName = mb_strtolower($b['name']);
            $aCity = mb_strtolower($a['city']);
            $bCity = mb_strtolower($b['city']);
            $aStarts = str_starts_with($aName, $query) || str_starts_with($aCity, $query) ? 0 : 1;
            $bStarts = str_starts_with($bName, $query) || str_starts_with($bCity, $query) ? 0 : 1;
            if ($aStarts !== $bStarts) {
                return $aStarts <=> $bStarts;
            }

            return strcmp($a['name'], $b['name']);
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * Quartiers Casamance pour autocomplétion (catalogue + annonces publiées).
     *
     * @return list<array{name: string, city: string, subtitle: string, label: string, kind: string}>
     */
    public static function searchDistricts(string $query, string $city = '', int $limit = 12): array
    {
        $query = mb_strtolower(trim($query));
        $city = trim($city);

        if ($query === '' && $city !== '') {
            return self::districtsForCity($city, $limit);
        }

        if (mb_strlen($query) < 1) {
            return [];
        }

        $catalog = [];
        foreach (self::placesCatalog() as $place) {
            $name = (string) $place['name'];
            $placeCity = (string) $place['city'];
            if (!self::isKnownCity($placeCity) || mb_strtolower($name) === mb_strtolower($placeCity)) {
                continue;
            }
            if ($city !== '' && mb_strtolower($placeCity) !== mb_strtolower($city)) {
                continue;
            }

            $terms = array_merge([$name, $placeCity], $place['keywords'] ?? []);
            $matched = false;
            foreach ($terms as $term) {
                $termLower = mb_strtolower((string) $term);
                if ($termLower === $query || str_starts_with($termLower, $query) || mb_strpos($termLower, $query) !== false) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                continue;
            }

            $key = mb_strtolower($name . '|' . $placeCity);
            $catalog[$key] = [
                'name' => $name,
                'city' => $placeCity,
                'subtitle' => $placeCity . ', Casamance',
                'label' => $name . ', ' . $placeCity,
                'kind' => 'district',
            ];
        }
        $catalog = array_values($catalog);

        $published = (new \App\Models\Property())->suggestDistricts($query, $limit * 2, $city);
        $merged = [];

        foreach (array_merge($published, $catalog) as $place) {
            if (($place['kind'] ?? 'district') !== 'district') {
                continue;
            }

            $placeCity = (string) ($place['city'] ?? '');
            if (!self::isKnownCity($placeCity)) {
                continue;
            }

            if ($city !== '' && mb_strtolower($placeCity) !== mb_strtolower($city)) {
                continue;
            }

            $name = (string) ($place['name'] ?? '');
            if ($name === '' || mb_strtolower($name) === mb_strtolower($placeCity)) {
                continue;
            }

            $key = mb_strtolower($name . '|' . $placeCity);
            $merged[$key] = [
                'name' => $name,
                'city' => $placeCity,
                'subtitle' => $placeCity . ', Casamance',
                'label' => $name . ', ' . $placeCity,
                'kind' => 'district',
            ];
        }

        $results = array_values($merged);
        usort($results, static function (array $a, array $b) use ($query): int {
            $aName = mb_strtolower($a['name']);
            $bName = mb_strtolower($b['name']);
            $aStarts = str_starts_with($aName, $query) ? 0 : 1;
            $bStarts = str_starts_with($bName, $query) ? 0 : 1;
            if ($aStarts !== $bStarts) {
                return $aStarts <=> $bStarts;
            }

            return strcmp($a['name'], $b['name']);
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * @return list<array{name: string, city: string, subtitle: string, label: string, kind: string}>
     */
    public static function districtsForCity(string $city, int $limit = 12): array
    {
        $city = trim($city);
        if ($city === '' || !self::isKnownCity($city)) {
            return [];
        }

        $cityLower = mb_strtolower($city);
        $results = [];

        foreach (self::placesCatalog() as $place) {
            $name = (string) $place['name'];
            $placeCity = (string) $place['city'];
            if (mb_strtolower($placeCity) !== $cityLower || mb_strtolower($name) === $cityLower) {
                continue;
            }

            $key = mb_strtolower($name . '|' . $placeCity);
            $results[$key] = [
                'name' => $name,
                'city' => $placeCity,
                'subtitle' => $placeCity . ', Casamance',
                'label' => $name . ', ' . $placeCity,
                'kind' => 'district',
            ];
        }

        $published = (new \App\Models\Property())->suggestDistricts('', 20, $city);
        foreach ($published as $place) {
            $name = (string) ($place['name'] ?? '');
            $placeCity = (string) ($place['city'] ?? '');
            if ($name === '' || mb_strtolower($name) === mb_strtolower($placeCity)) {
                continue;
            }

            $key = mb_strtolower($name . '|' . $placeCity);
            $results[$key] = [
                'name' => $name,
                'city' => $placeCity,
                'subtitle' => $placeCity . ', Casamance',
                'label' => $name . ', ' . $placeCity,
                'kind' => 'district',
            ];
        }

        $list = array_values($results);
        usort($list, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return array_slice($list, 0, $limit);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function profile(string $city): ?array
    {
        $profiles = self::profiles();

        if (isset($profiles[$city])) {
            $profiles[$city]['tagline'] = self::tagline($city);

            return $profiles[$city];
        }

        $catalog = self::casamance();
        if (!isset($catalog[$city])) {
            return null;
        }

        $profile = self::defaultProfile($city, $catalog[$city]);
        $profile['tagline'] = self::tagline($city);

        return $profile;
    }

    /**
     * @param array{image: string, tagline: string, credit: string} $meta
     * @return array<string, mixed>
     */
    private static function defaultProfile(string $city, array $meta): array
    {
        $heroImage = $meta['image'];

        return self::mergeProfile([
            'city' => $city,
            'region' => 'Casamance · Sénégal',
            'hero_image' => $heroImage,
            'hero_credit' => $meta['credit'],
            'tagline' => $meta['tagline'],
            'booking_lead' => 'Logements meublés et locations en Casamance',
            'cta_text' => 'Réservez votre logement et vivez la Casamance autrement.',
            'lands_lead' => 'Terrains disponibles dans la région',
            'quick_facts' => [
                ['icon' => 'bi-house-door', 'label' => 'Meublé'],
                ['icon' => 'bi-geo-alt', 'label' => 'Casamance'],
                ['icon' => 'bi-calendar-check', 'label' => 'Longue durée'],
                ['icon' => 'bi-shield-check', 'label' => 'Annonces vérifiées'],
            ],
            'stats' => [
                ['icon' => 'bi-sun', 'value' => '28°C', 'label' => 'Climat tropical'],
                ['icon' => 'bi-geo-alt', 'value' => $city, 'label' => 'Destination'],
                ['icon' => 'bi-house', 'value' => 'Dispo', 'label' => 'Logements'],
                ['icon' => 'bi-calendar-check', 'value' => 'Oct–Mai', 'label' => 'Saison recommandée'],
            ],
            'spots' => [],
            'tips' => [
                'Comparez les annonces et contactez le propriétaire pour visiter.',
                'Privilégiez les logements proches de vos points d\'intérêt.',
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function mergeProfile(array $data): array
    {
        return array_merge([
            'filters' => self::defaultFilters(),
        ], $data);
    }

    /**
     * @return list<array{label: string, type: string}>
     */
    private static function defaultFilters(): array
    {
        return [
            ['label' => 'Tous', 'type' => ''],
            ['label' => 'Villas', 'type' => 'villa'],
            ['label' => 'Appartements', 'type' => 'appartement'],
            ['label' => 'Studios', 'type' => 'studio'],
            ['label' => 'Maisons', 'type' => 'maison'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function profiles(): array
    {
        return [
            'Cap Skirring' => self::mergeProfile([
                'city' => 'Cap Skirring',
                'region' => 'Basse-Casamance · Oussouye',
                'hero_image' => 'images/destinations/cap-skirring-800.jpg',
                'hero_credit' => 'Plage de Cap Skirring — Wikimedia Commons',
                'tagline' => 'Plages paradisiaques de l\'Atlantique',
                'booking_lead' => 'Villas et appartements meublés au bord de l\'océan',
                'cta_text' => 'Réservez votre villa ou appartement en bord de mer.',
                'lands_lead' => 'Investissez dans la perle balnéaire de la Casamance',
                'quick_facts' => [
                    ['icon' => 'bi-umbrella', 'label' => 'Proche plage'],
                    ['icon' => 'bi-house-door', 'label' => 'Meublé'],
                    ['icon' => 'bi-airplane', 'label' => 'Aéroport CSK'],
                    ['icon' => 'bi-calendar-check', 'label' => 'Nov–Mai'],
                ],
                'stats' => [
                    ['icon' => 'bi-sun', 'value' => '28°C', 'label' => 'Température moyenne'],
                    ['icon' => 'bi-airplane', 'value' => 'CSK', 'label' => 'Aéroport international'],
                    ['icon' => 'bi-water', 'value' => '17 km', 'label' => 'De côtes sauvages'],
                    ['icon' => 'bi-calendar-check', 'value' => 'Nov–Mai', 'label' => 'Saison sèche idéale'],
                ],
                'spots' => [
                    ['name' => 'Plage de Cap Skirring', 'desc' => 'Baignade et promenades au coucher du soleil.', 'distance' => 'Centre'],
                    ['name' => 'Kabrousse', 'desc' => 'Village de pêcheurs et plage tranquille.', 'distance' => '5 km'],
                    ['name' => 'Oussouye', 'desc' => 'Marchés diola et accès aux bolongs.', 'distance' => '25 km'],
                    ['name' => 'Réserve de Basse-Casamance', 'desc' => 'Forêt dense et faune riche.', 'distance' => '30 km'],
                ],
                'tips' => [
                    'Réservez tôt en haute saison (déc.–fév.) : les villas partent vite.',
                    'Choisissez proche plage ou aéroport selon votre transport.',
                    'Goûtez le poisson braisé dans les campements locaux.',
                ],
            ]),
            'Ziguinchor' => self::mergeProfile([
                'city' => 'Ziguinchor',
                'region' => 'Capitale de la Casamance',
                'hero_image' => 'images/destinations/ziguinchor-800.jpg',
                'hero_credit' => 'Wiki Loves Africa — pêcheurs de Ziguinchor',
                'tagline' => 'Port fluvial & marchés colorés',
                'booking_lead' => 'Appartements meublés et studios en centre-ville',
                'cta_text' => 'Louez un appartement meublé au cœur de la Casamance.',
                'lands_lead' => 'Terrains à bâtir et parcelles en centre-ville',
                'quick_facts' => [
                    ['icon' => 'bi-building', 'label' => 'Centre-ville'],
                    ['icon' => 'bi-shop', 'label' => 'Marchés'],
                    ['icon' => 'bi-airplane', 'label' => 'Aéroport ZIG'],
                    ['icon' => 'bi-water', 'label' => 'Fleuve'],
                ],
                'stats' => [
                    ['icon' => 'bi-sun', 'value' => '30°C', 'label' => 'Température moyenne'],
                    ['icon' => 'bi-airplane', 'value' => 'ZIG', 'label' => 'Aéroport régional'],
                    ['icon' => 'bi-water', 'value' => 'Casamance', 'label' => 'Fleuve & port'],
                    ['icon' => 'bi-calendar-check', 'value' => 'Oct–Mai', 'label' => 'Saison sèche'],
                ],
                'spots' => [
                    ['name' => 'Marché Saint-Maur', 'desc' => 'Le grand marché couvert, épices et artisanat local.', 'distance' => 'Centre'],
                    ['name' => 'Embarcadère pirogues', 'desc' => 'Traversées vers les îles et balades sur le fleuve.', 'distance' => '2 km'],
                    ['name' => 'Avenue Général de Gaulle', 'desc' => 'Artères commerçantes et restaurants.', 'distance' => 'Centre'],
                    ['name' => 'Île de Carabane', 'desc' => 'Excursion en pirogue, plages et ruines coloniales.', 'distance' => '60 km'],
                ],
                'tips' => [
                    'Privilégiez un logement près du centre pour les commerces et transports.',
                    'Les studios conviennent aux séjours professionnels courts.',
                    'Visitez le marché tôt le matin pour l\'ambiance authentique.',
                ],
                'filters' => [
                    ['label' => 'Tous', 'type' => ''],
                    ['label' => 'Appartements', 'type' => 'appartement'],
                    ['label' => 'Studios', 'type' => 'studio'],
                    ['label' => 'Chambres', 'type' => 'chambre'],
                    ['label' => 'Maisons', 'type' => 'maison'],
                ],
            ]),
            'Oussouye' => self::mergeProfile([
                'city' => 'Oussouye',
                'region' => 'Pays Bassari · Basse-Casamance',
                'hero_image' => 'images/destinations/oussouye-800.jpg',
                'hero_credit' => 'Diembéreng, Oussouye — Wikimedia Commons',
                'tagline' => 'Culture diola & nature préservée',
                'booking_lead' => 'Chambres et maisons au cœur du pays Bassari',
                'cta_text' => 'Séjournez entre forêt sacrée et bolongs de Casamance.',
                'lands_lead' => 'Terrains dans le pays Bassari',
                'quick_facts' => [
                    ['icon' => 'bi-tree', 'label' => 'Forêt sacrée'],
                    ['icon' => 'bi-people', 'label' => 'Culture diola'],
                    ['icon' => 'bi-water', 'label' => 'Bolongs'],
                    ['icon' => 'bi-umbrella', 'label' => '25 km plage'],
                ],
                'stats' => [
                    ['icon' => 'bi-sun', 'value' => '27°C', 'label' => 'Température moyenne'],
                    ['icon' => 'bi-tree', 'value' => 'Fromager', 'label' => 'Forêt sacrée'],
                    ['icon' => 'bi-water', 'value' => 'Bolongs', 'label' => 'Mangroves & pirogues'],
                    ['icon' => 'bi-signpost', 'value' => '25 km', 'label' => 'Cap Skirring'],
                ],
                'spots' => [
                    ['name' => 'Forêt du fromager sacré', 'desc' => 'Arbre centenaire vénéré, cœur spirituel diola.', 'distance' => 'Centre'],
                    ['name' => 'Marché d\'Oussouye', 'desc' => 'Produits locaux, poissons fumés et artisanat.', 'distance' => 'Centre'],
                    ['name' => 'Bolong de Kalissaye', 'desc' => 'Balade en pirogue entre mangroves.', 'distance' => '8 km'],
                    ['name' => 'Cap Skirring', 'desc' => 'Plages atlantiques à une courte route.', 'distance' => '25 km'],
                ],
                'tips' => [
                    'Respectez les us et coutumes autour de la forêt sacrée.',
                    'Idéal pour un séjour calme avant d\'aller à la plage.',
                    'Réservez une chambre chez l\'habitant pour l\'immersion locale.',
                ],
                'filters' => [
                    ['label' => 'Tous', 'type' => ''],
                    ['label' => 'Chambres', 'type' => 'chambre'],
                    ['label' => 'Maisons', 'type' => 'maison'],
                    ['label' => 'Villas', 'type' => 'villa'],
                    ['label' => 'Studios', 'type' => 'studio'],
                ],
            ]),
            'Bignona' => self::mergeProfile([
                'city' => 'Bignona',
                'region' => 'Haute-Casamance · Porte d\'entrée',
                'hero_image' => 'images/destinations/bignona-800.jpg',
                'hero_credit' => 'Bignona, Casamance — Emile Badiane',
                'tagline' => 'Porte d\'entrée de la Casamance',
                'booking_lead' => 'Maisons et appartements pour location mensuelle',
                'cta_text' => 'Installez-vous à Bignona, carrefour vers toute la Casamance.',
                'lands_lead' => 'Terrains résidentiels en zone en développement',
                'quick_facts' => [
                    ['icon' => 'bi-signpost-split', 'label' => 'Carrefour'],
                    ['icon' => 'bi-shop', 'label' => 'Marché'],
                    ['icon' => 'bi-car-front', 'label' => '45 km ZIG'],
                    ['icon' => 'bi-house', 'label' => 'Longue durée'],
                ],
                'stats' => [
                    ['icon' => 'bi-sun', 'value' => '32°C', 'label' => 'Température moyenne'],
                    ['icon' => 'bi-signpost-split', 'value' => 'RN6', 'label' => 'Route principale'],
                    ['icon' => 'bi-geo-alt', 'value' => '45 km', 'label' => 'De Ziguinchor'],
                    ['icon' => 'bi-calendar-check', 'value' => 'Toute l\'année', 'label' => 'Location mensuelle'],
                ],
                'spots' => [
                    ['name' => 'Marché de Bignona', 'desc' => 'Artisanat, fruits tropicaux et vie locale.', 'distance' => 'Centre'],
                    ['name' => 'Quartier Escale', 'desc' => 'Zone résidentielle calme, idéale pour s\'installer.', 'distance' => '2 km'],
                    ['name' => 'Route vers Ziguinchor', 'desc' => 'Accès rapide à la capitale régionale.', 'distance' => '45 km'],
                    ['name' => 'Diouloulou', 'desc' => 'Villages verdoyants et rizières alentour.', 'distance' => '15 km'],
                ],
                'tips' => [
                    'Parfait pour une location longue durée à prix abordable.',
                    'Vérifiez l\'accès route et eau pour les maisons en périphérie.',
                    'Point de départ idéal pour explorer toute la Casamance.',
                ],
                'filters' => [
                    ['label' => 'Tous', 'type' => ''],
                    ['label' => 'Maisons', 'type' => 'maison'],
                    ['label' => 'Appartements', 'type' => 'appartement'],
                    ['label' => 'Studios', 'type' => 'studio'],
                    ['label' => 'Villas', 'type' => 'villa'],
                ],
            ]),
            'Sedhiou' => self::mergeProfile([
                'city' => 'Sedhiou',
                'region' => 'Haute-Casamance · Fleuve Casamance',
                'hero_image' => 'images/destinations/sedhiou-800.jpg',
                'hero_credit' => 'Casamance — Wikimedia Commons',
                'tagline' => 'Nature luxuriante & fleuve Casamance',
                'booking_lead' => 'Logements et terrains le long du fleuve',
                'cta_text' => 'Trouvez un logement au fil de l\'eau en Haute-Casamance.',
                'lands_lead' => 'Parcelles agricoles et terrains riverains',
                'quick_facts' => [
                    ['icon' => 'bi-water', 'label' => 'Fleuve'],
                    ['icon' => 'bi-tree', 'label' => 'Nature'],
                    ['icon' => 'bi-cash-stack', 'label' => 'Terrains'],
                    ['icon' => 'bi-house', 'label' => 'Calme'],
                ],
                'stats' => [
                    ['icon' => 'bi-sun', 'value' => '31°C', 'label' => 'Température moyenne'],
                    ['icon' => 'bi-water', 'value' => 'Fleuve', 'label' => 'Casamance'],
                    ['icon' => 'bi-geo-alt', 'value' => '80 km', 'label' => 'De Ziguinchor'],
                    ['icon' => 'bi-calendar-check', 'value' => 'Oct–Juin', 'label' => 'Saison agricole'],
                ],
                'spots' => [
                    ['name' => 'Bords du fleuve Casamance', 'desc' => 'Promenades et pêche traditionnelle.', 'distance' => 'Centre'],
                    ['name' => 'Marché de Sedhiou', 'desc' => 'Produits du fleuve et du bush.', 'distance' => 'Centre'],
                    ['name' => 'Villages de Boutoupa', 'desc' => 'Campagne verdoyante et rizières.', 'distance' => '10 km'],
                    ['name' => 'Forêt de Haute-Casamance', 'desc' => 'Végétation dense et sentiers nature.', 'distance' => '20 km'],
                ],
                'tips' => [
                    'Intéressant pour investir dans un terrain agricole ou résidentiel.',
                    'Vérifiez l\'accès au fleuve pour les parcelles riveraines.',
                    'Environnement calme, loin de l\'affluence touristique.',
                ],
                'filters' => [
                    ['label' => 'Tous', 'type' => ''],
                    ['label' => 'Maisons', 'type' => 'maison'],
                    ['label' => 'Appartements', 'type' => 'appartement'],
                    ['label' => 'Studios', 'type' => 'studio'],
                    ['label' => 'Chambres', 'type' => 'chambre'],
                ],
            ]),
        ];
    }
}
