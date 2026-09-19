<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT id, first_name, last_name, email, phone, avatar, status, created_at, last_login_at
            FROM users
            WHERE id = :id AND deleted_at IS NULL AND status = \'active\'
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('
            SELECT *
            FROM users
            WHERE email = :email AND deleted_at IS NULL AND status = \'active\'
            LIMIT 1
        ');
        $stmt->execute([':email' => mb_strtolower(trim($email))]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function createClient(string $firstName, string $lastName, string $email, string $password): ?array
    {
        $email = mb_strtolower(trim($email));
        if ($this->findByEmail($email) !== null) {
            return null;
        }

        $stmt = $this->db->prepare('
            INSERT INTO users (first_name, last_name, email, password, status)
            VALUES (:first_name, :last_name, :email, :password, \'active\')
        ');
        $stmt->execute([
            ':first_name' => trim($firstName),
            ':last_name' => trim($lastName),
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        $id = (int) $this->db->lastInsertId();

        $roleStmt = $this->db->prepare('
            INSERT INTO user_roles (user_id, role_id)
            SELECT :user_id, id FROM roles WHERE slug = \'client\' LIMIT 1
        ');
        $roleStmt->execute([':user_id' => $id]);

        return $this->findById($id);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function createAdmin(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        ?string $phone = null
    ): ?array {
        $email = mb_strtolower(trim($email));
        if ($this->findByEmail($email) !== null) {
            return null;
        }

        $stmt = $this->db->prepare('
            INSERT INTO users (first_name, last_name, email, phone, password, email_verified_at, status)
            VALUES (:first_name, :last_name, :email, :phone, :password, NOW(), \'active\')
        ');
        $stmt->execute([
            ':first_name' => trim($firstName),
            ':last_name' => trim($lastName),
            ':email' => $email,
            ':phone' => $phone !== null && trim($phone) !== '' ? trim($phone) : null,
            ':password' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        $id = (int) $this->db->lastInsertId();
        $this->assignRole($id, 'admin');

        return $this->findById($id);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function createAgencyAccount(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        string $agencyName,
        string $city,
        ?string $phone = null,
        ?string $address = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $district = null
    ): ?array {
        $email = mb_strtolower(trim($email));
        if ($this->findByEmail($email) !== null) {
            return null;
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('
                INSERT INTO users (first_name, last_name, email, phone, password, status)
                VALUES (:first_name, :last_name, :email, :phone, :password, \'active\')
            ');
            $stmt->execute([
                ':first_name' => trim($firstName),
                ':last_name' => trim($lastName),
                ':email' => $email,
                ':phone' => $phone !== null && $phone !== '' ? $phone : null,
                ':password' => password_hash($password, PASSWORD_BCRYPT),
            ]);

            $userId = (int) $this->db->lastInsertId();

            foreach (['client', 'agency', 'owner'] as $roleSlug) {
                $roleStmt = $this->db->prepare('
                    INSERT INTO user_roles (user_id, role_id)
                    SELECT :user_id, id FROM roles WHERE slug = :slug LIMIT 1
                ');
                $roleStmt->execute([
                    ':user_id' => $userId,
                    ':slug' => $roleSlug,
                ]);
            }

            $establishmentStmt = $this->db->prepare('
                INSERT INTO establishments (
                    owner_id, name, description, city, district, address, latitude, longitude, phone, status
                ) VALUES (
                    :owner_id, :name, :description, :city, :district, :address, :latitude, :longitude, :phone, \'active\'
                )
            ');
            $establishmentStmt->execute([
                ':owner_id' => $userId,
                ':name' => trim($agencyName),
                ':description' => '',
                ':city' => trim($city),
                ':district' => $district !== null && $district !== '' ? trim($district) : '',
                ':address' => $address !== null && $address !== '' ? trim($address) : trim($city),
                ':latitude' => $latitude,
                ':longitude' => $longitude,
                ':phone' => $phone !== null && $phone !== '' ? $phone : '',
            ]);

            $this->db->commit();

            return $this->findById($userId);
        } catch (\Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return null;
        }
    }

    public function touchLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findAuthById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT id, first_name, last_name, email, phone, password, avatar, status, created_at, last_login_at
            FROM users
            WHERE id = :id AND deleted_at IS NULL AND status = \'active\'
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function hasRole(int $userId, string $slug): bool
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*)
            FROM user_roles ur
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE ur.user_id = :user_id AND r.slug = :slug
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':slug' => $slug,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @return list<string>
     */
    public function roleSlugs(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT r.slug
            FROM user_roles ur
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE ur.user_id = :user_id
        ');
        $stmt->execute([':user_id' => $userId]);

        return array_column($stmt->fetchAll(), 'slug');
    }

    /**
     * @return true|string Error message
     */
    public function updateProfile(int $id, string $firstName, string $lastName, string $email, ?string $phone): bool|string
    {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $email = mb_strtolower(trim($email));
        $phone = $phone !== null ? trim($phone) : null;

        if ($firstName === '' || $lastName === '' || $email === '') {
            return __('profile.error.required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return __('profile.error.email_invalid');
        }

        $existing = $this->findByEmail($email);
        if ($existing !== null && (int) $existing['id'] !== $id) {
            return __('profile.error.email_taken');
        }

        $stmt = $this->db->prepare('
            UPDATE users
            SET first_name = :first_name,
                last_name = :last_name,
                email = :email,
                phone = :phone,
                updated_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':phone' => $phone !== '' ? $phone : null,
            ':id' => $id,
        ]);

        return true;
    }

    public function updatePassword(int $id, string $password): void
    {
        $stmt = $this->db->prepare('
            UPDATE users
            SET password = :password, updated_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            ':password' => password_hash($password, PASSWORD_BCRYPT),
            ':id' => $id,
        ]);
    }

    public function assignRole(int $userId, string $slug): void
    {
        $stmt = $this->db->prepare('
            INSERT IGNORE INTO user_roles (user_id, role_id)
            SELECT :user_id, id FROM roles WHERE slug = :slug LIMIT 1
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':slug' => $slug,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findAdminById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.status, u.created_at
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id AND r.slug = \'admin\'
            WHERE u.id = :id AND u.deleted_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['active', 'inactive', 'banned'], true)) {
            return false;
        }

        $stmt = $this->db->prepare('
            UPDATE users SET status = :status, updated_at = NOW()
            WHERE id = :id AND deleted_at IS NULL
        ');
        $stmt->execute([
            ':id' => $id,
            ':status' => $status,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare('
            UPDATE users
            SET deleted_at = NOW(), status = \'inactive\', updated_at = NOW()
            WHERE id = :id AND deleted_at IS NULL
        ');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function removeRole(int $userId, string $slug): void
    {
        $stmt = $this->db->prepare('
            DELETE ur FROM user_roles ur
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE ur.user_id = :user_id AND r.slug = :slug
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':slug' => $slug,
        ]);
    }

    public function countActiveAdmins(): int
    {
        $stmt = $this->db->query('
            SELECT COUNT(*)
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id AND r.slug = \'admin\'
            WHERE u.deleted_at IS NULL AND u.status = \'active\'
        ');

        return (int) $stmt->fetchColumn();
    }

    /**
     * Trouve un client existant ou en crée un pour le locataire.
     */
    public function findOrCreateTenant(string $firstName, string $lastName, ?string $email = null, ?string $phone = null): int
    {
        $email = $email !== null ? mb_strtolower(trim($email)) : '';
        $phone = $phone !== null ? trim($phone) : '';

        if ($email !== '') {
            $userId = $this->resolveTenantUserIdByEmail($email, $phone);

            if ($userId !== null) {
                return $userId;
            }
        }

        if ($phone !== '') {
            $stmt = $this->db->prepare('
                SELECT id, status FROM users
                WHERE phone = :phone AND deleted_at IS NULL
                LIMIT 1
            ');
            $stmt->execute([':phone' => $phone]);
            $existing = $stmt->fetch();

            if (is_array($existing)) {
                return $this->activateTenantUser((int) $existing['id'], $phone);
            }
        }

        if ($email === '') {
            $email = $this->generateTenantEmail($firstName, $lastName);
        }

        $password = bin2hex(random_bytes(8));
        $created = $this->createClient($firstName, $lastName, $email, $password);
        if ($created === null) {
            throw new \RuntimeException('tenant_create_failed');
        }

        $userId = (int) $created['id'];
        if ($phone !== '') {
            $update = $this->db->prepare('UPDATE users SET phone = :phone WHERE id = :id');
            $update->execute([':phone' => $phone, ':id' => $userId]);
        }

        return $userId;
    }

    private function resolveTenantUserIdByEmail(string $email, string $phone): ?int
    {
        $stmt = $this->db->prepare('
            SELECT id, status FROM users WHERE email = :email AND deleted_at IS NULL LIMIT 1
        ');
        $stmt->execute([':email' => $email]);
        $existing = $stmt->fetch();

        if (!is_array($existing)) {
            return null;
        }

        return $this->activateTenantUser((int) $existing['id'], $phone !== '' ? $phone : null);
    }

    private function activateTenantUser(int $userId, ?string $phone): int
    {
        $stmt = $this->db->prepare('SELECT status FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();

        if (is_array($row) && (string) ($row['status'] ?? '') !== 'active') {
            $this->setStatus($userId, 'active');
        }

        if ($phone !== null && trim($phone) !== '') {
            $update = $this->db->prepare('UPDATE users SET phone = :phone, updated_at = NOW() WHERE id = :id');
            $update->execute([':phone' => trim($phone), ':id' => $userId]);
        }

        $this->assignRole($userId, 'client');

        return $userId;
    }

    private function generateTenantEmail(string $firstName, string $lastName): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '.', mb_strtolower(trim($firstName . '.' . $lastName))) ?? 'locataire';
        $slug = trim($slug, '.') ?: 'locataire';

        do {
            $email = $slug . '.' . bin2hex(random_bytes(4)) . '@rental.zig-imobilier.local';
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $exists = $stmt->fetch();
        } while ($exists);

        return $email;
    }
}
