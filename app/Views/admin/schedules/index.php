<?php
$days = [
    '' => 'Todos los días',
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo',
];

$scheduleCount = (int) ($scheduleCount ?? 0);
$assignmentCount = (int) ($assignmentCount ?? 0);
$groupCount = (int) ($groupCount ?? 0);
?>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex align-items-start justify-content-between flex-wrap">
                    <div class="pr-3 mb-3">
                        <h2 class="h5 mb-1 font-weight-bold">Horarios</h2>
                        <p class="text-muted mb-0 small">Plantillas maestras de asistencia y sus reglas por grupo.</p>
                    </div>
                    <a href="<?= htmlspecialchars(site_url('admin/horarios/nuevo'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm btn-icon-label mb-3">
                        <i class="bi bi-plus-circle mr-2"></i>
                        <span>Nuevo horario</span>
                    </a>
                </div>

                <div class="row no-gutters mb-3">
                    <div class="col-md-4 pr-md-2 mb-2 mb-md-0">
                        <div class="border rounded-lg p-3 h-100 bg-light">
                            <div class="small text-uppercase text-muted font-weight-bold">Horarios</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-900"><?= $scheduleCount ?></div>
                        </div>
                    </div>
                    <div class="col-md-4 pr-md-2 mb-2 mb-md-0">
                        <div class="border rounded-lg p-3 h-100 bg-light">
                            <div class="small text-uppercase text-muted font-weight-bold">Asignaciones</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-900"><?= $assignmentCount ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-lg p-3 h-100 bg-light">
                            <div class="small text-uppercase text-muted font-weight-bold">Grupos</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-900"><?= $groupCount ?></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between bulk-actions-bar border-top pt-3 mb-4">
                    <div class="small text-muted">Selecciona varios horarios para borrarlos de una vez.</div>
                    <form id="scheduleBulkDeleteForm" method="post" action="<?= htmlspecialchars(site_url('admin/horarios/eliminar-masivo'), ENT_QUOTES, 'UTF-8') ?>" class="d-inline-flex align-items-center" data-confirm-bulk-delete data-bulk-label="horarios" data-bulk-singular="horario">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-danger btn-sm btn-icon-label">
                            <i class="bi bi-trash mr-2"></i>
                            <span>Eliminar seleccionados</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="table-responsive px-3 pb-3 pt-1">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="bulk-select-column text-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="scheduleSelectAll">
                                    <label class="custom-control-label" for="scheduleSelectAll"></label>
                                </div>
                            </th>
                            <th>Nombre</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Tolerancia</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedules ?? [])): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">No hay horarios registrados todavía.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($schedules as $schedule): ?>
                                <tr>
                                    <td class="text-center align-middle">
                                        <div class="custom-control custom-checkbox">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input bulk-row-checkbox"
                                                id="scheduleSelect<?= (int) $schedule['id'] ?>"
                                                name="selected_ids[]"
                                                value="<?= (int) $schedule['id'] ?>"
                                                form="scheduleBulkDeleteForm"
                                                data-bulk-item="schedule">
                                            <label class="custom-control-label" for="scheduleSelect<?= (int) $schedule['id'] ?>"></label>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($schedule['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($schedule['start_time'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($schedule['end_time'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) $schedule['tolerance_before_minutes'] ?> / <?= (int) $schedule['tolerance_after_minutes'] ?> min</td>
                                    <td>
                                        <span class="badge badge-<?= ((int) $schedule['active'] === 1) ? 'success' : 'secondary' ?>">
                                            <?= ((int) $schedule['active'] === 1) ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td class="text-right table-actions pr-4 text-nowrap">
                                        <div class="d-inline-flex align-items-center justify-content-end flex-wrap">
                                            <a href="<?= htmlspecialchars(site_url('admin/horarios/' . (int) $schedule['id'] . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-primary btn-icon-only mb-1 mr-1" aria-label="Editar horario" title="Editar horario">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="post" action="<?= htmlspecialchars(site_url('admin/horarios/' . (int) $schedule['id'] . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="d-inline mb-1" data-confirm-delete>
                                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-only" aria-label="Eliminar horario" title="Eliminar horario">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 pb-0">
                <h3 class="h6 mb-1 font-weight-bold">Asignar horario a grupo</h3>
                <p class="text-muted mb-0 small">Una sola plantilla puede aplicarse a varios días del mismo grupo.</p>
            </div>
            <div class="card-body pt-3">
                <form method="post" action="<?= htmlspecialchars(site_url('admin/horarios/asignar'), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                    <div class="form-group mb-3">
                        <label for="schedule_id">Horario</label>
                        <select id="schedule_id" name="schedule_id" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ($scheduleOptions ?? [] as $schedule): ?>
                                <option value="<?= (int) $schedule['id'] ?>"><?= htmlspecialchars($schedule['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="group_id">Grupo</label>
                        <select id="group_id" name="group_id" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ($groups ?? [] as $group): ?>
                                <option value="<?= (int) $group['id'] ?>"><?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="day_of_week">Días aplicables</label>
                        <select id="day_of_week" name="day_of_week[]" class="form-control" multiple size="7">
                            <?php foreach ($days as $value => $label): ?>
                                <?php if ($value === ''): ?>
                                    <?php continue; ?>
                                <?php endif; ?>
                                <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Mantén presionada la tecla Ctrl en Windows para seleccionar varios días.</small>
                        <div class="btn-group btn-group-sm mt-2 flex-wrap" role="group" aria-label="Presets de días">
                            <button type="button" class="btn btn-outline-secondary mb-1 mr-1" data-day-preset="weekday">Lun-Vie</button>
                            <button type="button" class="btn btn-outline-secondary mb-1 mr-1" data-day-preset="saturday">Sábado</button>
                            <button type="button" class="btn btn-outline-secondary mb-1 mr-1" data-day-preset="weekend">Sáb-Dom</button>
                            <button type="button" class="btn btn-outline-secondary mb-1 mr-1" data-day-preset="all">Todos</button>
                            <button type="button" class="btn btn-outline-light mb-1" data-day-preset="clear">Limpiar</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 form-group mb-3">
                            <label for="valid_from">Válido desde</label>
                            <input type="date" id="valid_from" name="valid_from" class="form-control">
                        </div>
                        <div class="col-6 form-group mb-3">
                            <label for="valid_to">Válido hasta</label>
                            <input type="date" id="valid_to" name="valid_to" class="form-control">
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="assignment_active">Estado</label>
                        <select id="assignment_active" name="active" class="form-control">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-icon-label">
                        <i class="bi bi-link-45deg mr-2"></i>
                        <span>Asignar horario</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 pb-0">
                <h3 class="h6 mb-1 font-weight-bold">Asignaciones activas</h3>
                <p class="text-muted mb-0 small">Cada fila representa una regla aplicada a un grupo y día específico.</p>
            </div>
            <div class="table-responsive px-3 pb-3">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Grupo</th>
                            <th>Horario</th>
                            <th>Día</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignments ?? [])): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">No hay asignaciones registradas todavía.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td><?= htmlspecialchars($assignment['group_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($assignment['schedule_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($days[$assignment['day_of_week'] ?? ''] ?? 'Todos los días', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(($assignment['valid_from'] ?? '-') . ' / ' . ($assignment['valid_to'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="badge badge-<?= ((int) $assignment['active'] === 1) ? 'success' : 'secondary' ?>">
                                            <?= ((int) $assignment['active'] === 1) ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td class="text-right table-actions pr-4 text-nowrap">
                                        <form method="post" action="<?= htmlspecialchars(site_url('admin/horarios/asignaciones/' . (int) $assignment['id'] . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="d-inline" data-confirm-delete>
                                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-label mb-1">
                                                <i class="bi bi-x-circle mr-2"></i>
                                                <span>Quitar</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var select = document.getElementById('day_of_week');
        if (!select) {
            return;
        }

        function setSelected(days) {
            Array.prototype.forEach.call(select.options, function(option) {
                option.selected = days.indexOf(option.value) !== -1;
            });
        }

        document.querySelectorAll('[data-day-preset]').forEach(function(button) {
            button.addEventListener('click', function() {
                var preset = button.getAttribute('data-day-preset');

                if (preset === 'weekday') {
                    setSelected(['1', '2', '3', '4', '5']);
                } else if (preset === 'saturday') {
                    setSelected(['6']);
                } else if (preset === 'weekend') {
                    setSelected(['6', '7']);
                } else if (preset === 'all') {
                    setSelected(['1', '2', '3', '4', '5', '6', '7']);
                } else {
                    setSelected([]);
                }
            });
        });
    }());
</script>