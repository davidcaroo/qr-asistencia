<?php
$groups = $groups ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h2 class="h5 mb-0 font-weight-bold">Grupos</h2>
                <p class="text-muted mb-0 small">Administra los grupos que luego se asignan a empleados y horarios.</p>
            </div>
            <div class="d-flex flex-wrap mt-2 mt-md-0">
                <a href="<?= htmlspecialchars(site_url('admin/grupos/nuevo'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm btn-icon-label mb-2">
                    <i class="bi bi-people-plus mr-2"></i>
                    <span>Nuevo grupo</span>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body border-bottom">
        <div class="row">
            <div class="col-lg-4 mb-3">
                <label class="small text-uppercase font-weight-bold text-gray-700">Total de grupos</label>
                <div class="h4 mb-0 font-weight-bold text-gray-900"><?= number_format((int) ($groupCount ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="table-responsive px-3 pb-3">
        <table class="table table-hover mb-0" id="groupsTable">
            <thead class="thead-light">
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($groups)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">No hay grupos registrados todavía.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($groups as $group): ?>
                        <tr>
                            <td class="font-weight-bold text-gray-800"><?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><code><?= htmlspecialchars($group['slug'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= htmlspecialchars($group['description'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge badge-<?= ((int) $group['active'] === 1) ? 'success' : 'secondary' ?>">
                                    <?= ((int) $group['active'] === 1) ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="text-right table-actions pr-4 text-nowrap">
                                <div class="d-inline-flex align-items-center justify-content-end flex-wrap">
                                    <a href="<?= htmlspecialchars(site_url('admin/grupos/' . (int) $group['id'] . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-primary btn-icon-only mb-1 mr-1" aria-label="Editar grupo" title="Editar grupo">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="post" action="<?= htmlspecialchars(site_url('admin/grupos/' . (int) $group['id'] . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="d-inline mb-1" data-confirm-delete>
                                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(
                                                                                        \App\Core\Csrf::token(),
                                                                                        ENT_QUOTES,
                                                                                        'UTF-8'
                                                                                    ) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-only" aria-label="Eliminar grupo" title="Eliminar grupo">
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