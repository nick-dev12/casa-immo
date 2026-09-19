<?php

declare(strict_types=1);

/**
 * Script développeur uniquement — en production tout est automatique :
 * - migration SQL au chargement du site
 * - e-mail de confirmation à chaque réservation (+ nouvelles tentatives toutes les 5 min)
 * - rappels d'avis J+2 une fois par jour
 *
 * Usage local : php scripts/send-booking-confirmation-test.php email@exemple.com Prénom
 */

use App\Core\Env;
use App\Services\BookingConfirmationEmailService;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

Env::load(BASE_PATH);

/** @var array<string, mixed> $appConfig */
$appConfig = require BASE_PATH . '/config/app.php';
date_default_timezone_set((string) ($appConfig['timezone'] ?? 'UTC'));

$email = $argv[1] ?? '';
$firstName = $argv[2] ?? 'Voyageur';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php scripts/send-booking-confirmation-test.php email@exemple.com Prénom\n");
    exit(1);
}

$result = (new BookingConfirmationEmailService())->sendDemoTo($email, $firstName);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
exit($result['success'] && ($result['mailed'] ?? false) ? 0 : 1);
