<?php

$employee = $employee ?? [];
$currentSchedule = $currentSchedule ?? null;
$assignments = $assignments ?? [];

$dayLabels = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo',
];
?>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-uppercase small text-gray-500 mb-1">Empleado</div>
                <h2 class="h4 font-weight-bold mb-3"><?= htmlspecialchars($employee['full_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></h2>

                <dl class="row mb-0 small">
                    <dt class="col-5 text-gray-600">Cédula</dt>
                    <dd class="col-7"><?= htmlspecialchars($employee['cedula'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-5 text-gray-600">Correo</dt>
                    <dd class="col-7"><?= htmlspecialchars($employee['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-5 text-gray-600">Grupo</dt>
                    <dd class="col-7"><?= htmlspecialchars($employee['group_name'] ?? 'Sin grupo', ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt class="col-5 text-gray-600">Estado</dt>
                    <dd class="col-7">
                        <span class="badge badge-<?= ((int) ($employee['active'] ?? 0) === 1) ? 'success' : 'secondary' ?>">
                            <?= ((int) ($employee['active'] ?? 0) === 1) ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </dd>
                </dl>
            </div>
            <div class="card-footer bg-white d-flex flex-wrap justify-content-between">
                <a href="<?= htmlspecialchars(site_url('admin/empleados'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm mb-2 btn-icon-label">
                    <i class="bi bi-arrow-left mr-2"></i>
                    <span>Volver</span>
                </a>
                <?php if (\App\Core\Auth::can('employees.manage')): ?>
                    <div>
                        <a href="<?= htmlspecialchars(site_url('admin/empleados/' . (int) ($employee['id'] ?? 0) . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-primary btn-sm mb-2 btn-icon-label">
                            <i class="bi bi-pencil mr-2"></i>
                            <span>Editar</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h3 class="h6 mb-0 font-weight-bold">Horario activo de hoy</h3>
            </div>
            <div class="card-body">
                <?php if ($currentSchedule !== null): ?>
                    <div class="mb-2">
                        <div class="text-uppercase small text-gray-500">Horario</div>
                        <div class="h5 font-weight-bold mb-0"><?= htmlspecialchars($currentSchedule['name'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="mb-2 small text-gray-700">
                        <strong>Inicio:</strong> <?= htmlspecialchars($currentSchedule['start_time'], ENT_QUOTES, 'UTF-8') ?><br>
                        <strong>Fin:</strong> <?= htmlspecialchars($currentSchedule['end_time'], ENT_QUOTES, 'UTF-8') ?><br>
                        <strong>Tolerancia:</strong> <?= (int) $currentSchedule['tolerance_before_minutes'] ?> antes / <?= (int) $currentSchedule['tolerance_after_minutes'] ?> después
                    </div>
                    <span class="badge badge-success">Activo para este grupo hoy</span>
                <?php else: ?>
                    <div class="alert alert-warning border-0 mb-0">
                        No hay un horario activo para este grupo en la fecha actual.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h3 class="h6 mb-0 font-weight-bold">Resumen de grupo</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($employee['group_name'])): ?>
                    <p class="mb-2"><strong>Grupo:</strong> <?= htmlspecialchars($employee['group_name'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mb-0 text-muted small">Las asignaciones debajo determinan qué horario aplica según día y vigencia.</p>
                <?php else: ?>
                    <div class="alert alert-info border-0 mb-0">Este empleado no tiene un grupo asignado.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h3 class="h6 mb-0 font-weight-bold">Asignaciones del grupo</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Horario</th>
                    <th>Día</th>
                    <th>Vigencia</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">No hay asignaciones registradas para este grupo.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?= htmlspecialchars($assignment['schedule_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($dayLabels[(int) ($assignment['day_of_week'] ?? 0)] ?? 'Todos los días', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(($assignment['valid_from'] ?? '-') . ' / ' . ($assignment['valid_to'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge badge-<?= ((int) $assignment['active'] === 1) ? 'success' : 'secondary' ?>">
                                    <?= ((int) $assignment['active'] === 1) ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>