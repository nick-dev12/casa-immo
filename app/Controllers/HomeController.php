<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\DestinationHelper;
use App\Models\Land;
use App\Models\Property;

final class HomeController extends Controller
{
    public function index(): string
    {
        $propertyModel = new Property();
        $landModel = new Land();
        $propertyCities = $propertyModel->getCities();

        $filters = [
            'q' => trim((string) $this->request->input('q', '')),
            'city' => (string) $this->request->input('city', ''),
            'district' => (string) $this->request->input('district', ''),
            'type' => (string) $this->request->input('type', ''),
            'adults' => (string) $this->request->input('adults', ''),
            'children' => (string) $this->request->input('children', ''),
            'pets' => (string) $this->request->input('pets', ''),
            'business' => (string) $this->request->input('business', ''),
            'rooms' => (string) $this->request->input('rooms', ''),
            'guests' => (string) $this->request->input('guests', ''),
            'check_in' => (string) $this->request->input('check_in', ''),
            'check_out' => (string) $this->request->input('check_out', ''),
            'date_flex' => (string) $this->request->input('date_flex', ''),
        ];

        $parsedDestination = DestinationHelper::parseSearchQuery(
            $filters['q'],
            $filters['city'],
            $filters['district']
        );
        if ($parsedDestination['district'] === '' && trim($filters['q']) !== '') {
            $parsedDestination = DestinationHelper::applyPublishedDistrict(
                $parsedDestination,
                $propertyModel->findPublishedDistrict(trim($filters['q']))
            );
        }
        $filters['q'] = $parsedDestination['q'];
        if ($filters['city'] === '') {
            $filters['city'] = $parsedDestination['city'];
        }
        if ($filters['district'] === '') {
            $filters['district'] = $parsedDestination['district'];
        }

        if ($filters['adults'] !== '' || $filters['children'] !== '' || $filters['guests'] !== '') {
            $adults = max(1, (int) ($filters['adults'] ?: $filters['guests'] ?: 2));
            $children = max(0, (int) $filters['children']);
            $filters['adults'] = (string) $adults;
            $filters['children'] = (string) $children;
            $filters['guests'] = (string) ($adults + $children);
        }

        $currentCity = $filters['city'] ?: (
            in_array('Ziguinchor', $propertyCities, true)
                ? 'Ziguinchor'
                : ($propertyCities[0] ?? config('app', 'default_city', 'Ziguinchor'))
        );
        $isSearching = $filters['q'] !== ''
            || $filters['type'] !== ''
            || $filters['city'] !== ''
            || $filters['district'] !== ''
            || $filters['check_in'] !== ''
            || $filters['check_out'] !== ''
            || $filters['pets'] !== ''
            || $filters['adults'] !== ''
            || $filters['children'] !== ''
            || $filters['guests'] !== '';

        if ($isSearching) {
            $recommended = $propertyModel->search($filters, 6);
            $nearby = [];
            $cityPropertyRows = [];
            $hasDateFilter = $filters['check_in'] !== '' && $filters['check_out'] !== '';
            $otherProperties = $hasDateFilter ? [] : $propertyModel->getRecommended(6);
        } else {
            $recommended = $propertyModel->getRecommended(6);

            $cityOrder = array_values(array_unique(array_merge(
                DestinationHelper::priorityOrder(),
                $propertyCities
            )));
            $cityPropertyRows = array_values(array_filter(
                array_map(
                    static fn (array $row): array => array_merge($row, [
                        'tagline' => DestinationHelper::tagline($row['city']),
                    ]),
                    $propertyModel->getPropertiesByCities($cityOrder, 5, [])
                ),
                static fn (array $row): bool => !empty($row['properties'])
            ));
            $nearby = [];
            $otherProperties = [];
        }

        $lands = $landModel->getPopular(8);
        $landCities = $landModel->getCities();

        $cities = array_values(array_unique(array_merge(
            $propertyCities,
            $landCities
        )));
        sort($cities);

        return $this->view('home/index', [
            'title' => __('home.title'),
            'isHome' => true,
            'appName' => config('app', 'name'),
            'authSuccess' => \App\Core\Session::getFlash('auth_success'),
            'propertyCount' => $propertyModel->countSearch([]),
            'landCount' => $landModel->countSearch([]),
            'recommended' => $recommended,
            'nearby' => $nearby,
            'cityPropertyRows' => $cityPropertyRows ?? [],
            'lands' => $lands,
            'currentCity' => $currentCity,
            'cities' => $cities,
            'propertyTypes' => localized_types(Property::types(), 'property_type'),
            'landTypes' => localized_types(Land::types(), 'land_type'),
            'filters' => $filters,
            'isSearching' => $isSearching,
            'otherProperties' => $otherProperties,
            'cityGrid' => DestinationHelper::forGridCatalog(),
        ]);
    }
}
