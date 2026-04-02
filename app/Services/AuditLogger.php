<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Infrastructure\Repositories\AuditLogRepository;

final class AuditLogger
{
    public static function recordAdmin(string $action, string $entity, ?int $entityId = null, array $payload = [], ?string $ipAddress = null): void
    {
        try {
            $admin = Auth::admin();

            if ($admin === null) {
                return;
            }

            (new AuditLogRepository())->create([
                'actor_type' => 'admin',
                'actor_id' => (int) $admin['id'],
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'payload' => self::encodePayload($payload),
                'ip_address' => $ipAddress,
            ]);
        } catch (\Throwable) {
            return;
        }
    }

    public static function recordEmployee(int $employeeId, string $action, string $entity, ?int $entityId = null, array $payload = [], ?string $ipAddress = null): void
    {
        try {
            (new AuditLogRepository())->create([
                'actor_type' => 'employee',
                'actor_id' => $employeeId,
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'payload' => self::encodePayload($payload),
                'ip_address' => $ipAddress,
            ]);
        } catch (\Throwable) {
            return;
        }
    }

    private static function encodePayload(array $payload): ?string
    {
        if ($payload === []) {
            return null;
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }
}
