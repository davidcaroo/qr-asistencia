<div class="row">
    <div class="col-12 col-lg-4 mb-4">
        <div class="card shadow-sm border-0 metric-card metric-blue h-100">
            <div class="card-body">
                <div class="text-uppercase small text-white-50">Empleados</div>
                <div class="display-4 font-weight-bold text-white"><?= (int) ($totalEmployees ?? 0) ?></div>
                <div class="text-white-50">Registrados en el sistema</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4 mb-4">
        <div class="card shadow-sm border-0 metric-card metric-green h-100">
            <div class="card-body">
                <div class="text-uppercase small text-white-50">Marcaciones de hoy</div>
                <div class="display-4 font-weight-bold text-white"><?= (int) ($todayAttendances ?? 0) ?></div>
                <div class="text-white-50">Entradas y salidas registradas</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4 mb-4">
        <div class="card shadow-sm border-0 metric-card metric-gold h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <div class="text-uppercase small text-white-50">QR global</div>
                    <div class="h4 text-white mb-1">Rotativo cada 30s</div>
                    <div class="text-white-50">Abre el visor en tiempo real.</div>
                </div>
                <a href="<?= htmlspecialchars(site_url('admin/qr'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light btn-sm align-self-start mt-3">Ver QR</a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-2">
    <div class="card-body">
        <h2 class="h5 font-weight-bold mb-2">Flujo base</h2>
        <p class="text-muted mb-0">El sistema identifica al empleado por cédula, alterna entrada/salida automáticamente y aplica la ventana del horario asignado al grupo.</p>
    </div>
</div>