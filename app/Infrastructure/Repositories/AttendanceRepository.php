<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Core\Database;
use PDO;
use DateTimeImmutable;
use DateTimeZone;

final class AttendanceRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function recentForEmployee(int $employeeId, int $minutes): ?array
    {
        $cutoff = $this->now()->modify('-' . $minutes . ' minutes');
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
              AND attendance_date = :attendance_date
            ORDER BY marked_at DESC
            LIMIT 1
        ');
        $stmt->execute([
            'employee_id' => $employeeId,
            'attendance_date' => $this->todayDate(),
        ]);

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
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM attendance_records WHERE attendance_date = :attendance_date' . $whereSql);
        $stmt->execute(array_merge(['attendance_date' => $this->todayDate()], $params));

        return (int) $stmt->fetchColumn();
    }

    private function now(): DateTimeImmutable
    {
        $timezone = new DateTimeZone((string) config('app', 'timezone', 'UTC'));

        return new DateTimeImmutable('now', $timezone);
    }

    private function todayDate(): string
    {
        return $this->now()->format('Y-m-d');
    }

    public function monthlyEmployeeSummary(DateTimeImmutable $startDate, DateTimeImmutable $endDate, ?int $employeeId = null, ?int $groupId = null): array
    {
        $sql = '
            SELECT
                e.id AS employee_id,
                e.cedula,
                e.full_name,
                e.email,
                g.name AS group_name,
                COUNT(ar.id) AS total_records,
                SUM(CASE WHEN ar.mark_type = "entry" THEN 1 ELSE 0 END) AS total_entries,
                SUM(CASE WHEN ar.mark_type = "exit" THEN 1 ELSE 0 END) AS total_exits,
                SUM(CASE WHEN ar.mark_type = "entry" AND ar.schedule_state = "late" THEN 1 ELSE 0 END) AS total_late_entries,
                MIN(CASE WHEN ar.mark_type = "entry" THEN ar.marked_at END) AS first_entry_at,
                MAX(CASE WHEN ar.mark_type = "exit" THEN ar.marked_at END) AS last_exit_at,
                MAX(ar.marked_at) AS last_mark_at
            FROM employees e
            LEFT JOIN employee_groups g ON g.id = e.group_id
            LEFT JOIN attendance_records ar ON ar.employee_id = e.id
                AND ar.attendance_date BETWEEN :start_date AND :end_date
            WHERE e.active = 1
        ';

        $params = [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ];

        if ($employeeId !== null) {
            $sql .= ' AND e.id = :employee_id';
            $params['employee_id'] = $employeeId;
        }

        if ($groupId !== null) {
            $sql .= ' AND e.group_id = :group_id';
            $params['group_id'] = $groupId;
        }

        $sql .= '
            GROUP BY e.id, e.cedula, e.full_name, e.email, g.name
            ORDER BY g.name ASC, e.full_name ASC
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
