<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\NotFoundException;
use App\Helpers\DestinationHelper;
use App\Models\Land;
use App\Models\User;

final class LandController extends Controller
{
    public function index(): string
    {
        $model = new Land();
        $filters = [
            'q' => (string) $this->request->input('q', ''),
            'city' => (string) $this->request->input('city', ''),
            'type' => (string) $this->request->input('type', ''),
            'price_min' => (string) $this->request->input('price_min', ''),
            'price_max' => (string) $this->request->input('price_max', ''),
            'area_min' => (string) $this->request->input('area_min', ''),
            'area_max' => (string) $this->request->input('area_max', ''),
        ];

        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $lands = $model->search($filters, $perPage, $offset);
        $total = $model->countSearch($filters);
        $totalPages = (int) max(1, ceil($total / $perPage));

        $cities = $model->getCities();
        $defaultCity = in_array('Ziguinchor', $cities, true)
            ? 'Ziguinchor'
            : ($cities[0] ?? config('app', 'default_city', 'Ziguinchor'));

        return $this->view('lands/index', [
            'title' => __('lands.title'),
            'lands' => $lands,
            'filters' => $filters,
            'cities' => $cities,
            'landTypes' => localized_types(Land::types(), 'land_type'),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'currentCity' => $filters['city'] ?: $defaultCity,
            'cityGrid' => DestinationHelper::forGridCatalog(),
            'isLandsList' => true,
        ]);
    }

    public function show(string $id): string
    {
        $model = new Land();
        $land = $model->findById((int) $id);

        if ($land === null) {
            throw new NotFoundException('Terrain introuvable.');
        }

        $images = $model->getImages((int) $land['id']);
        $seller = !empty($land['seller_id'])
            ? (new User())->findById((int) $land['seller_id'])
            : null;

        return $this->view('lands/show', [
            'title' => (string) $land['title'],
            'land' => $land,
            'images' => $images,
            'seller' => $seller,
            'currentCity' => (string) $land['city'],
            'needsListingMap' => true,
            'needsLandPlanPreview' => (float) ($land['length_m'] ?? 0) > 0 && (float) ($land['width_m'] ?? 0) > 0,
        ]);
    }
}
