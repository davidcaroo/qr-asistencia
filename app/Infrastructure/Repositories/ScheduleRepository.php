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
              AND (gsa.valid_from IS NULL OR gsa.valid_from <= :current_date)
              AND (gsa.valid_to IS NULL OR gsa.valid_to >= :current_date)
            ORDER BY gsa.id DESC
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'group_id' => $groupId,
            'day_of_week' => $dayOfWeek,
            'current_date' => $date ?? gmdate('Y-m-d'),
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM work_schedules ORDER BY id DESC');

        return $stmt->fetchAll();
    }
}
