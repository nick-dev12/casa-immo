<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDOException;

final class HealthController extends Controller
{
    public function index(): never
    {
        $databaseStatus = 'disconnected';
        $databaseMessage = 'Non connecté';

        try {
            Database::connection()->query('SELECT 1');
            $databaseStatus = 'connected';
            $databaseMessage = 'Connexion MySQL OK';
        } catch (PDOException $exception) {
            $databaseMessage = $exception->getMessage();
        } catch (\Throwable $exception) {
            $databaseMessage = $exception->getMessage();
        }

        $this->json([
            'success' => true,
            'message' => 'Application opérationnelle',
            'data' => [
                'app' => config('app', 'name'),
                'env' => config('app', 'env'),
                'php_version' => PHP_VERSION,
                'database' => [
                    'status' => $databaseStatus,
                    'message' => $databaseMessage,
                ],
                'timestamp' => date('c'),
            ],
        ]);
    }
}
