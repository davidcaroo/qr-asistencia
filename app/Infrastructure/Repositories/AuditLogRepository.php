<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Core\Database;
use PDO;

final class AuditLogRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO audit_logs (
                actor_type,
                actor_id,
                action,
                entity,
                entity_id,
                payload,
                ip_address
            ) VALUES (
                :actor_type,
                :actor_id,
                :action,
                :entity,
                :entity_id,
                :payload,
                :ip_address
            )
        ');
        $stmt->execute([
            'actor_type' => $data['actor_type'],
            'actor_id' => $data['actor_id'],
            'action' => $data['action'],
            'entity' => $data['entity'],
            'entity_id' => $data['entity_id'],
            'payload' => $data['payload'],
            'ip_address' => $data['ip_address'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function recentDetailed(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));

        $stmt = $this->pdo->prepare('
            SELECT
                al.*,
                CASE
                    WHEN al.actor_type = "admin" THEN CONCAT(COALESCE(au.name, "Admin"), " <", COALESCE(au.email, "-"), ">")
                    WHEN al.actor_type = "employee" THEN CONCAT(COALESCE(e.full_name, "Empleado"), " (", COALESCE(e.cedula, "-"), ")")
                    ELSE "Sistema"
                END AS actor_label,
                COALESCE(au.role, e.full_name, "system") AS actor_role
            FROM audit_logs al
            LEFT JOIN admin_users au ON al.actor_type = "admin" AND au.id = al.actor_id
            LEFT JOIN employees e ON al.actor_type = "employee" AND e.id = al.actor_id
            ORDER BY al.id DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
