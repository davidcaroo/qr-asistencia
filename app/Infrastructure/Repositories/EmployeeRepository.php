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

    public function findByCedula(string $cedula, bool $activeOnly = true): ?array
    {
        $sql = 'SELECT * FROM employees WHERE cedula = :cedula';

        if ($activeOnly) {
            $sql .= ' AND active = 1';
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
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

    public function all(int $limit = 50, string $search = ''): array
    {
        $sql = 'SELECT e.*, g.name AS group_name FROM employees e LEFT JOIN employee_groups g ON g.id = e.group_id';

        if ($search !== '') {
            $sql .= ' WHERE e.cedula LIKE :search_cedula OR e.full_name LIKE :search_name OR e.email LIKE :search_email';
        }

        $sql .= ' ORDER BY e.id DESC LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $stmt->bindValue(':search_cedula', $like, PDO::PARAM_STR);
            $stmt->bindValue(':search_name', $like, PDO::PARAM_STR);
            $stmt->bindValue(':search_email', $like, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO employees (group_id, cedula, full_name, email, pin_hash, active) VALUES (:group_id, :cedula, :full_name, :email, :pin_hash, :active)'
        );
        $stmt->execute([
            'group_id' => $data['group_id'],
            'cedula' => $data['cedula'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'pin_hash' => $data['pin_hash'],
            'active' => $data['active'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE employees SET group_id = :group_id, cedula = :cedula, full_name = :full_name, email = :email, active = :active WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'group_id' => $data['group_id'],
            'cedula' => $data['cedula'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'active' => $data['active'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM employees WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    public function findByCedulaAnyState(string $cedula): ?array
    {
        return $this->findByCedula($cedula, false);
    }
}
