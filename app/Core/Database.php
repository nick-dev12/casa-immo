<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        require_once dirname(__DIR__, 2) . '/config/database.php';

        if (!($db instanceof PDO)) {
            throw new RuntimeException('Impossible de se connecter à la base de données.');
        }

        return self::$connection = $db;
    }
}
