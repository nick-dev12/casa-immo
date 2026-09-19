<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Helpers\AuthHelper;
use App\Models\Amenity;
use App\Models\Booking;
use App\Models\Establishment;
use App\Models\HostLand;
use App\Models\HostProperty;
use App\Models\Land;
use App\Models\Property;
use App\Models\ListingImage;
use App\Models\PropertyAvailability;
use App\Models\User;
use App\Services\ListingImageService;
use App\Services\ListingVideoService;

final class HostController extends Controller
{
    public function index(): string
    {
        [$userId, $establishment] = $this->requireEstablishment(false);
        if ($establishment === null) {
            $this->redirect(url('/host/setup'));
        }

        $dashboard = (new \App\Services\HostDashboardService())->forOwner($userId);

        return $this->hostView('host/index', [
            'title' => __('host.dashboard_title'),
            'pageTitle' => __('host.dashboard_title'),
            'establishment' => $establishment,
            'stats' => $dashboard['stats'],
            'recentBookings' => $dashboard['recent_bookings'],
            'user' => AuthHelper::user(),
            'success' => flash('host_success'),
            'error' => flash('host_error'),
        ]);
    }

    public function rejectBooking(string $id): never
    {
        [$userId, $establishment] = $this->requireEstablishment(false);
        if ($establishment === null) {
            $this->redirect(url('/host/setup'));
        }

        $bookingId = (int) $id;
        $bookingModel = new Booking();

        if (!$bookingModel->rejectForHost($bookingId, $userId)) {
            Session::flash('host_error', __('host.reject_booking_error'));
            $this->redirect(url('/host#host-bookings'));
        }

        (new \App\Services\BookingNotificationService())->notifyRejected($bookingId);

        Session::flash('host_success', __('host.reject_booking_success'));
        $this->redirect(url('/host#host-bookings'));
    }

    public function setup(): string
    {
        $userId = $this->requireAuth();
        $establishmentModel = new Establishment();
        $establishment = $establishmentModel->findByOwnerId($userId);

        if ($establishment !== null) {
            $this->redirect(url('/host'));
        }

        $user = AuthHelper::user();

        return $this->hostView('host/setup', [
            'title' => __('host.setup_title'),
            'pageTitle' => __('host.setup_title'),
            'user' => $user,
            'error' => flash('host_error'),
        ]);
    }

    public function storeSetup(): never
    {
        $userId = $this->requireAuth();
        $establishmentModel = new Establishment();

        if ($establishmentModel->findByOwnerId($userId) !== null) {
            $this->redirect(url('/host'));
        }

        $name = trim((string) $this->request->input('name', ''));
        $city = trim((string) $this->request->input('city', ''));

        if ($name === '' || $city === '') {
            Session::flash('host_error', __('host.error.required'));
            $this->redirect(url('/host/setup'));
        }

        $user = AuthHelper::user();
        $establishmentModel->create([
            'owner_id' => $userId,
            'name' => $name,
            'description' => '',
            'city' => $city,
            'district' => '',
            'address' => $city,
            'phone' => (string) ($user['phone'] ?? ''),
            'status' => 'active',
        ]);

        (new User())->assignRole($userId, 'owner');
        Session::flash('host_success', __('host.setup_success'));
        $this->redirect(url('/host'));
    }

    public function properties(): string
    {
        [$userId, $establishment] = $this->requireEstablishment();

        return $this->hostView('host/properties/index', [
            'title' => __('host.properties_title'),
            'establishment' => $establishment,
            'properties' => $this->attachPreviewImages((new HostProperty())->forOwner($userId), 'property'),
            'lands' => $this->attachPreviewImages((new HostLand())->forOwner($userId), 'land'),
            'success' => flash('host_success'),
        ]);
    }

    public function createProperty(): string
    {
        [, $establishment] = $this->requireEstablishment();
        $amenityModel = new Amenity();

        return $this->hostView('host/properties/form', array_merge([
            'title' => __('host.listing_new'),
            'pageTitle' => __('host.listing_new'),
            'establishment' => $establishment,
            'property' => null,
            'land' => null,
            'listingKind' => 'logement',
            'rentalPeriod' => 'nightly',
            'propertyTypes' => localized_types(Property::types(), 'property_type'),
            'landTypes' => localized_types(Land::types(), 'land_type'),
            'landPaperTypes' => localized_types(Land::paperTypes(), 'land_paper_type'),
            'amenitiesByCategory' => $amenityModel->groupedByCategory(),
            'selectedAmenityIds' => [],
            'error' => flash('host_error'),
        ], $this->imageViewData(null, null)));
    }

    public function storeProperty(): never
    {
        if ((string) $this->request->input('listing_kind', 'logement') === 'terrain') {
            $this->storeLand();
        }

        [$userId, $establishment] = $this->requireEstablishment();
        $data = $this->propertyInput();
        if ($data === null) {
            $this->redirect(url('/host/properties/new'));
        }
        $data = $this->withResolvedCoordinates($data, $establishment);

        $propertyId = (new HostProperty())->create($userId, (int) $establishment['id'], $data);
        (new Amenity())->syncForProperty($propertyId, $this->amenityInput());

        $imageError = $this->handlePropertyImages($propertyId);
        if ($imageError !== null) {
            Session::flash('host_error', $imageError);
            $this->redirect(url('/host/properties/' . $propertyId . '/edit'));
        }

        if ($this->wantsPublish()) {
            $publishError = $this->validateAndPublishProperty($userId, $propertyId);
            if ($publishError !== null) {
                $this->respondHostAction(false, $publishError, url('/host/properties/' . $propertyId . '/edit'));
            }

            $property = (new HostProperty())->findOwned($userId, $propertyId);
            $city = (string) ($property['city'] ?? config('app', 'default_city', 'Ziguinchor'));
            $publicUrl = url('/properties/' . $propertyId . '?city=' . urlencode($city));

            $this->respondHostAction(true, __('host.property_published'), $publicUrl);
        }

        if ($this->request->isAjax()) {
            $this->json([
                'success' => true,
                'redirect' => url('/host/properties/' . $propertyId . '/edit'),
                'listingId' => $propertyId,
            ]);
        }

        Session::flash('host_success', $this->saveSuccessMessage(__('host.property_created')));
        $this->redirect(url('/host/properties/' . $propertyId . '/edit'));
    }

    public function editProperty(string $id): string
    {
        [$userId, $establishment] = $this->requireEstablishment();
        $property = (new HostProperty())->findOwned($userId, (int) $id);
        if ($property === null) {
            $this->redirect(url('/host/properties'));
        }

        $amenityModel = new Amenity();

        return $this->hostView('host/properties/form', array_merge([
            'title' => __('host.property_edit'),
            'pageTitle' => __('host.property_edit'),
            'establishment' => $establishment,
            'property' => $property,
            'land' => null,
            'listingKind' => 'logement',
            'rentalPeriod' => (float) ($property['price_per_month'] ?? 0) > 0 && (float) ($property['price_per_night'] ?? 0) <= 0
                ? 'monthly'
                : 'nightly',
            'propertyTypes' => localized_types(Property::types(), 'property_type'),
            'landTypes' => localized_types(Land::types(), 'land_type'),
            'landPaperTypes' => localized_types(Land::paperTypes(), 'land_paper_type'),
            'amenitiesByCategory' => $amenityModel->groupedByCategory(),
            'selectedAmenityIds' => $amenityModel->idsForProperty((int) $property['id']),
            'success' => flash('host_success'),
            'error' => flash('host_error'),
        ], $this->imageViewData($property, null)));
    }

    public function editLand(string $id): string
    {
        [$userId, $establishment] = $this->requireEstablishment();
        $land = (new HostLand())->findOwned($userId, (int) $id);
        if ($land === null) {
            $this->redirect(url('/host/properties'));
        }

        return $this->hostView('host/properties/form', array_merge([
            'title' => __('host.land_edit'),
            'pageTitle' => __('host.land_edit'),
            'establishment' => $establishment,
            'property' => null,
            'land' => $land,
            'listingKind' => 'terrain',
            'rentalPeriod' => 'nightly',
            'propertyTypes' => localized_types(Property::types(), 'property_type'),
            'landTypes' => localized_types(Land::types(), 'land_type'),
            'landPaperTypes' => localized_types(Land::paperTypes(), 'land_paper_type'),
            'amenitiesByCategory' => [],
            'selectedAmenityIds' => [],
            'success' => flash('host_success'),
            'error' => flash('host_error'),
        ], $this->imageViewData(null, $land)));
    }

    public function storeLand(): never
    {
        [$userId, $establishment] = $this->requireEstablishment();
        $data = $this->landInput();
        if ($data === null) {
            $this->redirect(url('/host/properties/new'));
        }
        $data = $this->withResolvedCoordinates($data, $establishment);

        $landId = (new HostLand())->create($userId, $data);

        $imageError = $this->handleLandImages($landId);
        if ($imageError !== null) {
            Session::flash('host_error', $imageError);
            $this->redirect(url('/host/lands/' . $landId . '/edit'));
        }

        if ($this->wantsPublish()) {
            $publishError = $this->validateAndPublishLand($userId, $landId);
            if ($publishError !== null) {
                $this->respondHostAction(false, $publishError, url('/host/lands/' . $landId . '/edit'));
            }

            $land = (new HostLand())->findOwned($userId, $landId);
            $city = (string) ($land['city'] ?? config('app', 'default_city', 'Ziguinchor'));
            $publicUrl = url('/lands/' . $landId . '?city=' . urlencode($city));

            $this->respondHostAction(true, __('host.land_published'), $publicUrl);
        }

        if ($this->request->isAjax()) {
            $this->json([
                'success' => true,
                'redirect' => url('/host/lands/' . $landId . '/edit'),
                'listingId' => $landId,
            ]);
        }

        Session::flash('host_success', $this->saveSuccessMessage(__('host.land_created')));
        $this->redirect(url('/host/lands/' . $landId . '/edit'));
    }

    public function updateLand(string $id): never
    {
        [$userId, $establishment] = $this->requireEstablishment();
        $landId = (int) $id;
        $hostLand = new HostLand();
        if ($hostLand->findOwned($userId, $landId) === null) {
            $this->redirect(url('/host/properties'));
        }

        $data = $this->landInput();
        if ($data === null) {
            $this->redirect(url('/host/lands/' . $landId . '/edit'));
        }
        $data = $this->withResolvedCoordinates($data, $establishment);

        $hostLand->update($landId, $data);

        $imageError = $this->handleLandImages($landId);
        if ($imageError !== null) {
            Session::flash('host_error', $imageError);
            $this->redirect(url('/host/lands/' . $landId . '/edit'));
        }

        Session::flash('host_success', $this->saveSuccessMessage(__('host.land_updated')));
        $this->redirect(url('/host/lands/' . $landId . '/edit'));
    }

    public function publishLand(string $id): never
    {
        [$userId, $establishment] = $this->requireEstablishment();
        $landId = (int) $id;
        $editUrl = url('/host/lands/' . $landId . '/edit');
        $hostLand = new HostLand();
        $land = $hostLand->findOwned($userId, $landId);
        if ($land === null) {
            $this->respondHostAction(false, __('host.error.not_found'), url('/host/properties'));
        }

        $data = $this->landInput();
        if ($data === null) {
            $this->respondHostAction(false, __('host.error.required'), $editUrl);
        }
        $data = $this->withResolvedCoordinates($data, $establishment);

        $hostLand->update($landId, $data);
        $land = $hostLand->findOwned($userId, $landId);

        $imageError = $this->handleLandImages($landId);
        if ($imageError !== null) {
            $this->respondHostAction(false, $imageError, $editUrl);
        }

        $publishError = $this->validateAndPublishLand($userId, $landId);
        if ($publishError !== null) {
            $this->respondHostAction(false, $publishError, $editUrl);
        }

        $city = (string) ($land['city'] ?? config('app', 'default_city', 'Ziguinchor'));
        $publicUrl = url('/lands/' . $landId . '?city=' . urlencode($city));

        $this->respondHostAction(true, __('host.land_published'), $publicUrl);
    }

    public function updateProperty(string $id): never
    {
        [$userId, $establishment] = $this->requireEstablishment();
        $propertyId = (int) $id;
        $hostProperty = new HostProperty();
        if ($hostProperty->findOwned($userId, $propertyId) === null) {
            $this->redirect(url('/host/properties'));
        }

        $data = $this->propertyInput();
        if ($data === null) {
            $this->redirect(url('/host/properties/' . $propertyId . '/edit'));
        }
        $data = $this->withResolvedCoordinates($data, $establishment);

        $hostProperty->update($propertyId, $data);
        (new Amenity())->syncForProperty($propertyId, $this->amenityInput());

        $imageError = $this->handlePropertyImages($propertyId);
        if ($imageError !== null) {
            Session::flash('host_error', $imageError);
            $this->redirect(url('/host/properties/' . $propertyId . '/edit'));
        }

        Session::flash('host_success', $this->saveSuccessMessage(__('host.property_updated')));
        $this->redirect(url('/host/properties/' . $propertyId . '/edit'));
    }

    public function uploadPropertyImages(string $id): never
    {
        [$userId, $establishment] = $this->requireEstablishment();
        $propertyId = (int) $id;

        if ((new HostProperty())->findOwned($userId, $propertyId) === null) {
            $this->json(['success' => false, 'message' => __('host.error.not_found')], 404);
        }

        $countBefore = (new ListingImage())->countForProperty($propertyId);
        $imageError = $this->handlePropertyImages($propertyId);
        if ($imageError !== null) {
            $this->json(['success' => false, 'message' => $imageError], 422);
        }

        $this->json($this->uploadImagesPayload($countBefore, $propertyId, 'property'));
    }

    public function uploadPropertyVideo(string $id): never
    {
        [$userId] = $this->requireEstablishment();
        $propertyId = (int) $id;
        $hostProperty = new HostProperty();

        if ($hostProperty->findOwned($userId, $propertyId) === null) {
            $this->json(['success' => false, 'message' => __('host.error.not_found')], 404);
        }

        $videoError = $this->handlePropertyVideo($propertyId);
        if ($videoError !== null) {
            $this->json(['success' => false, 'message' => $videoError], 422);
        }

        $path = $hostProperty->videoPath($propertyId);
        $this->json([
            'success' => true,
            'video' => $path !== null ? [
                'path' => $path,
                'url' => upload_url($path),
            ] : null,
        ]);
    }

    public function uploadLandImages(string $id): never
    {
        [$userId] = $this->requireEstablishment();
        $landId = (int) $id;

        if ((new HostLand())->findOwned($userId, $landId) === null) {
            $this->json(['success' => false, 'message' => __('host.error.not_found')], 404);
        }

        $countBefore = (new ListingImage())->countForLand($landId);
        $imageError = $this->handleLandImages($landId);
        if ($imageError !== null) {
            $this->json(['success' => false, 'message' => $imageError], 422);
        }

        $this->json($this->uploadImagesPayload($countBefore, $landId, 'land'));
    }

    public function publishProperty(string $id): never
    {
        [$userId, $establishment] = $this->requireEstablishment();
        $propertyId = (int) $id;
        $editUrl = url('/host/properties/' . $propertyId . '/edit');
        $hostProperty = new HostProperty();
        $property = $hostProperty->findOwned($userId, $propertyId);
        if ($property === null) {
            $this->respondHostAction(false, __('host.error.not_found'), url('/host/properties'));
        }

        $data = $this->propertyInput();
        if ($data === null) {
            $this->respondHostAction(false, __('host.error.required'), $editUrl);
        }
        $data = $this->withResolvedCoordinates($data, $establishment);

        $hostProperty->update($propertyId, $data);
        (new Amenity())->syncForProperty($propertyId, $this->amenityInput());
        $property = $hostProperty->findOwned($userId, $propertyId);

        $imageError = $this->handlePropertyImages($propertyId);
        if ($imageError !== null) {
            $this->respondHostAction(false, $imageError, $editUrl);
        }

        if (trim((string) ($property['title'] ?? '')) === '') {
            $this->respondHostAction(false, __('host.error.publish_incomplete'), $editUrl);
        }

        $publishError = $this->validateAndPublishProperty($userId, $propertyId);
        if ($publishError !== null) {
            $this->respondHostAction(false, $publishError, $editUrl);
        }

        $city = (string) ($property['city'] ?? config('app', 'default_city', 'Ziguinchor'));
        $publicUrl = url('/properties/' . $propertyId . '?city=' . urlencode($city));

        $this->respondHostAction(true, __('host.property_published'), $publicUrl);
    }

    public function deleteProperty(string $id): never
    {
        [$userId] = $this->requireEstablishment();
        $propertyId = (int) $id;
        $hostProperty = new HostProperty();

        if ($hostProperty->findOwned($userId, $propertyId) === null) {
            $this->respondHostAction(false, __('host.error.not_found'), url('/host/properties'));
        }

        if ($hostProperty->hasActiveBookings($propertyId)) {
            $this->respondHostAction(false, __('host.error.delete_active_bookings'), url('/host/properties'));
        }

        if (!$hostProperty->softDelete($userId, $propertyId)) {
            $this->respondHostAction(false, __('host.error.delete_failed'), url('/host/properties'));
        }

        $this->respondHostAction(true, __('host.listing_deleted'), url('/host/properties'));
    }

    public function deleteLand(string $id): never
    {
        [$userId] = $this->requireEstablishment();
        $landId = (int) $id;
        $hostLand = new HostLand();

        if ($hostLand->findOwned($userId, $landId) === null) {
            $this->respondHostAction(false, __('host.error.not_found'), url('/host/properties'));
        }

        if (!$hostLand->softDelete($userId, $landId)) {
            $this->respondHostAction(false, __('host.error.delete_failed'), url('/host/properties'));
        }

        $this->respondHostAction(true, __('host.listing_deleted'), url('/host/properties'));
    }

    public function availability(string $id): string
    {
        [$userId, $establishment] = $this->requireEstablishment();
        $propertyId = (int) $id;
        $property = (new HostProperty())->findOwned($userId, $propertyId);
        if ($property === null) {
            $this->redirect(url('/host/properties'));
        }

        $availabilityModel = new PropertyAvailability();
        $blockedMap = $availabilityModel->statusMap($propertyId);
        $blockedDates = [];
        foreach ($blockedMap as $date => $status) {
            if (in_array($status, ['blocked', 'maintenance'], true)) {
                $blockedDates[] = $date;
            }
        }
        $reservedDates = (new Booking())->reservedDatesForProperty($propertyId);

        return $this->hostView('host/properties/availability', [
            'title' => __('host.availability_title'),
            'establishment' => $establishment,
            'property' => $property,
            'blockedDates' => $blockedDates,
            'reservedDates' => $reservedDates,
            'success' => flash('host_success'),
            'error' => flash('host_error'),
        ]);
    }

    public function updateAvailability(string $id): never
    {
        [$userId, $establishment] = $this->requireEstablishment();
        $propertyId = (int) $id;
        if ((new HostProperty())->findOwned($userId, $propertyId) === null) {
            $this->redirect(url('/host/properties'));
        }

        $action = (string) $this->request->input('action', 'block');
        $availabilityModel = new PropertyAvailability();
        $redirectUrl = url('/host/properties/' . $propertyId . '/availability');

        if ($action === 'unblock') {
            $date = trim((string) $this->request->input('date', ''));
            if ($date !== '') {
                $availabilityModel->unblockDate($propertyId, $date);
                Session::flash('host_success', __('host.availability_unblocked'));
            }
            $this->redirect($redirectUrl);
        }

        if ($action === 'block_dates') {
            $dates = $this->request->input('dates', []);
            if (is_string($dates)) {
                $dates = array_filter(array_map('trim', explode(',', $dates)));
            }
            if (!is_array($dates)) {
                $dates = [];
            }

            $reserved = (new Booking())->reservedDatesForProperty($propertyId);
            $dates = array_values(array_filter(
                $dates,
                static fn (string $date): bool => !in_array($date, $reserved, true)
            ));

            $count = $availabilityModel->blockDates($propertyId, $dates);
            if ($count === 0) {
                Session::flash('host_error', __('host.error.dates_required'));
            } else {
                Session::flash('host_success', __('host.availability_blocked'));
            }
            $this->redirect($redirectUrl);
        }

        $from = trim((string) $this->request->input('date_from', ''));
        $to = trim((string) $this->request->input('date_to', ''));
        $note = trim((string) $this->request->input('note', ''));

        if ($from === '' || $to === '') {
            Session::flash('host_error', __('host.error.dates_required'));
            $this->redirect($redirectUrl);
        }

        $availabilityModel->blockRange($propertyId, $from, $to, 'blocked', $note !== '' ? $note : null);
        Session::flash('host_success', __('host.availability_blocked'));
        $this->redirect($redirectUrl);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hostView(string $view, array $data): string
    {
        $userId = AuthHelper::id();
        $navBadges = [];

        if ($userId !== null && !isset($data['navBadges'])) {
            try {
                $stats = (new \App\Services\HostDashboardService())->forOwner($userId)['stats'];
                $navBadges = [
                    'listings' => (int) ($stats['listings_draft'] ?? 0) + (int) ($stats['listings_pending'] ?? 0),
                    'bookings' => (int) ($stats['bookings_upcoming'] ?? 0),
                    'messages' => 0,
                ];
            } catch (\Throwable) {
                $navBadges = ['listings' => 0, 'bookings' => 0, 'messages' => 0];
            }

            try {
                $navBadges['messages'] = (new \App\Models\Conversation())->unreadCountForUser($userId);
            } catch (\Throwable) {
                $navBadges['messages'] = 0;
            }
        }

        return $this->view($view, array_merge($data, [
            'isHostPage' => true,
            'isAccountPage' => true,
            'isAgency' => $userId !== null && AuthHelper::isAgency($userId),
            'navBadges' => $data['navBadges'] ?? $navBadges,
        ]));
    }

    private function respondHostAction(bool $success, string $message, ?string $redirectUrl = null, int $errorStatus = 422): never
    {
        if ($this->request->isAjax()) {
            if ($success) {
                $this->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => $redirectUrl ?? url('/host/properties'),
                ]);
            }

            $this->json([
                'success' => false,
                'message' => $message,
                'redirect' => $redirectUrl,
            ], $errorStatus);
        }

        if ($success) {
            Session::flash('host_success', $message);
            $this->redirect($redirectUrl ?? url('/host/properties'));
        }

        Session::flash('host_error', $message);
        $this->redirect($redirectUrl ?? url('/host/properties'));
    }

    private function requireAuth(): int
    {
        if (!AuthHelper::check()) {
            $this->redirect(url('/login?redirect=/host'));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=/host'));
        }

        return (int) $user['id'];
    }

    /**
     * @return array{0: int, 1: array<string, mixed>|null}
     */
    private function requireEstablishment(bool $mustExist = true): array
    {
        $userId = $this->requireAuth();
        $establishment = (new Establishment())->findByOwnerId($userId);

        if ($mustExist && $establishment === null) {
            $this->redirect(url('/host/setup'));
        }

        return [$userId, $establishment];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function withResolvedCoordinates(array $data, ?array $establishment = null): array
    {
        $lat = $data['latitude'] ?? null;
        $lng = $data['longitude'] ?? null;

        if ($lat !== null && $lng !== null && !($lat == 0.0 && $lng == 0.0)) {
            return $data;
        }

        if ($establishment === null) {
            return $data;
        }

        $estLat = (float) ($establishment['latitude'] ?? 0);
        $estLng = (float) ($establishment['longitude'] ?? 0);
        if ($estLat === 0.0 && $estLng === 0.0) {
            return $data;
        }

        $data['latitude'] = $estLat;
        $data['longitude'] = $estLng;

        if (trim((string) ($data['address'] ?? '')) === '') {
            $estAddress = trim((string) ($establishment['address'] ?? ''));
            if ($estAddress !== '') {
                $data['address'] = $estAddress;
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function propertyInput(): ?array
    {
        $title = trim((string) $this->request->input('title', ''));
        $city = trim((string) $this->request->input('city', ''));

        if ($title === '' || $city === '') {
            Session::flash('host_error', __('host.error.required'));
            return null;
        }

        $address = trim((string) $this->request->input('address', ''));
        if ($address === '') {
            $district = trim((string) $this->request->input('district', ''));
            $address = $district !== '' ? $district . ', ' . $city : $city;
        }

        $priceValue = trim((string) $this->request->input('listing_price', ''));
        $rentalPeriod = (string) $this->request->input('rental_period', 'nightly');
        if (!in_array($rentalPeriod, ['nightly', 'monthly'], true)) {
            $rentalPeriod = 'nightly';
        }

        $latitude = trim((string) $this->request->input('latitude', ''));
        $longitude = trim((string) $this->request->input('longitude', ''));

        return [
            'title' => $title,
            'description' => trim((string) $this->request->input('description', '')),
            'type' => (string) $this->request->input('type', 'appartement'),
            'address' => $address,
            'city' => $city,
            'district' => trim((string) $this->request->input('district', '')),
            'latitude' => $latitude !== '' ? (float) $latitude : null,
            'longitude' => $longitude !== '' ? (float) $longitude : null,
            'area_sqm' => null,
            'bedrooms' => max(1, (int) $this->request->input('bedrooms', 1)),
            'bathrooms' => max(1, (int) $this->request->input('bathrooms', 1)),
            'capacity' => max(1, (int) $this->request->input('capacity', 2)),
            'rules' => '',
            'status' => 'draft',
            'currency' => 'XOF',
            'price_per_night' => $rentalPeriod === 'nightly' && $priceValue !== '' ? (float) $priceValue : null,
            'price_per_week' => null,
            'price_per_month' => $rentalPeriod === 'monthly' && $priceValue !== '' ? (float) $priceValue : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function landInput(): ?array
    {
        $title = trim((string) $this->request->input('title', ''));
        $city = trim((string) $this->request->input('city', ''));
        $area = trim((string) $this->request->input('land_area', ''));
        $width = trim((string) $this->request->input('land_width', ''));
        $length = trim((string) $this->request->input('land_length', ''));
        $price = trim((string) $this->request->input('land_price', ''));
        $paperType = trim((string) $this->request->input('paper_type', ''));

        if ($title === '' || $city === '' || $price === '' || $width === '' || $length === '' || $paperType === '') {
            Session::flash('host_error', __('host.error.required'));
            return null;
        }

        if (!array_key_exists($paperType, Land::paperTypes())) {
            Session::flash('host_error', __('host.error.land_paper_type_invalid'));
            return null;
        }

        $widthVal = (float) $width;
        $lengthVal = (float) $length;
        if ($widthVal <= 0 || $lengthVal <= 0) {
            Session::flash('host_error', __('host.error.land_dimensions_invalid'));
            return null;
        }

        $areaUnit = (string) $this->request->input('land_area_unit', 'm2');
        $areaVal = round($widthVal * $lengthVal, 2);
        if ($areaUnit === 'ha') {
            $areaVal = round($areaVal / 10000, 4);
        } elseif ($areaUnit === 'are') {
            $areaVal = round($areaVal / 100, 2);
        }

        $address = trim((string) $this->request->input('address', ''));
        if ($address === '') {
            $district = trim((string) $this->request->input('district', ''));
            $address = $district !== '' ? $district . ', ' . $city : $city;
        }

        $latitude = trim((string) $this->request->input('latitude', ''));
        $longitude = trim((string) $this->request->input('longitude', ''));

        return [
            'title' => $title,
            'description' => trim((string) $this->request->input('description', '')),
            'area' => $areaVal,
            'area_unit' => $areaUnit,
            'width_m' => $widthVal,
            'length_m' => $lengthVal,
            'price' => (float) $price,
            'currency' => 'XOF',
            'city' => $city,
            'district' => trim((string) $this->request->input('district', '')),
            'address' => $address,
            'latitude' => $latitude !== '' ? (float) $latitude : null,
            'longitude' => $longitude !== '' ? (float) $longitude : null,
            'land_type' => (string) $this->request->input('land_type', 'residentiel'),
            'paper_type' => $paperType,
            'status' => 'draft',
        ];
    }

    /**
     * @return list<int>
     */
    private function amenityInput(): array
    {
        $raw = $this->request->input('amenities', []);
        if (!is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function saveSuccessMessage(string $baseMessage): string
    {
        $uploaded = (int) Session::getFlash('host_images_saved', 0);
        if ($uploaded <= 0) {
            return $baseMessage;
        }

        return $baseMessage . ' ' . __('host.images.saved', ['count' => $uploaded]);
    }

    private function imagesMin(): int
    {
        return (int) config('app', 'property_images_min', 4);
    }

    private function imagesMax(): int
    {
        return (int) config('app', 'property_images_max', 10);
    }

    /**
     * @return array<string, mixed>
     */
    private function uploadImagesPayload(int $countBefore, int $listingId, string $kind): array
    {
        $imageModel = new ListingImage();
        $images = $kind === 'land'
            ? $imageModel->forLand($listingId)
            : $imageModel->forProperty($listingId);

        $added = array_values(array_slice($images, $countBefore));
        $image = $added[0] ?? null;

        $payload = [
            'success' => true,
            'count' => count($images),
        ];

        if ($image !== null) {
            $payload['image'] = [
                'id' => (int) ($image['id'] ?? 0),
                'path' => (string) ($image['path'] ?? ''),
                'url' => upload_url((string) ($image['path'] ?? '')),
                'is_primary' => (int) ($image['is_primary'] ?? 0) === 1,
            ];
        }

        return $payload;
    }

    private function wantsPublish(): bool
    {
        return (string) $this->request->input('intent', 'save') === 'publish';
    }

    /**
     * @param list<array<string, mixed>> $listings
     * @return list<array<string, mixed>>
     */
    private function attachPreviewImages(array $listings, string $kind): array
    {
        $imageModel = new ListingImage();

        foreach ($listings as &$listing) {
            $id = (int) ($listing['id'] ?? 0);
            $images = $kind === 'land'
                ? $imageModel->forLand($id)
                : $imageModel->forProperty($id);
            $listing['image_count'] = count($images);
            if ($images !== []) {
                $listing['primary_image'] = (string) ($images[0]['path'] ?? $listing['primary_image'] ?? '');
            }
        }
        unset($listing);

        return $listings;
    }

    private function validateAndPublishProperty(int $userId, int $propertyId): ?string
    {
        $hostProperty = new HostProperty();
        $property = $hostProperty->findOwned($userId, $propertyId);
        if ($property === null) {
            return __('host.error.publish_incomplete');
        }

        if (trim((string) ($property['title'] ?? '')) === '') {
            return __('host.error.publish_incomplete');
        }

        $hasPrice = (float) ($property['price_per_night'] ?? 0) > 0
            || (float) ($property['price_per_month'] ?? 0) > 0;
        if (!$hasPrice) {
            return __('host.error.publish_incomplete');
        }

        if ((new ListingImage())->countForProperty($propertyId) < $this->imagesMin()) {
            return __('host.error.images_min', ['min' => $this->imagesMin()]);
        }

        $hostProperty->updateStatus($propertyId, 'approved');

        return null;
    }

    private function validateAndPublishLand(int $userId, int $landId): ?string
    {
        $hostLand = new HostLand();
        $land = $hostLand->findOwned($userId, $landId);
        if ($land === null) {
            return __('host.error.publish_land_incomplete');
        }

        if (trim((string) ($land['title'] ?? '')) === ''
            || (float) ($land['price'] ?? 0) <= 0
            || (float) ($land['area'] ?? 0) <= 0
            || (float) ($land['width_m'] ?? 0) <= 0
            || (float) ($land['length_m'] ?? 0) <= 0
            || trim((string) ($land['paper_type'] ?? '')) === '') {
            return __('host.error.publish_land_incomplete');
        }

        if ((new ListingImage())->countForLand($landId) < $this->imagesMin()) {
            return __('host.error.images_min', ['min' => $this->imagesMin()]);
        }

        $hostLand->updateStatus($landId, 'approved');

        return null;
    }

    /**
     * @return array{existingImages: list<array<string, mixed>>, imagesMin: int, imagesMax: int}
     */
    private function imageViewData(?array $property, ?array $land): array
    {
        $imageModel = new ListingImage();
        $existingImages = [];

        if ($property !== null) {
            $existingImages = $imageModel->forProperty((int) $property['id']);
        } elseif ($land !== null) {
            $existingImages = $imageModel->forLand((int) $land['id']);
        }

        return [
            'existingImages' => $existingImages,
            'imagesMin' => $this->imagesMin(),
            'imagesMax' => $this->imagesMax(),
        ];
    }

    /**
     * @return list<int>
     */
    private function removeImageIds(): array
    {
        $raw = $this->request->input('remove_image_ids', []);
        if (!is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function handlePropertyImages(int $propertyId): ?string
    {
        $imageModel = new ListingImage();
        $service = new ListingImageService();

        foreach ($this->removeImageIds() as $imageId) {
            $path = $imageModel->deleteProperty($imageId, $propertyId);
            $service->deleteFile($path);
        }

        $current = $imageModel->countForProperty($propertyId);
        $maxNew = max(0, $this->imagesMax() - $current);
        if ($maxNew === 0 && $this->request->hasUploadedFile('images')) {
            return __('host.images.error_max', ['max' => $this->imagesMax()]);
        }

        if (!$this->request->hasUploadedFile('images')) {
            $imageModel->ensurePrimaryProperty($propertyId);

            return null;
        }

        try {
            $paths = $service->storeMany(
                $this->request->file('images'),
                'uploads/properties/' . $propertyId,
                $maxNew
            );
        } catch (\RuntimeException $e) {
            return $e->getMessage();
        }

        $sort = $current;
        $hadImages = $current > 0;
        foreach ($paths as $index => $path) {
            $imageModel->addProperty($propertyId, $path, $sort, !$hadImages && $index === 0);
            $sort++;
        }

        $imageModel->ensurePrimaryProperty($propertyId);

        if ($paths !== []) {
            Session::flash('host_images_saved', count($paths));
        }

        return null;
    }

    private function handlePropertyVideo(int $propertyId): ?string
    {
        $hostProperty = new HostProperty();
        $service = new ListingVideoService();
        $remove = (string) $this->request->input('remove_video', '') === '1';

        if ($remove) {
            $existing = $hostProperty->videoPath($propertyId);
            $service->deleteFile($existing);
            $hostProperty->setVideoPath($propertyId, null);

            return null;
        }

        if (!$this->request->hasUploadedFile('video')) {
            return null;
        }

        $existing = $hostProperty->videoPath($propertyId);
        if ($existing !== null) {
            $service->deleteFile($existing);
        }

        try {
            $path = $service->store(
                $this->request->file('video'),
                'uploads/properties/' . $propertyId
            );
        } catch (\RuntimeException $e) {
            return $e->getMessage();
        }

        if ($path === null) {
            return null;
        }

        $hostProperty->setVideoPath($propertyId, $path);

        return null;
    }

    private function handleLandImages(int $landId): ?string
    {
        $imageModel = new ListingImage();
        $service = new ListingImageService();

        foreach ($this->removeImageIds() as $imageId) {
            $path = $imageModel->deleteLand($imageId, $landId);
            $service->deleteFile($path);
        }

        $current = $imageModel->countForLand($landId);
        $maxNew = max(0, $this->imagesMax() - $current);
        if ($maxNew === 0 && $this->request->hasUploadedFile('images')) {
            return __('host.images.error_max', ['max' => $this->imagesMax()]);
        }

        if (!$this->request->hasUploadedFile('images')) {
            $imageModel->ensurePrimaryLand($landId);

            return null;
        }

        try {
            $paths = $service->storeMany(
                $this->request->file('images'),
                'uploads/lands/' . $landId,
                $maxNew
            );
        } catch (\RuntimeException $e) {
            return $e->getMessage();
        }

        $sort = $current;
        $hadImages = $current > 0;
        foreach ($paths as $index => $path) {
            $imageModel->addLand($landId, $path, $sort, !$hadImages && $index === 0);
            $sort++;
        }

        $imageModel->ensurePrimaryLand($landId);

        if ($paths !== []) {
            Session::flash('host_images_saved', count($paths));
        }

        return null;
    }
}
