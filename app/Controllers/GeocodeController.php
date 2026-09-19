<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\GeocodeService;

final class GeocodeController extends Controller
{
    public function reverse(): never
    {
        $lat = (float) $this->request->input('lat', 0);
        $lng = (float) $this->request->input('lng', 0);

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 || ($lat === 0.0 && $lng === 0.0)) {
            $this->json([
                'success' => false,
                'message' => __('host.geolocate.invalid_coords'),
            ], 422);
        }

        $result = (new GeocodeService())->reverse($lat, $lng);
        if ($result === null) {
            $this->json([
                'success' => false,
                'message' => __('host.geolocate.reverse_failed'),
            ], 502);
        }

        $this->json([
            'success' => true,
            'message' => 'OK',
            'data' => $result,
        ]);
    }
}
