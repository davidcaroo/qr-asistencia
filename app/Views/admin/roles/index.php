<?php
$roles = $roles ?? [];
$permissionCounts = $permissionCounts ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h2 class="h5 mb-0 font-weight-bold">Roles y permisos</h2>
                <p class="text-muted mb-0 small">Define permisos y acceso para cada perfil administrativo.</p>
            </div>
            <div class="d-flex flex-wrap mt-2 mt-md-0">
                <a href="<?= htmlspecialchars(site_url('admin/roles/nuevo'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm btn-icon-label mb-2">
                    <i class="bi bi-shield-lock mr-2"></i>
                    <span>Nuevo rol</span>
                </a>
            </div>
        </div>
    </div>
    <div class="table-responsive px-3 pb-3">
        <table class="table table-hover mb-0" id="rolesTable">
            <thead class="thead-light">
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Descripcion</th>
                    <th>Permisos</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roles)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No hay roles registrados todavia.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td class="font-weight-bold text-gray-800"><?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><code><?= htmlspecialchars($role['slug'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= htmlspecialchars($role['description'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge badge-light border">
                                    <?= (int) ($permissionCounts[(int) $role['id']] ?? 0) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= ((int) $role['active'] === 1) ? 'success' : 'secondary' ?>">
                                    <?= ((int) $role['active'] === 1) ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="text-right table-actions pr-4 text-nowrap">
                                <div class="d-inline-flex align-items-center justify-content-end flex-wrap">
                                    <a href="<?= htmlspecialchars(site_url('admin/roles/' . (int) $role['id'] . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-primary btn-icon-only mb-1 mr-1" aria-label="Editar rol" title="Editar rol">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="post" action="<?= htmlspecialchars(site_url('admin/roles/' . (int) $role['id'] . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="d-inline mb-1" data-confirm-delete>
                                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-only" aria-label="Eliminar rol" title="Eliminar rol">
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
