<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PasswordResetToken extends Model
{
    private const TTL_MINUTES = 15;

    public static function ttlMinutes(): int
    {
        return self::TTL_MINUTES;
    }

    public function store(string $email, string $plainToken): void
    {
        $email = mb_strtolower(trim($email));
        $hash = hash('sha256', $plainToken);

        $stmt = $this->db->prepare('
            INSERT INTO password_reset_tokens (email, token, created_at)
            VALUES (:email, :token, NOW())
            ON DUPLICATE KEY UPDATE token = VALUES(token), created_at = NOW()
        ');
        $stmt->execute([
            ':email' => $email,
            ':token' => $hash,
        ]);
    }

    public function isValid(string $email, string $plainToken): bool
    {
        $email = mb_strtolower(trim($email));
        $hash = hash('sha256', $plainToken);

        $stmt = $this->db->prepare('
            SELECT token, created_at
            FROM password_reset_tokens
            WHERE email = :email
            LIMIT 1
        ');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        if (!$row || !hash_equals((string) $row['token'], $hash)) {
            return false;
        }

        $createdAt = strtotime((string) $row['created_at']);
        if ($createdAt === false) {
            return false;
        }

        return (time() - $createdAt) <= (self::TTL_MINUTES * 60);
    }

    public function delete(string $email): void
    {
        $stmt = $this->db->prepare('DELETE FROM password_reset_tokens WHERE email = :email');
        $stmt->execute([':email' => mb_strtolower(trim($email))]);
    }
}
