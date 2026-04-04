<?php
$logs = $logs ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? [];

$selectedSearch = (string) ($filters['search'] ?? '');
$selectedActorType = (string) ($filters['actor_type'] ?? '');
$selectedAction = (string) ($filters['action'] ?? '');
$selectedEntity = (string) ($filters['entity'] ?? '');
$selectedDateFrom = (string) ($filters['date_from'] ?? '');
$selectedDateTo = (string) ($filters['date_to'] ?? '');
$selectedPerPage = (int) ($pagination['perPage'] ?? 10);

$baseQuery = array_filter([
    'search' => $selectedSearch,
    'actor_type' => $selectedActorType,
    'action' => $selectedAction,
    'entity' => $selectedEntity,
    'date_from' => $selectedDateFrom,
    'date_to' => $selectedDateTo,
    'per_page' => $selectedPerPage,
], static fn ($value) => $value !== '' && $value !== null);

$buildPageUrl = static function (int $page) use ($baseQuery): string {
    return site_url('admin/auditoria?' . http_build_query($baseQuery + ['page' => $page]));
};

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
        'role.created' => 'Rol creado',
        'role.updated' => 'Rol actualizado',
        'role.deleted' => 'Rol eliminado',
        'admin_user.created' => 'Usuario administrador creado',
        'admin_user.updated' => 'Usuario administrador actualizado',
        'admin_user.deleted' => 'Usuario administrador eliminado',
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
                <span class="badge badge-light border p-2">Total coincidencias: <?= number_format((int) ($logCount ?? 0)) ?></span>
                <span class="badge badge-light border p-2 ml-2">Mostrando <?= number_format((int) ($pagination['from'] ?? 0)) ?>-<?= number_format((int) ($pagination['to'] ?? 0)) ?></span>
            </div>
        </div>
    </div>
    <div class="card-body border-bottom bg-light">
        <form method="get" action="<?= htmlspecialchars(site_url('admin/auditoria'), ENT_QUOTES, 'UTF-8') ?>" class="row align-items-end">
            <div class="col-lg-4 col-md-6 mb-3">
                <label class="small text-uppercase font-weight-bold text-gray-700">Buscar</label>
                <input type="search" name="search" class="form-control" placeholder="Actor, acción, entidad, IP o detalle" value="<?= htmlspecialchars($selectedSearch, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-lg-2 col-md-6 mb-3">
                <label class="small text-uppercase font-weight-bold text-gray-700">Actor</label>
                <select name="actor_type" class="form-control">
                    <option value="">Todos</option>
                    <option value="admin" <?= $selectedActorType === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    <option value="employee" <?= $selectedActorType === 'employee' ? 'selected' : '' ?>>Empleado</option>
                    <option value="system" <?= $selectedActorType === 'system' ? 'selected' : '' ?>>Sistema</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6 mb-3">
                <label class="small text-uppercase font-weight-bold text-gray-700">Acción</label>
                <input type="text" name="action" class="form-control" placeholder="group.created" value="<?= htmlspecialchars($selectedAction, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-lg-2 col-md-6 mb-3">
                <label class="small text-uppercase font-weight-bold text-gray-700">Entidad</label>
                <input type="text" name="entity" class="form-control" placeholder="employee" value="<?= htmlspecialchars($selectedEntity, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-lg-1 col-md-6 mb-3">
                <label class="small text-uppercase font-weight-bold text-gray-700">Desde</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($selectedDateFrom, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-lg-1 col-md-6 mb-3">
                <label class="small text-uppercase font-weight-bold text-gray-700">Hasta</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($selectedDateTo, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-lg-2 col-md-6 mb-3">
                <label class="small text-uppercase font-weight-bold text-gray-700">Por página</label>
                <select name="per_page" class="form-control">
                    <option value="10" <?= $selectedPerPage === 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $selectedPerPage === 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $selectedPerPage === 50 ? 'selected' : '' ?>>50</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6 mb-3 d-flex">
                <button type="submit" class="btn btn-primary mr-2 flex-fill">
                    <i class="bi bi-funnel-fill mr-1"></i> Filtrar
                </button>
                <a class="btn btn-outline-secondary flex-fill" href="<?= htmlspecialchars(site_url('admin/auditoria'), ENT_QUOTES, 'UTF-8') ?>">Limpiar</a>
            </div>
        </form>
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
                        <td colspan="6" class="text-center text-muted py-5">No hay eventos de auditoría para los filtros seleccionados.</td>
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
    <?php if ((int) ($pagination['totalPages'] ?? 1) > 1): ?>
        <div class="card-footer bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="text-muted small mb-3 mb-md-0">
                Página <?= number_format((int) ($pagination['currentPage'] ?? 1)) ?> de <?= number_format((int) ($pagination['totalPages'] ?? 1)) ?>.
            </div>
            <nav aria-label="Paginación de auditoría">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= ((int) ($pagination['currentPage'] ?? 1) <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildPageUrl(max(1, (int) ($pagination['currentPage'] ?? 1) - 1)), ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
                    </li>
                    <?php
                    $currentPage = (int) ($pagination['currentPage'] ?? 1);
                    $totalPages = (int) ($pagination['totalPages'] ?? 1);
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    if ($startPage > 1) {
                        $endPage = min($totalPages, $endPage + ($startPage - 1));
                    }
                    if ($endPage < $totalPages) {
                        $startPage = max(1, $startPage - ($totalPages - $endPage));
                    }
                    ?>
                    <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                        <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($buildPageUrl($page), ENT_QUOTES, 'UTF-8') ?>"><?= number_format($page) ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ((int) ($pagination['currentPage'] ?? 1) >= (int) ($pagination['totalPages'] ?? 1)) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildPageUrl(min((int) ($pagination['totalPages'] ?? 1), (int) ($pagination['currentPage'] ?? 1) + 1)), ENT_QUOTES, 'UTF-8') ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>