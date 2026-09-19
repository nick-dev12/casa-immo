<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use PDOException;

final class DatabaseMigrationService
{
    public function runPending(): int
    {
        $pdo = Database::connection();
        $this->ensureMigrationsTable($pdo);
        $this->bootstrapExistingInstall($pdo);
        $this->reconcileLegacyMigrations($pdo);

        $applied = 0;
        $files = glob(base_path('database/migrations/*.sql')) ?: [];
        sort($files, SORT_STRING);

        foreach ($files as $path) {
            $name = basename($path);
            if ($this->isApplied($pdo, $name)) {
                continue;
            }

            $sql = (string) file_get_contents($path);
            $this->executeSqlBatch($pdo, $sql);
            $this->markApplied($pdo, $name);
            $applied++;
        }

        return $applied;
    }

    /**
     * Base déjà créée via schema.sql sans historique schema_migrations :
     * on marque les anciennes migrations comme appliquées et on n'exécute que les nouvelles.
     */
    private function bootstrapExistingInstall(PDO $pdo): void
    {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM schema_migrations')->fetchColumn();
        if ($count > 0) {
            return;
        }

        $hasBookings = (bool) $pdo->query("SHOW TABLES LIKE 'bookings'")->fetchColumn();
        if (!$hasBookings) {
            return;
        }

        $skipExecution = [
            '019_booking_review_reminder.sql',
            '020_booking_confirmation_pin.sql',
            '021_booking_confirmation_email_sent.sql',
        ];

        $files = glob(base_path('database/migrations/*.sql')) ?: [];
        sort($files, SORT_STRING);

        foreach ($files as $path) {
            $name = basename($path);
            if (in_array($name, $skipExecution, true)) {
                continue;
            }
            $this->markApplied($pdo, $name);
        }
    }

    /**
     * Base déjà en prod : ne pas ré-exécuter les migrations 001–018 manquantes dans l'historique.
     */
    private function reconcileLegacyMigrations(PDO $pdo): void
    {
        $hasBookings = (bool) $pdo->query("SHOW TABLES LIKE 'bookings'")->fetchColumn();
        if (!$hasBookings) {
            return;
        }

        $files = glob(base_path('database/migrations/*.sql')) ?: [];
        sort($files, SORT_STRING);

        foreach ($files as $path) {
            $name = basename($path);
            if ($this->isApplied($pdo, $name) || !$this->isLegacyMigrationName($name)) {
                continue;
            }

            $this->markApplied($pdo, $name);
        }
    }

    private function isLegacyMigrationName(string $name): bool
    {
        if (!preg_match('/^(\d+)_/', $name, $matches)) {
            return false;
        }

        return (int) $matches[1] < 19;
    }

    private function ensureMigrationsTable(PDO $pdo): void
    {
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS `schema_migrations` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `migration` VARCHAR(191) NOT NULL,
                `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `schema_migrations_migration_unique` (`migration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    private function isApplied(PDO $pdo, string $name): bool
    {
        $stmt = $pdo->prepare('SELECT 1 FROM schema_migrations WHERE migration = :name LIMIT 1');
        $stmt->execute([':name' => $name]);

        return (bool) $stmt->fetchColumn();
    }

    private function markApplied(PDO $pdo, string $name): void
    {
        $stmt = $pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (:name)');
        $stmt->execute([':name' => $name]);
    }

    private function executeSqlBatch(PDO $pdo, string $sql): void
    {
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if ($statement === '') {
                continue;
            }

            try {
                $pdo->exec($statement);
            } catch (PDOException $exception) {
                $message = $exception->getMessage();
                if ($this->isIgnorableMigrationError($message)) {
                    continue;
                }

                throw $exception;
            }
        }
    }

    private function isIgnorableMigrationError(string $message): bool
    {
        $lower = strtolower($message);

        return str_contains($lower, 'duplicate column')
            || str_contains($lower, 'already exists')
            || str_contains($lower, 'duplicate key name');
    }
}
