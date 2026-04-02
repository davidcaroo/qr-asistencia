<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Core\Database;
use PDO;

final class GroupRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM employee_groups ORDER BY name ASC')->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO employee_groups (name, slug, description, active) VALUES (:name, :slug, :description, :active)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'active' => $data['active'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM employee_groups WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM employee_groups WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM employee_groups WHERE LOWER(name) = LOWER(:name) LIMIT 1');
        $stmt->execute(['name' => $name]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByNameOrSlug(string $value): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM employee_groups WHERE LOWER(name) = LOWER(:name_value) OR LOWER(slug) = LOWER(:slug_value) LIMIT 1');
        $stmt->execute([
            'name_value' => $value,
            'slug_value' => $value,
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE employee_groups SET name = :name, slug = :slug, description = :description, active = :active WHERE id = :id'
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
        $stmt = $this->pdo->prepare('DELETE FROM employee_groups WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
