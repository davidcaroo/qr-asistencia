<?php

declare(strict_types=1);

namespace App\Services;

use App\Infrastructure\Repositories\AttendanceRepository;
use App\Infrastructure\Repositories\EmployeeRepository;
use App\Infrastructure\Repositories\QrSessionRepository;
use DateTimeImmutable;
use PDOException;
use App\Services\AuditLogger;

final class AttendanceService
{
    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly AttendanceRepository $attendanceRepository,
        private readonly QrSessionRepository $qrSessionRepository,
        private readonly ScheduleService $scheduleService,
        private readonly QrTokenService $qrTokenService,
    ) {}

    public function register(string $cedula, string $token, string $ipAddress, string $userAgent): array
    {
        $now = new DateTimeImmutable('now');
        $lockMinutes = (int) config('app', 'attendance_lock_minutes', 5);
        $tokenHash = hash('sha256', $token);

        if (!$this->qrTokenService->validate($token, $now)) {
            return ['ok' => false, 'message' => 'QR vencido o inválido.'];
        }

        $qrSession = $this->qrSessionRepository->findValidByTokenHash($tokenHash, $now);
        if ($qrSession === null) {
            return ['ok' => false, 'message' => 'La sesión QR ya no está disponible.'];
        }

        $employee = $this->employeeRepository->findByCedula($cedula);
        if ($employee === null) {
            return ['ok' => false, 'message' => 'Cédula no encontrada.'];
        }

        $recent = $this->attendanceRepository->recentForEmployee((int) $employee['id'], $lockMinutes);
        if ($recent !== null) {
            return ['ok' => false, 'message' => 'Ya registraste asistencia hace menos de ' . $lockMinutes . ' minutos.'];
        }

        $lastToday = $this->attendanceRepository->lastTodayForEmployee((int) $employee['id']);
        $markType = (!$lastToday || $lastToday['mark_type'] === 'exit') ? 'entry' : 'exit';

        $schedule = $this->scheduleService->resolveForEmployeeGroup($employee['group_id'] ? (int) $employee['group_id'] : null, $now);
        $scheduleState = $schedule ? $this->scheduleService->evaluate($schedule, $now, $markType) : 'unscheduled';

        $attemptBucket = intdiv($now->getTimestamp(), $lockMinutes * 60);

        try {
            $attendanceId = $this->attendanceRepository->create([
                'employee_id' => (int) $employee['id'],
                'schedule_id' => $schedule['id'] ?? null,
                'qr_session_id' => (int) $qrSession['id'],
                'mark_type' => $markType,
                'schedule_state' => $scheduleState,
                'attendance_date' => $now->format('Y-m-d'),
                'attendance_time' => $now->format('H:i:s'),
                'marked_at' => $now->format('Y-m-d H:i:s'),
                'attempt_bucket' => $attemptBucket,
                'source' => 'qr_global',
                'ip_address' => $ipAddress,
                'user_agent' => mb_substr($userAgent, 0, 255),
                'notes' => null,
            ]);

            AuditLogger::recordEmployee((int) $employee['id'], 'attendance.marked', 'attendance_record', $attendanceId, [
                'employee_id' => (int) $employee['id'],
                'mark_type' => $markType,
                'schedule_state' => $scheduleState,
                'schedule_id' => $schedule['id'] ?? null,
                'qr_session_id' => (int) $qrSession['id'],
                'attendance_date' => $now->format('Y-m-d'),
                'attendance_time' => $now->format('H:i:s'),
            ], $ipAddress);
        } catch (PDOException $exception) {
            return [
                'ok' => false,
                'message' => 'No se pudo registrar la asistencia. Intenta nuevamente.',
                'error' => $exception->getCode(),
            ];
        }

        return [
            'ok' => true,
            'message' => $markType === 'entry' ? 'Entrada registrada correctamente.' : 'Salida registrada correctamente.',
            'attendance_id' => $attendanceId,
            'employee' => $employee,
            'mark_type' => $markType,
            'schedule_state' => $scheduleState,
        ];
    }
}
