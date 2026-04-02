<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <h2 class="h5 mb-0 font-weight-bold">Horarios</h2>
            <a href="#" class="btn btn-primary btn-sm mt-2 mt-md-0">Nuevo horario</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nombre</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Tolerancia</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules ?? [] as $schedule): ?>
                    <tr>
                        <td><?= htmlspecialchars($schedule['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($schedule['start_time'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($schedule['end_time'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $schedule['tolerance_before_minutes'] ?> / <?= (int) $schedule['tolerance_after_minutes'] ?> min</td>
                        <td><?= ((int) $schedule['active'] === 1) ? 'Activo' : 'Inactivo' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>