<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Core\Database;
use PDO;

final class RoleRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM roles ORDER BY name ASC');

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM roles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM roles WHERE LOWER(name) = LOWER(:name) LIMIT 1');
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO roles (name, slug, description, active) VALUES (:name, :slug, :description, :active)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'active' => $data['active'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE roles SET name = :name, slug = :slug, description = :description, active = :active WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'active' => $data['active'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM roles WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    public function permissionsForRole(int $roleId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.*
            FROM permissions p
            INNER JOIN role_permissions rp ON rp.permission_id = p.id
            WHERE rp.role_id = :role_id
            ORDER BY p.slug ASC
        ');
        $stmt->execute(['role_id' => $roleId]);

        return $stmt->fetchAll();
    }

    public function assignPermissions(int $roleId, array $permissionIds): void
    {
        $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));

        $this->pdo->prepare('DELETE FROM role_permissions WHERE role_id = :role_id')
            ->execute(['role_id' => $roleId]);

        if ($permissionIds === []) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)'
        );

        foreach ($permissionIds as $permissionId) {
            $stmt->execute([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function countAdminsWithRole(int $roleId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM admin_user_roles WHERE role_id = :role_id');
        $stmt->execute(['role_id' => $roleId]);

        return (int) $stmt->fetchColumn();
    }

    public function rolePermissionsForAdmin(int $adminUserId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.slug
            FROM admin_user_roles aur
            INNER JOIN role_permissions rp ON rp.role_id = aur.role_id
            INNER JOIN permissions p ON p.id = rp.permission_id
            WHERE aur.admin_user_id = :admin_user_id
        ');
        $stmt->execute(['admin_user_id' => $adminUserId]);

        return array_map(static fn($row): string => (string) $row['slug'], $stmt->fetchAll());
    }

    public function roleForAdmin(int $adminUserId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT r.*
            FROM admin_user_roles aur
            INNER JOIN roles r ON r.id = aur.role_id
            WHERE aur.admin_user_id = :admin_user_id
            LIMIT 1
        ');
        $stmt->execute(['admin_user_id' => $adminUserId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
