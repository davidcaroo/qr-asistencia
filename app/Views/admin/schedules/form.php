<?php

$schedule = $schedule ?? null;
$errors = $errors ?? [];
$isEdit = !empty($schedule['id']);
$action = $formAction ?? site_url('admin/horarios');
$title = $formTitle ?? ($isEdit ? 'Editar horario' : 'Nuevo horario');
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h2 class="h5 mb-0 font-weight-bold"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-muted mb-0 small">Define la plantilla de horario que luego se asigna a un grupo.</p>
        </div>
        <a href="<?= htmlspecialchars(site_url('admin/horarios'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0 btn-icon-label">
            <i class="bi bi-arrow-left mr-2"></i>
            <span>Volver</span>
        </a>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger border-0 shadow-sm">
                <ul class="mb-0 pl-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="row">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="col-lg-6 form-group">
                <label for="name">Nombre</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars((string) ($schedule['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="col-lg-3 form-group">
                <label for="start_time">Inicio</label>
                <input type="time" class="form-control" id="start_time" name="start_time" value="<?= htmlspecialchars((string) ($schedule['start_time'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="col-lg-3 form-group">
                <label for="end_time">Fin</label>
                <input type="time" class="form-control" id="end_time" name="end_time" value="<?= htmlspecialchars((string) ($schedule['end_time'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="col-lg-3 form-group">
                <label for="tolerance_before_minutes">Tolerancia antes</label>
                <input type="number" min="0" class="form-control" id="tolerance_before_minutes" name="tolerance_before_minutes" value="<?= (int) ($schedule['tolerance_before_minutes'] ?? 0) ?>">
            </div>

            <div class="col-lg-3 form-group">
                <label for="tolerance_after_minutes">Tolerancia después</label>
                <input type="number" min="0" class="form-control" id="tolerance_after_minutes" name="tolerance_after_minutes" value="<?= (int) ($schedule['tolerance_after_minutes'] ?? 0) ?>">
            </div>

            <div class="col-lg-3 form-group">
                <label for="active">Estado</label>
                <select class="form-control" id="active" name="active">
                    <option value="1" <?= (int) ($schedule['active'] ?? 1) === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (int) ($schedule['active'] ?? 1) === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="col-lg-3 d-flex align-items-end form-group">
                <button type="submit" class="btn btn-primary btn-block btn-icon-label">
                    <i class="bi bi-check2-circle mr-2"></i>
                    <span><?= $isEdit ? 'Guardar cambios' : 'Crear horario' ?></span>
                </button>
            </div>
        </form>
    </div>
</div>