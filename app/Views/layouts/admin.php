<?php

$adminUser = \App\Core\Auth::admin();
$flashMessages = [];
if ($success = flash('success')) {
    $flashMessages[] = ['type' => 'success', 'title' => 'Correcto', 'message' => (string) $success];
}
if ($error = flash('error')) {
    $flashMessages[] = ['type' => 'error', 'title' => 'Atención', 'message' => (string) $error];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?= htmlspecialchars($pageTitle ?? 'Panel', ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars(config('app', 'name'), ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="icon" type="image/png" href="<?= htmlspecialchars(asset_url('img/favicon-qr-asistencia.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars(asset_url('img/favicon-qr-asistencia.png'), ENT_QUOTES, 'UTF-8') ?>">

    <link href="<?= htmlspecialchars(asset_url('vendor/fontawesome-free/css/all.min.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(asset_url('vendor/datatables/dataTables.bootstrap4.min.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars(asset_url('css/sb-admin-2.min.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <link href="<?= htmlspecialchars(asset_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= htmlspecialchars(site_url('admin'), ENT_QUOTES, 'UTF-8') ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div class="sidebar-brand-text mx-3">QR Asistencia</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item active">
                <a class="nav-link" href="<?= htmlspecialchars(site_url('admin'), ENT_QUOTES, 'UTF-8') ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Gestión</div>
            <?php if (\App\Core\Auth::can('groups.manage')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(site_url('admin/grupos'), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-fw fa-layer-group"></i>
                        <span>Grupos</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('employees.view')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(site_url('admin/empleados'), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Empleados</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('schedules.manage')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(site_url('admin/horarios'), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-fw fa-clock"></i>
                        <span>Horarios</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('qr.view')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(site_url('admin/qr'), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-fw fa-qrcode"></i>
                        <span>QR global</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('reports.view')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(site_url('admin/reportes/asistencia'), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-fw fa-chart-line"></i>
                        <span>Reportes</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('users.manage')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(site_url('admin/usuarios'), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-fw fa-user-shield"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('roles.manage')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(site_url('admin/roles'), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-fw fa-shield-alt"></i>
                        <span>Roles</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('audit.view')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(site_url('admin/auditoria'), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-fw fa-shield-alt"></i>
                        <span>Auditoría</span>
                    </a>
                </li>
            <?php endif; ?>
            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <div class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" value="<?= htmlspecialchars($pageTitle ?? 'Panel', ENT_QUOTES, 'UTF-8') ?>" readonly>
                        </div>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <span class="badge badge-danger badge-counter"><?= !empty($flashMessages) ? count($flashMessages) : 0 ?></span>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">Alertas</h6>
                                <?php if (!empty($flashMessages)): ?>
                                    <?php foreach ($flashMessages as $flashMessage): ?>
                                        <a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="icon-circle <?= $flashMessage['type'] === 'success' ? 'bg-success' : 'bg-danger' ?>">
                                                    <i class="fas fa-<?= $flashMessage['type'] === 'success' ? 'check' : 'exclamation-triangle' ?> text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="small text-gray-500"><?= htmlspecialchars($flashMessage['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                                <span class="font-weight-bold"><?= htmlspecialchars($flashMessage['message'], ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="dropdown-item text-gray-500">Sin novedades</div>
                                <?php endif; ?>
                            </div>
                        </li>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($adminUser['name'] ?? 'Invitado', ENT_QUOTES, 'UTF-8') ?></span>
                                <img class="img-profile rounded-circle" src="<?= htmlspecialchars(asset_url('img/undraw_profile.svg'), ENT_QUOTES, 'UTF-8') ?>" alt="Perfil">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <form method="post" action="<?= htmlspecialchars(site_url('logout'), ENT_QUOTES, 'UTF-8') ?>" class="m-0" data-confirm-logout>
                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Salir
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>

                <main class="container-fluid">
                    <?= $content ?>
                </main>
            </div>
        </div>
    </div>

    <script src="<?= htmlspecialchars(asset_url('vendor/jquery/jquery.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('vendor/bootstrap/js/bootstrap.bundle.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('vendor/jquery-easing/jquery.easing.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('vendor/datatables/jquery.dataTables.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('vendor/datatables/dataTables.bootstrap4.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.APP_FLASH = <?= json_encode($flashMessages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="<?= htmlspecialchars(asset_url('js/sb-admin-2.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('assets/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>

</html>