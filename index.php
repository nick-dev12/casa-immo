<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Env;
use App\Core\ErrorHandler;

define('BASE_PATH', __DIR__);

require BASE_PATH . '/vendor/autoload.php';

Env::load(BASE_PATH);

/** @var array<string, mixed> $appConfig */
$appConfig = require BASE_PATH . '/config/app.php';

ErrorHandler::register((bool) $appConfig['debug']);

$app = App::boot();
$app->run();
