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

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM employee_groups WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

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
}
