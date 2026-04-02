<?php
$logs = $logs ?? [];

function audit_action_label(string $action): string
{
    return match ($action) {
        'group.created' => 'Grupo creado',
        'group.updated' => 'Grupo actualizado',
        'group.deleted' => 'Grupo eliminado',
        'employee.created' => 'Empleado creado',
        'employee.updated' => 'Empleado actualizado',
        'employee.deleted' => 'Empleado eliminado',
        'employee.bulk_deleted' => 'Eliminación masiva de empleados',
        'employee.imported' => 'Importación de empleados',
        'schedule.created' => 'Horario creado',
        'schedule.updated' => 'Horario actualizado',
        'schedule.deleted' => 'Horario eliminado',
        'schedule.bulk_deleted' => 'Eliminación masiva de horarios',
        'schedule.assigned' => 'Horario asignado',
        'schedule.unassigned' => 'Asignación eliminada',
        'attendance.marked' => 'Marcación registrada',
        default => $action,
    };
}

function audit_payload_summary(?string $payload): string
{
    if ($payload === null || $payload === '') {
        return '-';
    }

    $decoded = json_decode($payload, true);
    if (!is_array($decoded) || $decoded === []) {
        return $payload;
    }

    $parts = [];
    foreach ($decoded as $key => $value) {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $parts[] = $key . ': ' . (string) $value;
    }

    return implode(' | ', array_slice($parts, 0, 4));
}
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h2 class="h5 mb-0 font-weight-bold">Auditoría</h2>
                <p class="text-muted mb-0 small">Registro de acciones sensibles realizadas por administradores y el sistema.</p>
            </div>
            <div class="d-flex flex-wrap mt-2 mt-md-0">
                <span class="badge badge-light border p-2">Total registros: <?= number_format((int) ($logCount ?? 0)) ?></span>
            </div>
        </div>
    </div>
    <div class="table-responsive px-3 pb-3">
        <table class="table table-hover mb-0" id="auditTable">
            <thead class="thead-light">
                <tr>
                    <th>Fecha</th>
                    <th>Actor</th>
                    <th>Acción</th>
                    <th>Entidad</th>
                    <th>ID</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No hay eventos de auditoría registrados todavía.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($log['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="font-weight-bold text-gray-800"><?= htmlspecialchars((string) ($log['actor_label'] ?? 'Sistema'), ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="small text-muted"><?= htmlspecialchars((string) ($log['actor_type'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td><?= htmlspecialchars(audit_action_label((string) ($log['action'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($log['entity'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($log['entity_id'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(audit_payload_summary($log['payload'] ?? null), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>