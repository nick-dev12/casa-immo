<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\BookingLifecycleService;

final class CronController extends Controller
{
    public function run(): never
    {
        $secret = trim((string) env('CRON_SECRET', ''));
        $provided = trim((string) $this->request->input('token', ''));

        if ($secret === '' || !hash_equals($secret, $provided)) {
            $this->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        $result = (new BookingLifecycleService())->runDaily();

        $this->json([
            'success' => true,
            'data' => $result,
            'timestamp' => date('c'),
        ]);
    }
}
