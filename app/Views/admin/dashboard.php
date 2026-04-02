<div class="row">
    <div class="col-12 col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-stat border-left-primary shadow-sm h-100">
            <div class="card-body">
                <div class="dashboard-stat__header">
                    <div>
                        <div class="text-uppercase dashboard-stat__label text-primary">Empleados</div>
                        <div class="h2 mb-0 font-weight-bold text-gray-800"><?= (int) ($totalEmployees ?? 0) ?></div>
                        <div class="text-muted small mt-2">Registrados en el sistema</div>
                    </div>
                    <div class="dashboard-stat__icon text-primary bg-primary-soft">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-stat border-left-success shadow-sm h-100">
            <div class="card-body">
                <div class="dashboard-stat__header">
                    <div>
                        <div class="text-uppercase dashboard-stat__label text-success">Entradas</div>
                        <div class="h2 mb-0 font-weight-bold text-gray-800"><?= (int) ($todayEntries ?? 0) ?></div>
                        <div class="text-muted small mt-2">Marcadas hoy al ingresar</div>
                    </div>
                    <div class="dashboard-stat__icon text-success bg-success-soft">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-stat border-left-info shadow-sm h-100">
            <div class="card-body">
                <div class="dashboard-stat__header">
                    <div>
                        <div class="text-uppercase dashboard-stat__label text-info">Salidas</div>
                        <div class="h2 mb-0 font-weight-bold text-gray-800"><?= (int) ($todayExits ?? 0) ?></div>
                        <div class="text-muted small mt-2">Marcadas al terminar la jornada</div>
                    </div>
                    <div class="dashboard-stat__icon text-info bg-info-soft">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-stat border-left-danger shadow-sm h-100">
            <div class="card-body">
                <div class="dashboard-stat__header">
                    <div>
                        <div class="text-uppercase dashboard-stat__label text-danger">Tardanzas</div>
                        <div class="h2 mb-0 font-weight-bold text-gray-800"><?= (int) ($todayLateEntries ?? 0) ?></div>
                        <div class="text-muted small mt-2">Entradas después de la tolerancia</div>
                    </div>
                    <div class="dashboard-stat__icon text-danger bg-danger-soft">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card dashboard-qr-card border-left-warning shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <div class="dashboard-qr-card__header mb-4">
                    <div>
                        <div class="text-uppercase dashboard-stat__label text-warning">QR global</div>
                        <div class="h4 mb-1 font-weight-bold text-gray-800">Rotativo cada 30s</div>
                        <div class="text-muted small">Abre el visor en tiempo real.</div>
                    </div>
                    <div class="dashboard-stat__icon text-warning bg-warning-soft">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                </div>
                <a href="<?= htmlspecialchars(site_url('admin/qr'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm align-self-start btn-icon-label">
                    <i class="bi bi-qr-code-scan mr-2"></i>
                    <span>Ver QR</span>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0 h-100 dashboard-flow-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="dashboard-flow-card__icon mr-3"><i class="bi bi-diagram-3"></i></div>
                    <div>
                        <h2 class="h5 font-weight-bold mb-1">Flujo base</h2>
                        <p class="text-muted mb-0 small">Reglas de marcación activas</p>
                    </div>
                </div>
                <div class="dashboard-flow-card__steps">
                    <div class="dashboard-flow-card__step">
                        <span class="dashboard-flow-card__step-icon"><i class="bi bi-person-vcard"></i></span>
                        <div>
                            <strong>Identificación</strong>
                            <span>El sistema busca al empleado por cédula.</span>
                        </div>
                    </div>
                    <div class="dashboard-flow-card__step">
                        <span class="dashboard-flow-card__step-icon"><i class="bi bi-arrow-left-right"></i></span>
                        <div>
                            <strong>Entrada y salida</strong>
                            <span>Alterna automáticamente según la última marcación.</span>
                        </div>
                    </div>
                    <div class="dashboard-flow-card__step">
                        <span class="dashboard-flow-card__step-icon"><i class="bi bi-clock-history"></i></span>
                        <div>
                            <strong>Tolerancia</strong>
                            <span>La entrada tardía se marca cuando supera la ventana permitida.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>