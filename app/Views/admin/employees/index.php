<?php
$groups = $groups ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h2 class="h5 mb-0 font-weight-bold">Empleados</h2>
                <p class="text-muted mb-0 small">Gestiona registros individuales o importa un archivo masivo.</p>
            </div>
            <div class="d-flex flex-wrap mt-2 mt-md-0">
                <a href="<?= htmlspecialchars(site_url('admin/empleados/importar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-primary btn-sm mr-2 mb-2">Importar</a>
                <a href="<?= htmlspecialchars(site_url('admin/empleados/nuevo'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm mb-2">Nuevo empleado</a>
            </div>
        </div>
    </div>
    <div class="card-body border-bottom">
        <div class="row">
            <div class="col-lg-4 mb-3">
                <label class="small text-uppercase font-weight-bold text-gray-700">Buscar</label>
                <input type="search" id="employeeSearch" class="form-control" placeholder="Cédula, nombre, correo...">
            </div>
            <div class="col-lg-4 mb-3">
                <label class="small text-uppercase font-weight-bold text-gray-700">Grupo</label>
                <select id="employeeGroupFilter" class="form-control">
                    <option value="">Todos los grupos</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-4 mb-3">
                <label class="small text-uppercase font-weight-bold text-gray-700">Estado</label>
                <select id="employeeStatusFilter" class="form-control">
                    <option value="">Todos</option>
                    <option value="Activo">Activos</option>
                    <option value="Inactivo">Inactivos</option>
                </select>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="employeesTable">
            <thead class="thead-light">
                <tr>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Grupo</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees ?? [])): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No hay empleados registrados todavía.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employees as $employee): ?>
                        <tr data-group="<?= htmlspecialchars($employee['group_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-status="<?= ((int) $employee['active'] === 1) ? 'Activo' : 'Inactivo' ?>">
                            <td><?= htmlspecialchars($employee['cedula'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($employee['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($employee['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($employee['group_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge badge-<?= ((int) $employee['active'] === 1) ? 'success' : 'secondary' ?>">
                                    <?= ((int) $employee['active'] === 1) ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="<?= htmlspecialchars(site_url('admin/empleados/' . (int) $employee['id'] . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-primary mb-1">Editar</a>
                                <form method="post" action="<?= htmlspecialchars(site_url('admin/empleados/' . (int) $employee['id'] . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="d-inline" data-confirm-delete>
                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger mb-1">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    window.EMPLOYEE_TABLE_READY = true;
</script>