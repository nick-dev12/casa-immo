<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\NotFoundException;
use App\Helpers\AuthHelper;
use App\Helpers\DestinationHelper;
use App\Models\Booking;
use App\Models\Land;
use App\Models\Property;
use App\Models\PropertyAvailability;

final class PropertyController extends Controller
{
    public function index(): string
    {
        $model = new Property();
        $filters = [
            'q' => (string) $this->request->input('q', ''),
            'city' => (string) $this->request->input('city', ''),
            'district' => (string) $this->request->input('district', ''),
            'type' => (string) $this->request->input('type', ''),
            'guests' => (string) $this->request->input('guests', ''),
            'check_in' => (string) $this->request->input('check_in', ''),
            'check_out' => (string) $this->request->input('check_out', ''),
            'price_min' => (string) $this->request->input('price_min', ''),
            'price_max' => (string) $this->request->input('price_max', ''),
            'bedrooms' => (string) $this->request->input('bedrooms', ''),
        ];

        $parsedDestination = DestinationHelper::parseSearchQuery(
            trim((string) $filters['q']),
            (string) $filters['city'],
            (string) $filters['district']
        );
        if ($parsedDestination['district'] === '' && trim((string) $filters['q']) !== '') {
            $parsedDestination = DestinationHelper::applyPublishedDistrict(
                $parsedDestination,
                $model->findPublishedDistrict(trim((string) $filters['q']))
            );
        }
        $filters['q'] = $parsedDestination['q'];
        if ($filters['city'] === '') {
            $filters['city'] = $parsedDestination['city'];
        }
        if ($filters['district'] === '') {
            $filters['district'] = $parsedDestination['district'];
        }

        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $properties = $model->search($filters, $perPage, $offset);
        $total = $model->countSearch($filters);
        $totalPages = (int) max(1, ceil($total / $perPage));

        $destination = $filters['city'] !== ''
            ? DestinationHelper::profile($filters['city'])
            : null;

        $viewName = $destination !== null ? 'properties/destination' : 'properties/index';

        $lands = [];
        if ($destination !== null) {
            $landModel = new Land();
            $lands = $landModel->search(['city' => $filters['city']], 4, 0);
        }

        $cities = $model->getCities();
        $defaultCity = in_array('Ziguinchor', $cities, true)
            ? 'Ziguinchor'
            : ($cities[0] ?? config('app', 'default_city', 'Ziguinchor'));

        return $this->view($viewName, [
            'title' => $destination !== null
                ? __('properties.title_city', ['city' => $filters['city']])
                : __('properties.title'),
            'properties' => $properties,
            'filters' => $filters,
            'cities' => $cities,
            'propertyTypes' => localized_types(Property::types(), 'property_type'),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'currentCity' => $filters['city'] ?: $defaultCity,
            'destination' => $destination,
            'isDestination' => $destination !== null,
            'isPropertiesList' => $destination === null,
            'cityGrid' => DestinationHelper::forGridCatalog(),
            'lands' => $lands,
        ]);
    }

    public function show(string $id): string
    {
        $model = new Property();
        $property = $model->findById((int) $id);

        if ($property === null) {
            throw new NotFoundException('Logement introuvable.');
        }

        $images = $model->getGalleryImages((int) $property['id'], (string) $property['type']);
        $amenities = $model->getAmenities((int) $property['id']);
        $reviews = $model->getReviews((int) $property['id']);
        $owner = $model->getOwner((int) $property['owner_id']);

        $amenitiesByCategory = [];
        foreach ($amenities as $amenity) {
            $cat = (string) $amenity['category'];
            $amenitiesByCategory[$cat][] = $amenity;
        }

        $similarProperties = $model->getNearby(
            (string) $property['city'],
            6,
            [(int) $property['id']]
        );

        $propertyId = (int) $property['id'];
        $availabilityModel = new PropertyAvailability();
        $blockedMap = $availabilityModel->statusMap($propertyId);
        $blockedDates = [];
        foreach ($blockedMap as $date => $status) {
            if (in_array($status, ['blocked', 'maintenance'], true)) {
                $blockedDates[] = $date;
            }
        }
        $reservedDates = (new Booking())->reservedDatesForProperty($propertyId);
        $unavailableDates = array_values(array_unique(array_merge($blockedDates, $reservedDates)));
        sort($unavailableDates);

        $userId = AuthHelper::id();
        $ownerId = (int) ($property['owner_id'] ?? 0);
        $canViewHostContact = $owner !== null && $userId !== null && (
            $userId === $ownerId
            || (new Booking())->userHasActiveBookingForProperty($userId, $propertyId)
        );

        return $this->view('properties/show', [
            'title' => (string) $property['title'],
            'property' => $property,
            'images' => $images,
            'amenities' => $amenities,
            'amenitiesByCategory' => $amenitiesByCategory,
            'reviews' => $reviews,
            'owner' => $owner,
            'canViewHostContact' => $canViewHostContact,
            'currentCity' => (string) $property['city'],
            'similarProperties' => $similarProperties,
            'unavailableDates' => $unavailableDates,
        ], 'layouts/property');
    }
}
