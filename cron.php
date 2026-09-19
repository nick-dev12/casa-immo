<?php

declare(strict_types=1);

use App\Core\Env;
use App\Services\BookingLifecycleService;

define('BASE_PATH', __DIR__);

require BASE_PATH . '/vendor/autoload.php';

Env::load(BASE_PATH);

/** @var array<string, mixed> $appConfig */
$appConfig = require BASE_PATH . '/config/app.php';
date_default_timezone_set((string) ($appConfig['timezone'] ?? 'UTC'));

try {
    (new \App\Services\DatabaseMigrationService())->runPending();
} catch (\Throwable) {
    // ignore
}

(new \App\Services\BackgroundSchedulerService())->tick();
$result = ['scheduler' => 'ok'];

if (PHP_SAPI === 'cli') {
    echo json_encode([
        'success' => true,
        'data' => $result,
        'timestamp' => date('c'),
    ], JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(0);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'data' => $result,
    'timestamp' => date('c'),
], JSON_UNESCAPED_UNICODE);
