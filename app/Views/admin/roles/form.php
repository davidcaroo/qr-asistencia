<?php
$role = $role ?? null;
$errors = $errors ?? [];
$permissions = $permissions ?? [];
$selectedPermissions = $selectedPermissions ?? [];
$isEdit = !empty($role['id']);
$action = $formAction ?? site_url('admin/roles');
$title = $formTitle ?? ($isEdit ? 'Editar rol' : 'Nuevo rol');
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h2 class="h5 mb-0 font-weight-bold"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-muted mb-0 small">Define el acceso y permisos disponibles para el rol.</p>
        </div>
        <a href="<?= htmlspecialchars(site_url('admin/roles'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0 btn-icon-label">
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
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars((string) ($role['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="col-lg-6 form-group">
                <label for="slug">Slug</label>
                <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars((string) ($role['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Se genera automaticamente si lo dejas vacio">
            </div>

            <div class="col-12 form-group">
                <label for="description">Descripcion</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Opcional"><?= htmlspecialchars((string) ($role['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="col-lg-3 form-group">
                <label for="active">Estado</label>
                <select class="form-control" id="active" name="active">
                    <option value="1" <?= (int) ($role['active'] ?? 1) === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (int) ($role['active'] ?? 1) === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="col-12 mt-3">
                <h3 class="h6 font-weight-bold">Permisos</h3>
                <p class="text-muted small">Selecciona los permisos que estaran disponibles para este rol.</p>
                <div class="row">
                    <?php if (empty($permissions)): ?>
                        <div class="col-12 text-muted">No hay permisos configurados.</div>
                    <?php else: ?>
                        <?php foreach ($permissions as $permission): ?>
                            <?php $permissionId = (int) ($permission['id'] ?? 0); ?>
                            <div class="col-md-6 col-lg-4 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="permission<?= $permissionId ?>"
                                        name="permission_ids[]"
                                        value="<?= $permissionId ?>"
                                        <?= in_array($permissionId, $selectedPermissions, true) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="permission<?= $permissionId ?>">
                                        <span class="font-weight-bold"><?= htmlspecialchars((string) ($permission['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="d-block text-muted small"><?= htmlspecialchars((string) ($permission['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 d-flex flex-wrap justify-content-end mt-4">
                <a href="<?= htmlspecialchars(site_url('admin/roles'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light mr-2 mb-2 btn-icon-label">
                    <i class="bi bi-x-circle mr-2"></i>
                    <span>Cancelar</span>
                </a>
                <button type="submit" class="btn btn-primary mb-2 btn-icon-label">
                    <i class="bi bi-check2-circle mr-2"></i>
                    <span><?= $isEdit ? 'Guardar cambios' : 'Crear rol' ?></span>
                </button>
            </div>
        </form>
    </div>
</div>
