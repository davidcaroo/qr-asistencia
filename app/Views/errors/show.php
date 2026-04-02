<?php

$statusCode = (int) ($statusCode ?? 500);
$title = (string) ($title ?? 'Error');
$message = (string) ($message ?? 'Se produjo un error inesperado.');
$details = isset($details) && $details !== '' ? (string) $details : null;
$actionLabel = (string) ($actionLabel ?? 'Ir al inicio');
$actionUrl = (string) ($actionUrl ?? site_url('marcar'));
$helpText = (string) ($helpText ?? 'Intenta nuevamente o vuelve a la pantalla principal.');

$copyByCode = [
    400 => ['chip' => 'Solicitud inválida', 'icon' => 'fa-exclamation-triangle', 'tone' => 'warning'],
    401 => ['chip' => 'Sin autenticación', 'icon' => 'fa-user-lock', 'tone' => 'warning'],
    403 => ['chip' => 'Acceso restringido', 'icon' => 'fa-ban', 'tone' => 'danger'],
    404 => ['chip' => 'Ruta no encontrada', 'icon' => 'fa-map-signs', 'tone' => 'danger'],
    405 => ['chip' => 'Método no permitido', 'icon' => 'fa-ban', 'tone' => 'warning'],
    408 => ['chip' => 'Tiempo agotado', 'icon' => 'fa-hourglass-end', 'tone' => 'warning'],
    429 => ['chip' => 'Demasiadas solicitudes', 'icon' => 'fa-stopwatch', 'tone' => 'warning'],
    500 => ['chip' => 'Error del servidor', 'icon' => 'fa-server', 'tone' => 'danger'],
    502 => ['chip' => 'Puerta de enlace', 'icon' => 'fa-network-wired', 'tone' => 'danger'],
    503 => ['chip' => 'Servicio temporalmente fuera', 'icon' => 'fa-tools', 'tone' => 'warning'],
    504 => ['chip' => 'Tiempo de respuesta agotado', 'icon' => 'fa-clock', 'tone' => 'warning'],
];

$decor = $copyByCode[$statusCode] ?? ['chip' => 'Error', 'icon' => 'fa-exclamation-circle', 'tone' => 'warning'];
$toneClass = $decor['tone'] === 'danger' ? 'bg-danger' : 'bg-warning';
?>

<div class="container py-5">
    <div class="row justify-content-center align-items-center min-vh-100 py-4">
        <div class="col-xl-10 col-lg-11">
            <div class="card border-0 shadow-lg overflow-hidden">
                <div class="row no-gutters">
                    <div class="col-lg-5 <?= $toneClass ?> text-white p-5 d-flex align-items-center">
                        <div>
                            <div class="badge badge-light text-uppercase text-primary badge-pill px-3 py-2 mb-4">HTTP <?= htmlspecialchars((string) $statusCode, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="display-3 font-weight-bold mb-3"><?= htmlspecialchars((string) $statusCode, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas <?= htmlspecialchars($decor['icon'], ENT_QUOTES, 'UTF-8') ?> fa-2x mr-3"></i>
                                <h1 class="h3 mb-0 font-weight-bold"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
                            </div>
                            <p class="mb-0 text-white-75"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>

                    <div class="col-lg-7 bg-white">
                        <div class="p-4 p-md-5">
                            <span class="badge badge-pill badge-primary px-3 py-2 mb-3"><?= htmlspecialchars($decor['chip'], ENT_QUOTES, 'UTF-8') ?></span>
                            <h2 class="h4 font-weight-bold text-gray-900 mb-3"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="text-gray-700 mb-4"><?= htmlspecialchars($helpText, ENT_QUOTES, 'UTF-8') ?></p>

                            <?php if ($details !== null): ?>
                                <div class="alert alert-light border-left-primary shadow-sm mb-4">
                                    <div class="small text-uppercase font-weight-bold text-primary mb-2">Detalle técnico</div>
                                    <div class="text-gray-800 small" style="white-space: pre-wrap;"><?= htmlspecialchars($details, ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card border-left-primary shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Ruta sugerida</div>
                                            <div class="h6 mb-0 text-gray-900"><?= htmlspecialchars(parse_url($actionUrl, PHP_URL_PATH) ?: '/', ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card border-left-info shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Qué puedes hacer</div>
                                            <div class="h6 mb-0 text-gray-900">Volver a intentar o regresar al inicio</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap mt-2">
                                <a href="<?= htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-icon-split mr-2 mb-2">
                                    <span class="icon text-white-50"><i class="fas fa-home"></i></span>
                                    <span class="text"><?= htmlspecialchars($actionLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                </a>
                                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-icon-split mb-2">
                                    <span class="icon text-gray-600"><i class="fas fa-arrow-left"></i></span>
                                    <span class="text">Volver atrás</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>