<?php

$employee = $employee ?? null;
$errors = $errors ?? [];
$groups = $groups ?? [];
$isEdit = !empty($employee['id']);
$action = $formAction ?? site_url('admin/empleados');
$title = $formTitle ?? ($isEdit ? 'Editar empleado' : 'Nuevo empleado');
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h2 class="h5 mb-0 font-weight-bold"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-muted mb-0 small">La marcación usa cédula como identificador principal.</p>
        </div>
        <a href="<?= htmlspecialchars(site_url('admin/empleados'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0">Volver</a>
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

            <div class="col-lg-4 form-group">
                <label for="cedula">Cédula</label>
                <input type="text" class="form-control" id="cedula" name="cedula" value="<?= htmlspecialchars((string) ($employee['cedula'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="col-lg-5 form-group">
                <label for="full_name">Nombre completo</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars((string) ($employee['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="col-lg-3 form-group">
                <label for="group_id">Grupo</label>
                <select class="form-control" id="group_id" name="group_id">
                    <option value="">Sin grupo</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= (int) $group['id'] ?>" <?= ((int) ($employee['group_id'] ?? 0) === (int) $group['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-6 form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars((string) ($employee['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="col-lg-3 form-group">
                <label for="active">Estado</label>
                <select class="form-control" id="active" name="active">
                    <option value="1" <?= (int) ($employee['active'] ?? 1) === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (int) ($employee['active'] ?? 1) === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="col-12 d-flex flex-wrap justify-content-end mt-2">
                <a href="<?= htmlspecialchars(site_url('admin/empleados'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light mr-2 mb-2">Cancelar</a>
                <button type="submit" class="btn btn-primary mb-2"><?= $isEdit ? 'Guardar cambios' : 'Crear empleado' ?></button>
            </div>
        </form>
    </div>
</div>