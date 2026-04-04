<?php
$users = $users ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h2 class="h5 mb-0 font-weight-bold">Usuarios administradores</h2>
                <p class="text-muted mb-0 small">Gestiona accesos internos y asigna roles.</p>
            </div>
            <div class="d-flex flex-wrap mt-2 mt-md-0">
                <a href="<?= htmlspecialchars(site_url('admin/usuarios/nuevo'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm btn-icon-label mb-2">
                    <i class="bi bi-person-plus mr-2"></i>
                    <span>Nuevo usuario</span>
                </a>
            </div>
        </div>
    </div>
    <div class="table-responsive px-3 pb-3">
        <table class="table table-hover mb-0" id="adminUsersTable">
            <thead class="thead-light">
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Ultimo acceso</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No hay usuarios registrados todavia.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="font-weight-bold text-gray-800"><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge badge-light border">
                                    <?= htmlspecialchars($user['role_name'] ?? ($user['role_slug'] ?? 'Sin rol'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= ((int) $user['active'] === 1) ? 'success' : 'secondary' ?>">
                                    <?= ((int) $user['active'] === 1) ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars((string) ($user['last_login_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-right table-actions pr-4 text-nowrap">
                                <div class="d-inline-flex align-items-center justify-content-end flex-wrap">
                                    <a href="<?= htmlspecialchars(site_url('admin/usuarios/' . (int) $user['id'] . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-primary btn-icon-only mb-1 mr-1" aria-label="Editar usuario" title="Editar usuario">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="post" action="<?= htmlspecialchars(site_url('admin/usuarios/' . (int) $user['id'] . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="d-inline mb-1" data-confirm-delete>
                                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-only" aria-label="Eliminar usuario" title="Eliminar usuario">
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
