<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Core\Database;
use PDO;

final class GroupScheduleAssignmentRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function allDetailed(): array
    {
        $stmt = $this->pdo->query(
            'SELECT gsa.*, ws.name AS schedule_name, ws.start_time, ws.end_time, ws.active AS schedule_active, g.name AS group_name FROM group_schedule_assignments gsa INNER JOIN work_schedules ws ON ws.id = gsa.schedule_id INNER JOIN employee_groups g ON g.id = gsa.group_id ORDER BY gsa.id DESC'
        );

        return $stmt->fetchAll();
    }

    public function allByGroupId(int $groupId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT gsa.*, ws.name AS schedule_name, ws.start_time, ws.end_time, ws.tolerance_before_minutes, ws.tolerance_after_minutes, ws.active AS schedule_active, g.name AS group_name FROM group_schedule_assignments gsa INNER JOIN work_schedules ws ON ws.id = gsa.schedule_id INNER JOIN employee_groups g ON g.id = gsa.group_id WHERE gsa.group_id = :group_id ORDER BY gsa.id DESC'
        );
        $stmt->execute(['group_id' => $groupId]);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT gsa.*, ws.name AS schedule_name, g.name AS group_name FROM group_schedule_assignments gsa INNER JOIN work_schedules ws ON ws.id = gsa.schedule_id INNER JOIN employee_groups g ON g.id = gsa.group_id WHERE gsa.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO group_schedule_assignments (group_id, schedule_id, day_of_week, valid_from, valid_to, active) VALUES (:group_id, :schedule_id, :day_of_week, :valid_from, :valid_to, :active)'
        );
        $stmt->execute([
            'group_id' => $data['group_id'],
            'schedule_id' => $data['schedule_id'],
            'day_of_week' => $data['day_of_week'],
            'valid_from' => $data['valid_from'],
            'valid_to' => $data['valid_to'],
            'active' => $data['active'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM group_schedule_assignments WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
