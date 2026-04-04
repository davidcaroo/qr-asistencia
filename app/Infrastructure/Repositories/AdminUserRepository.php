<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Core\Database;
use PDO;

final class AdminUserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT au.*, r.id AS role_id, r.name AS role_name, r.slug AS role_slug
            FROM admin_users au
            LEFT JOIN admin_user_roles aur ON aur.admin_user_id = au.id
            LEFT JOIN roles r ON r.id = aur.role_id
            WHERE au.email = :email AND au.active = 1
            LIMIT 1
        ');
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByEmailAnyState(string $email): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT au.*, r.id AS role_id, r.name AS role_name, r.slug AS role_slug
            FROM admin_users au
            LEFT JOIN admin_user_roles aur ON aur.admin_user_id = au.id
            LEFT JOIN roles r ON r.id = aur.role_id
            WHERE au.email = :email
            LIMIT 1
        ');
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT au.*, r.id AS role_id, r.name AS role_name, r.slug AS role_slug
            FROM admin_users au
            LEFT JOIN admin_user_roles aur ON aur.admin_user_id = au.id
            LEFT JOIN roles r ON r.id = aur.role_id
            WHERE au.id = :id AND au.active = 1
            LIMIT 1
        ');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function touchLastLogin(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE admin_users SET last_login_at = UTC_TIMESTAMP() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function all(int $limit = 200): array
    {
        $stmt = $this->pdo->prepare('
            SELECT au.*, r.name AS role_name, r.slug AS role_slug
            FROM admin_users au
            LEFT JOIN admin_user_roles aur ON aur.admin_user_id = au.id
            LEFT JOIN roles r ON r.id = aur.role_id
            ORDER BY au.id DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO admin_users (name, email, password_hash, role, active)
            VALUES (:name, :email, :password_hash, :role, :active)
        ');
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'] ?? 'rrhh',
            'active' => $data['active'] ?? 1,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE admin_users
            SET name = :name, email = :email, role = :role, active = :active
            WHERE id = :id
        ');

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'rrhh',
            'active' => $data['active'] ?? 1,
        ]);
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->pdo->prepare('UPDATE admin_users SET password_hash = :password_hash WHERE id = :id');

        return $stmt->execute([
            'id' => $id,
            'password_hash' => $passwordHash,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM admin_users WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    public function setRole(int $adminUserId, int $roleId): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO admin_user_roles (admin_user_id, role_id)
            VALUES (:admin_user_id, :role_id)
            ON DUPLICATE KEY UPDATE role_id = VALUES(role_id)
        ');
        $stmt->execute([
            'admin_user_id' => $adminUserId,
            'role_id' => $roleId,
        ]);
    }
}
