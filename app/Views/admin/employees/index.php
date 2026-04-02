<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <h2 class="h5 mb-0 font-weight-bold">Empleados</h2>
            <a href="#" class="btn btn-primary btn-sm mt-2 mt-md-0">Nuevo empleado</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Grupo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees ?? [] as $employee): ?>
                    <tr>
                        <td><?= htmlspecialchars($employee['cedula'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($employee['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($employee['group_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= ((int) $employee['active'] === 1) ? 'Activo' : 'Inactivo' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>