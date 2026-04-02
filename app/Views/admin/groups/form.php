<?php
$group = $group ?? null;
$errors = $errors ?? [];
$isEdit = !empty($group['id']);
$action = $formAction ?? site_url('admin/grupos');
$title = $formTitle ?? ($isEdit ? 'Editar grupo' : 'Nuevo grupo');
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h2 class="h5 mb-0 font-weight-bold"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-muted mb-0 small">El slug se usa como identificador interno y puede generarse automáticamente.</p>
        </div>
        <a href="<?= htmlspecialchars(site_url('admin/grupos'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0 btn-icon-label">
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
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars((string) ($group['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="col-lg-6 form-group">
                <label for="slug">Slug</label>
                <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars((string) ($group['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Se genera automáticamente si lo dejas vacío">
            </div>

            <div class="col-12 form-group">
                <label for="description">Descripción</label>
                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Opcional"><?= htmlspecialchars((string) ($group['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="col-lg-3 form-group">
                <label for="active">Estado</label>
                <select class="form-control" id="active" name="active">
                    <option value="1" <?= (int) ($group['active'] ?? 1) === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (int) ($group['active'] ?? 1) === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="col-12 d-flex flex-wrap justify-content-end mt-2">
                <a href="<?= htmlspecialchars(site_url('admin/grupos'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light mr-2 mb-2 btn-icon-label">
                    <i class="bi bi-x-circle mr-2"></i>
                    <span>Cancelar</span>
                </a>
                <button type="submit" class="btn btn-primary mb-2 btn-icon-label">
                    <i class="bi bi-check2-circle mr-2"></i>
                    <span><?= $isEdit ? 'Guardar cambios' : 'Crear grupo' ?></span>
                </button>
            </div>
        </form>
    </div>
</div>