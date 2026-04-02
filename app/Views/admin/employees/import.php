<?php
?>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap">
                <div>
                    <h2 class="h5 mb-0 font-weight-bold">Importar empleados</h2>
                    <p class="text-muted mb-0 small">Carga masiva desde CSV, XLS o XLSX.</p>
                </div>
                <a href="<?= htmlspecialchars(site_url('admin/empleados'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0 btn-icon-label">
                    <i class="bi bi-arrow-left mr-2"></i>
                    <span>Volver</span>
                </a>
            </div>
            <div class="card-body">
                <form method="post" action="<?= htmlspecialchars(site_url('admin/empleados/importar'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-group">
                        <label for="file">Archivo</label>
                        <input type="file" class="form-control-file" id="file" name="file" accept=".csv,.xls,.xlsx" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-icon-label">
                        <i class="bi bi-file-earmark-arrow-up mr-2"></i>
                        <span>Importar ahora</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h3 class="h6 font-weight-bold text-uppercase text-gray-700 mb-3">Columnas soportadas</h3>
                <ul class="small mb-0 pl-3 text-gray-700">
                    <li><strong>cedula</strong> o <strong>cédula</strong></li>
                    <li><strong>full_name</strong>, <strong>nombre</strong> o <strong>name</strong></li>
                    <li><strong>email</strong> o <strong>correo</strong></li>
                    <li><strong>group_id</strong>, <strong>group</strong> o <strong>grupo</strong></li>
                    <li><strong>active</strong>, <strong>estado</strong> o <strong>activo</strong></li>
                </ul>
                <hr>
                <p class="small text-muted mb-0">Si el archivo trae una cédula existente, el empleado se actualiza. Si no existe, se crea. Los grupos pueden venir por ID, nombre o slug.</p>
            </div>
        </div>
    </div>
</div>