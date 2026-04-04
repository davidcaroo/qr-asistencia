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

    public function countDetailed(array $filters = []): int
    {
        [$whereSql, $params] = $this->buildDetailedQueryParts($filters);

        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) AS total
            FROM audit_logs al
            LEFT JOIN admin_users au ON al.actor_type = "admin" AND au.id = al.actor_id
            LEFT JOIN employees e ON al.actor_type = "employee" AND e.id = al.actor_id
            ' . $whereSql . '
        ');
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function paginateDetailed(int $limit = 10, int $offset = 0, array $filters = []): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);
        [$whereSql, $params] = $this->buildDetailedQueryParts($filters);

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
            ' . $whereSql . '
            ORDER BY al.id DESC
            LIMIT :limit OFFSET :offset
        ');
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function buildDetailedQueryParts(array $filters): array
    {
        $where = [];
        $params = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $where[] = '(
                al.action LIKE :search
                OR al.entity LIKE :search
                OR CAST(al.entity_id AS CHAR) LIKE :search
                OR CAST(al.payload AS CHAR) LIKE :search
                OR al.ip_address LIKE :search
                OR au.name LIKE :search
                OR au.email LIKE :search
                OR e.full_name LIKE :search
                OR e.cedula LIKE :search
            )';
            $params[':search'] = '%' . $search . '%';
        }

        $actorType = trim((string) ($filters['actor_type'] ?? ''));
        if ($actorType !== '') {
            $allowedActorTypes = ['admin', 'employee', 'system'];
            if (in_array($actorType, $allowedActorTypes, true)) {
                $where[] = 'al.actor_type = :actor_type';
                $params[':actor_type'] = $actorType;
            }
        }

        $action = trim((string) ($filters['action'] ?? ''));
        if ($action !== '') {
            $where[] = 'al.action LIKE :action';
            $params[':action'] = '%' . $action . '%';
        }

        $entity = trim((string) ($filters['entity'] ?? ''));
        if ($entity !== '') {
            $where[] = 'al.entity LIKE :entity';
            $params[':entity'] = '%' . $entity . '%';
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $where[] = 'al.created_at >= :date_from';
            $params[':date_from'] = $dateFrom . ' 00:00:00';
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $where[] = 'al.created_at < DATE_ADD(:date_to, INTERVAL 1 DAY)';
            $params[':date_to'] = $dateTo . ' 00:00:00';
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';

        return [$whereSql, $params];
    }
}
