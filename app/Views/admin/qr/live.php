<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0" id="qrLiveRoot" data-endpoint="<?= htmlspecialchars(site_url('api/qr/current'), ENT_QUOTES, 'UTF-8') ?>" data-refresh-seconds="<?= (int) config('app', 'qr_window_seconds', 30) ?>">
            <div class="card-body p-4 p-md-5 text-center">
                <div class="badge badge-info text-uppercase mb-3">QR global rotativo</div>
                <h2 class="h3 font-weight-bold mb-2">Pantalla de marcado</h2>
                <p class="text-muted mb-4">Este código cambia automáticamente. Cada token expira en la ventana configurada.</p>

                <div class="qr-frame mx-auto mb-3">
                    <div id="qrCanvas" class="d-inline-block"></div>
                </div>

                <div class="small text-muted mb-2">Expira en <span id="qrCountdown">--</span></div>
                <div class="font-weight-bold text-break" id="qrUrl">Cargando...</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="<?= htmlspecialchars(asset_url('assets/js/qr-live.js'), ENT_QUOTES, 'UTF-8') ?>"></script>