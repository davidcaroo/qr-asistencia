<?php
$user = $user ?? null;
$errors = $errors ?? [];
$roles = $roles ?? [];
$isEdit = !empty($user['id']);
$action = $formAction ?? site_url('admin/usuarios');
$title = $formTitle ?? ($isEdit ? 'Editar usuario' : 'Nuevo usuario');
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h2 class="h5 mb-0 font-weight-bold"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-muted mb-0 small">Configura acceso, rol y credenciales del usuario administrativo.</p>
        </div>
        <a href="<?= htmlspecialchars(site_url('admin/usuarios'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0 btn-icon-label">
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
                <label for="name">Nombre completo</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars((string) ($user['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="col-lg-6 form-group">
                <label for="email">Correo electronico</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="col-lg-4 form-group">
                <label for="role_id">Rol</label>
                <select class="form-control" id="role_id" name="role_id" required>
                    <option value="">Selecciona...</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int) $role['id'] ?>" <?= ((int) ($user['role_id'] ?? 0) === (int) $role['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-3 form-group">
                <label for="active">Estado</label>
                <select class="form-control" id="active" name="active">
                    <option value="1" <?= (int) ($user['active'] ?? 1) === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (int) ($user['active'] ?? 1) === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="col-12">
                <div class="border rounded-lg p-3 bg-light mb-3">
                    <div class="font-weight-bold mb-2">Credenciales</div>
                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label for="password">Contrasena</label>
                            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label for="password_confirm">Confirmar contrasena</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" autocomplete="new-password">
                        </div>
                        <div class="col-lg-4 d-flex align-items-center">
                            <span class="text-muted small"><?= $isEdit ? 'Deja en blanco si no deseas cambiarla.' : 'Minimo 8 caracteres.' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex flex-wrap justify-content-end mt-2">
                <a href="<?= htmlspecialchars(site_url('admin/usuarios'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light mr-2 mb-2 btn-icon-label">
                    <i class="bi bi-x-circle mr-2"></i>
                    <span>Cancelar</span>
                </a>
                <button type="submit" class="btn btn-primary mb-2 btn-icon-label">
                    <i class="bi bi-check2-circle mr-2"></i>
                    <span><?= $isEdit ? 'Guardar cambios' : 'Crear usuario' ?></span>
                </button>
            </div>
        </form>
    </div>
</div>
