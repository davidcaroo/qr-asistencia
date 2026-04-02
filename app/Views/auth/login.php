<?php
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Acceso administrativo</h1>
                                </div>

                                <form class="user" method="post" action="<?= htmlspecialchars(site_url('login'), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <div class="form-group">
                                        <input type="email" name="email" class="form-control form-control-user" placeholder="Correo electrónico" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control form-control-user" placeholder="Contraseña" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-user btn-block btn-icon-label">
                                        <i class="bi bi-box-arrow-in-right mr-2"></i>
                                        <span>Entrar</span>
                                    </button>
                                </form>

                                <hr>
                                <div class="text-center">
                                    <a class="small" href="<?= htmlspecialchars(site_url('marcar'), ENT_QUOTES, 'UTF-8') ?>">Ir al módulo de asistencia</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>