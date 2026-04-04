<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Core\Database;
use PDO;

final class PermissionRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM permissions ORDER BY slug ASC');

        return $stmt->fetchAll();
    }

    public function findByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if ($ids === []) {
            return [];
        }

        $placeholders = [];
        $params = [];

        foreach ($ids as $index => $id) {
            $placeholder = ':id' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $id;
        }

        $stmt = $this->pdo->prepare('SELECT * FROM permissions WHERE id IN (' . implode(', ', $placeholders) . ')');
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
