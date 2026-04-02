<?php
?>
<div class="attendance-page-shell">
    <div class="container py-4 py-lg-5">
        <div class="attendance-compact card border-0 shadow-lg overflow-hidden mx-auto">
            <div class="card-body p-0">
                <div class="row no-gutters">
                    <div class="col-lg-5 attendance-qr-pane d-flex align-items-center justify-content-center">
                        <div class="attendance-qr-pane__inner text-center">
                            <div class="badge badge-light text-primary badge-pill px-3 py-2 mb-3">QR global en vivo</div>
                            <h1 class="h3 font-weight-bold text-white mb-2">Escanea para marcar</h1>
                            <p class="attendance-qr-text mb-4">El código cambia automáticamente. Si ya tienes la página abierta, también puedes completar la cédula aquí.</p>

                            <div
                                class="qr-frame attendance-qr-frame mx-auto mb-3"
                                id="attendanceQrFrame"
                                data-current-url="<?= htmlspecialchars((string) ($currentQrUrl ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                data-current-expires-at="<?= htmlspecialchars((string) ($currentQrExpiresAt ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <div id="attendanceQrCanvas"></div>
                            </div>

                            <div class="attendance-qr-meta d-flex justify-content-between align-items-center flex-wrap">
                                <span class="small text-white-75 mb-2 mb-md-0">Expira en <strong id="attendanceQrCountdown">--</strong></span>
                                <span class="small text-white-75">Actualización automática</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7 bg-white">
                        <div class="p-4 p-md-5">
                            <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap">
                                <div class="mb-3 mb-md-0">
                                    <span class="badge badge-pill badge-primary px-3 py-2 attendance-badge">Asistencia por QR</span>
                                    <h2 class="attendance-heading mt-3 mb-2">Ingresa tu cédula</h2>
                                    <p class="attendance-subtitle mb-0">Usa el número con el que estás registrado. No necesitas contraseña para marcar.</p>
                                </div>
                                <a class="btn btn-link text-muted p-0 attendance-admin-link btn-icon-label" href="<?= htmlspecialchars(site_url('login'), ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="bi bi-shield-lock mr-2"></i>
                                    <span>Panel admin</span>
                                </a>
                            </div>

                            <?php if ($success = flash('success')): ?>
                                <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
                                    <strong>Listo.</strong> <?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($error = flash('error')): ?>
                                <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
                                    <strong>Revisa tu registro.</strong> <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php endif; ?>

                            <?php if (empty($currentQrToken)): ?>
                                <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
                                    No hay un QR válido para mostrar ahora mismo.
                                </div>
                            <?php endif; ?>

                            <form method="post" action="<?= htmlspecialchars(site_url('marcar'), ENT_QUOTES, 'UTF-8') ?>" class="attendance-form">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="token" value="<?= htmlspecialchars($currentQrToken ?? $incomingToken, ENT_QUOTES, 'UTF-8') ?>">

                                <div class="form-group mb-4">
                                    <label for="cedula" class="small text-uppercase font-weight-bold text-gray-700 mb-2">Cédula</label>
                                    <div class="input-group input-group-lg attendance-input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-id-card text-primary"></i></span>
                                        </div>
                                        <input type="text" name="cedula" id="cedula" class="form-control border-left-0" inputmode="numeric" autocomplete="off" placeholder="Ej. 12345678" required>
                                    </div>
                                    <small class="form-text text-muted mt-2">Escribe tu número de cédula sin puntos ni espacios.</small>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg btn-block btn-icon-split attendance-submit shadow-sm btn-icon-label">
                                    <span class="icon text-white-50"><i class="bi bi-check2-circle"></i></span>
                                    <span class="text">Registrar asistencia</span>
                                </button>
                            </form>

                            <div class="attendance-note mt-4 pt-4 border-top">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="small text-muted mb-2 mb-md-0">Si ya registraste tu entrada, el siguiente movimiento será salida.</div>
                                    <a class="btn btn-outline-primary btn-sm btn-icon-label" href="<?= htmlspecialchars(site_url('login'), ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-box-arrow-in-right mr-2"></i>
                                        <span>Panel administrativo</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="<?= htmlspecialchars(asset_url('assets/js/qr-live.js'), ENT_QUOTES, 'UTF-8') ?>"></script>