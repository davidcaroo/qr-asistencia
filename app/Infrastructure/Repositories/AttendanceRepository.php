<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Core\Database;
use PDO;

final class AttendanceRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function recentForEmployee(int $employeeId, int $minutes): ?array
    {
        $cutoff = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->modify('-' . $minutes . ' minutes');
        $stmt = $this->pdo->prepare('
            SELECT *
            FROM attendance_records
            WHERE employee_id = :employee_id
                            AND marked_at >= :cutoff
            ORDER BY marked_at DESC
            LIMIT 1
        ');
        $stmt->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->bindValue(':cutoff', $cutoff->format('Y-m-d H:i:s'));
        $stmt->execute();

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function lastTodayForEmployee(int $employeeId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT *
            FROM attendance_records
            WHERE employee_id = :employee_id
              AND attendance_date = UTC_DATE()
            ORDER BY marked_at DESC
            LIMIT 1
        ');
        $stmt->execute(['employee_id' => $employeeId]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO attendance_records (
                employee_id,
                schedule_id,
                qr_session_id,
                mark_type,
                schedule_state,
                attendance_date,
                attendance_time,
                marked_at,
                attempt_bucket,
                source,
                ip_address,
                user_agent,
                notes
            ) VALUES (
                :employee_id,
                :schedule_id,
                :qr_session_id,
                :mark_type,
                :schedule_state,
                :attendance_date,
                :attendance_time,
                :marked_at,
                :attempt_bucket,
                :source,
                :ip_address,
                :user_agent,
                :notes
            )
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    public function countToday(): int
    {
        return $this->countTodayBy('');
    }

    public function countTodayEntries(): int
    {
        return $this->countTodayBy(' AND mark_type = :mark_type', ['mark_type' => 'entry']);
    }

    public function countTodayExits(): int
    {
        return $this->countTodayBy(' AND mark_type = :mark_type', ['mark_type' => 'exit']);
    }

    public function countTodayLateEntries(): int
    {
        return $this->countTodayBy(
            ' AND mark_type = :mark_type AND schedule_state = :schedule_state',
            ['mark_type' => 'entry', 'schedule_state' => 'late']
        );
    }

    private function countTodayBy(string $whereSql, array $params = []): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM attendance_records WHERE attendance_date = UTC_DATE()' . $whereSql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }
}
