<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Core\Database;
use PDO;

final class EmployeeRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function findByCedula(string $cedula): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM employees WHERE cedula = :cedula AND active = 1 LIMIT 1');
        $stmt->execute(['cedula' => $cedula]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM employees WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function all(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare('SELECT e.*, g.name AS group_name FROM employees e LEFT JOIN employee_groups g ON g.id = e.group_id ORDER BY e.id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
