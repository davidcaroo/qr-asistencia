<?php

declare(strict_types=1);

namespace App\Services;

use App\Infrastructure\Repositories\ScheduleRepository;
use DateTimeImmutable;

final class ScheduleService
{
    public function __construct(private readonly ScheduleRepository $scheduleRepository) {}

    public function resolveForEmployeeGroup(?int $groupId, ?DateTimeImmutable $now = null): ?array
    {
        if ($groupId === null) {
            return null;
        }

        $now = $now ?? new DateTimeImmutable('now');
        $dayOfWeek = ((int) $now->format('N'));

        return $this->scheduleRepository->activeForGroupAndDay($groupId, $dayOfWeek, $now->format('Y-m-d'));
    }

    public function evaluate(array $schedule, DateTimeImmutable $now, string $markType): string
    {
        $currentTime = $now->format('H:i:s');
        $startTime = $schedule['start_time'];
        $endTime = $schedule['end_time'];
        $beforeMinutes = (int) $schedule['tolerance_before_minutes'];
        $afterMinutes = (int) $schedule['tolerance_after_minutes'];

        if ($markType === 'entry') {
            $windowStart = (new DateTimeImmutable($now->format('Y-m-d') . ' ' . $startTime))->modify('-' . $beforeMinutes . ' minutes')->format('H:i:s');
            $windowEnd = (new DateTimeImmutable($now->format('Y-m-d') . ' ' . $startTime))->modify('+' . $afterMinutes . ' minutes')->format('H:i:s');

            if ($currentTime < $windowStart || $currentTime > $windowEnd) {
                return 'outside_window';
            }

            return $currentTime > $startTime ? 'late' : 'on_time';
        }

        $windowStart = (new DateTimeImmutable($now->format('Y-m-d') . ' ' . $endTime))->modify('-' . $beforeMinutes . ' minutes')->format('H:i:s');
        $windowEnd = (new DateTimeImmutable($now->format('Y-m-d') . ' ' . $endTime))->modify('+' . $afterMinutes . ' minutes')->format('H:i:s');

        if ($currentTime < $windowStart || $currentTime > $windowEnd) {
            return 'outside_window';
        }

        return $currentTime < $endTime ? 'early' : 'on_time';
    }
}
