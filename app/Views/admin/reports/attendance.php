<?php

$selectedMonth = (string) ($filters['month'] ?? '');
$selectedEmployeeId = (string) ($filters['employee_id'] ?? '');
$selectedGroupId = (string) ($filters['group_id'] ?? '');

$exportQuery = array_filter([
    'month' => $selectedMonth,
    'employee_id' => $selectedEmployeeId,
    'group_id' => $selectedGroupId,
]);

$exportCsvUrl = site_url('admin/reportes/asistencia/exportar?' . http_build_query($exportQuery + ['format' => 'csv']));
$exportXlsUrl = site_url('admin/reportes/asistencia/exportar?' . http_build_query($exportQuery + ['format' => 'xls']));
$exportXlsxUrl = site_url('admin/reportes/asistencia/exportar?' . http_build_query($exportQuery + ['format' => 'xlsx']));

?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">Reporte mensual de asistencia</h1>
        <p class="mb-0 text-muted">Consulta entradas, salidas y tardanzas por empleado para <?= htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8') ?>.</p>
    </div>
    <div class="mt-3 mt-md-0 d-flex flex-wrap">
        <a class="btn btn-outline-primary btn-sm mr-2 mb-2" href="<?= htmlspecialchars($exportCsvUrl, ENT_QUOTES, 'UTF-8') ?>">
            <i class="bi bi-filetype-csv mr-1"></i> CSV
        </a>
        <a class="btn btn-outline-success btn-sm mr-2 mb-2" href="<?= htmlspecialchars($exportXlsUrl, ENT_QUOTES, 'UTF-8') ?>">
            <i class="bi bi-file-earmark-excel mr-1"></i> XLS
        </a>
        <a class="btn btn-success btn-sm mb-2" href="<?= htmlspecialchars($exportXlsxUrl, ENT_QUOTES, 'UTF-8') ?>">
            <i class="bi bi-file-earmark-spreadsheet mr-1"></i> XLSX
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Empleados en reporte</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format((int) ($totals['employees'] ?? 0)) ?></div>
                    </div>
                    <div class="col-auto"><i class="bi bi-people-fill text-gray-300" style="font-size: 2rem;"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Entradas del mes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format((int) ($totals['entries'] ?? 0)) ?></div>
                    </div>
                    <div class="col-auto"><i class="bi bi-box-arrow-in-right text-gray-300" style="font-size: 2rem;"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Salidas del mes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format((int) ($totals['exits'] ?? 0)) ?></div>
                    </div>
                    <div class="col-auto"><i class="bi bi-box-arrow-right text-gray-300" style="font-size: 2rem;"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Tardanzas del mes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format((int) ($totals['late_entries'] ?? 0)) ?></div>
                    </div>
                    <div class="col-auto"><i class="bi bi-alarm text-gray-300" style="font-size: 2rem;"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
        <div>
            <h6 class="m-0 font-weight-bold text-primary">Filtros del reporte</h6>
            <small class="text-muted">Ajusta el mes, empleado o grupo antes de exportar.</small>
        </div>
    </div>
    <div class="card-body">
        <form method="get" action="<?= htmlspecialchars(site_url('admin/reportes/asistencia'), ENT_QUOTES, 'UTF-8') ?>" class="row align-items-end">
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">Mes</label>
                <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($selectedMonth, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <label class="form-label">Empleado</label>
                <select name="employee_id" class="form-control">
                    <option value="">Todos los empleados</option>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) $employee['id'] === $selectedEmployeeId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($employee['cedula'] . ' - ' . $employee['full_name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">Grupo</label>
                <select name="group_id" class="form-control">
                    <option value="">Todos los grupos</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= htmlspecialchars((string) $group['id'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) $group['id'] === $selectedGroupId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-6 mb-3">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="bi bi-funnel-fill mr-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
        <div>
            <h6 class="m-0 font-weight-bold text-primary">Detalle por empleado</h6>
            <small class="text-muted">Resumen de marcaciones durante <?= htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8') ?>.</small>
        </div>
        <div class="mt-3 mt-lg-0">
            <span class="badge badge-light border">Formato exportable: CSV, XLS, XLSX</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="attendanceReportTable" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>Cédula</th>
                        <th>Empleado</th>
                        <th>Grupo</th>
                        <th class="text-right">Entradas</th>
                        <th class="text-right">Salidas</th>
                        <th class="text-right">Tardanzas</th>
                        <th>Primera entrada</th>
                        <th>Última salida</th>
                        <th>Último registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($summaryRows)): ?>
                        <?php foreach ($summaryRows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($row['cedula'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="font-weight-bold text-gray-800"><?= htmlspecialchars((string) ($row['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars((string) ($row['email'] ?? 'Sin correo'), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td><?= htmlspecialchars((string) ($row['group_name'] ?? 'Sin grupo'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-right"><?= number_format((int) ($row['total_entries'] ?? 0)) ?></td>
                                <td class="text-right"><?= number_format((int) ($row['total_exits'] ?? 0)) ?></td>
                                <td class="text-right"><?= number_format((int) ($row['total_late_entries'] ?? 0)) ?></td>
                                <td><?= htmlspecialchars((string) ($row['first_entry_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['last_exit_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['last_mark_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">No hay datos para los filtros seleccionados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>