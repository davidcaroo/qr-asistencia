<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Core\Database;
use PDO;

final class ScheduleRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function activeForGroupAndDay(int $groupId, int $dayOfWeek, ?string $date = null): ?array
    {
        $sql = '
            SELECT ws.*, gsa.group_id
            FROM work_schedules ws
            INNER JOIN group_schedule_assignments gsa ON gsa.schedule_id = ws.id
            WHERE gsa.group_id = :group_id
              AND ws.active = 1
              AND gsa.active = 1
              AND (gsa.day_of_week IS NULL OR gsa.day_of_week = :day_of_week)
              AND (gsa.valid_from IS NULL OR gsa.valid_from <= :current_date_from)
              AND (gsa.valid_to IS NULL OR gsa.valid_to >= :current_date_to)
            ORDER BY gsa.day_of_week IS NULL ASC, gsa.valid_from DESC, gsa.id DESC
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'group_id' => $groupId,
            'day_of_week' => $dayOfWeek,
            'current_date_from' => $date ?? gmdate('Y-m-d'),
            'current_date_to' => $date ?? gmdate('Y-m-d'),
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM work_schedules ORDER BY id DESC');

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM work_schedules WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO work_schedules (name, start_time, end_time, tolerance_before_minutes, tolerance_after_minutes, active) VALUES (:name, :start_time, :end_time, :tolerance_before_minutes, :tolerance_after_minutes, :active)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'tolerance_before_minutes' => $data['tolerance_before_minutes'],
            'tolerance_after_minutes' => $data['tolerance_after_minutes'],
            'active' => $data['active'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE work_schedules SET name = :name, start_time = :start_time, end_time = :end_time, tolerance_before_minutes = :tolerance_before_minutes, tolerance_after_minutes = :tolerance_after_minutes, active = :active WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'tolerance_before_minutes' => $data['tolerance_before_minutes'],
            'tolerance_after_minutes' => $data['tolerance_after_minutes'],
            'active' => $data['active'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM work_schedules WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    public function deleteMany(array $ids): int
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if ($ids === []) {
            return 0;
        }

        $placeholders = [];
        $params = [];

        foreach ($ids as $index => $id) {
            $placeholder = ':id' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $id;
        }

        $stmt = $this->pdo->prepare('DELETE FROM work_schedules WHERE id IN (' . implode(', ', $placeholders) . ')');
        $stmt->execute($params);

        return $stmt->rowCount();
    }
}
