<?php
?>
<div class="auth-shell">
    <div class="auth-background"></div>

    <div class="container auth-login-container py-4 py-lg-5">
        <div class="row justify-content-center align-items-center auth-login-row">
            <div class="col-xl-11 col-lg-12">
                <div class="card auth-card border-0 shadow-lg overflow-hidden">
                    <div class="row no-gutters">
                        <div class="col-lg-5 d-none d-lg-flex auth-hero">
                            <div class="auth-hero__inner d-flex flex-column justify-content-between">
                                <div>
                                    <div class="auth-brand-banner mb-4">
                                        <img src="<?= htmlspecialchars(asset_url('img/logo-qr-asistencia.png'), ENT_QUOTES, 'UTF-8') ?>" alt="QR Asistencia" class="auth-brand-banner__img">
                                    </div>

                                    <div class="brand-lockup mb-3">
                                        <div class="brand-lockup__mark brand-lockup__mark--auth">
                                            <i class="fas fa-qrcode"></i>
                                        </div>
                                        <div>
                                            <div class="brand-lockup__eyebrow">Panel corporativo</div>
                                            <div class="brand-lockup__name">QR Asistencia</div>
                                        </div>
                                    </div>

                                    <span class="auth-pill mb-3 d-inline-flex align-items-center">
                                        <i class="fas fa-shield-alt mr-2"></i>
                                        Acceso seguro para administración
                                    </span>

                                    <h1 class="auth-hero__title mb-3">Control centralizado para RRHH y operación.</h1>
                                    <p class="auth-hero__copy mb-0">
                                        Ingresa al panel para gestionar grupos, horarios, reportes y auditoría desde una sola interfaz
                                        coherente con el tema Bootstrap del sistema.
                                    </p>
                                </div>

                                <div class="auth-hero__stats">
                                    <div class="auth-stat">
                                        <span class="auth-stat__value">24/7</span>
                                        <span class="auth-stat__label">Disponibilidad del módulo</span>
                                    </div>
                                    <div class="auth-stat">
                                        <span class="auth-stat__value">1</span>
                                        <span class="auth-stat__label">Portal administrativo</span>
                                    </div>
                                    <div class="auth-stat">
                                        <span class="auth-stat__value">QR</span>
                                        <span class="auth-stat__label">Asistencia controlada</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="auth-form-pane p-4 p-md-5 p-lg-5">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div>
                                        <div class="auth-form-kicker">Acceso administrativo</div>
                                        <h2 class="auth-form-title mb-1">Bienvenido de nuevo</h2>
                                        <p class="auth-form-subtitle mb-0">Introduce tus credenciales para continuar.</p>
                                    </div>
                                    <div class="d-none d-md-flex auth-form-badge">
                                        <i class="fas fa-lock mr-2"></i>
                                        Sesión protegida
                                    </div>
                                </div>

                                <form class="user auth-login-form" method="post" action="<?= htmlspecialchars(site_url('login'), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                                    <div class="form-group mb-3">
                                        <label for="login-email" class="small font-weight-bold text-gray-700 mb-2">Correo electrónico</label>
                                        <div class="input-group input-group-user auth-input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            </div>
                                            <input id="login-email" type="email" name="email" class="form-control form-control-user auth-form-control" placeholder="nombre@empresa.com" autocomplete="username" required autofocus>
                                        </div>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label for="login-password" class="small font-weight-bold text-gray-700 mb-2">Contraseña</label>
                                        <div class="input-group input-group-user auth-input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            </div>
                                            <input id="login-password" type="password" name="password" class="form-control form-control-user auth-form-control" placeholder="Tu contraseña" autocomplete="current-password" required>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-user btn-block auth-submit-btn">
                                        <i class="fas fa-sign-in-alt mr-2"></i>
                                        Entrar al panel
                                    </button>
                                </form>

                                <div class="auth-divider my-4"></div>

                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between">
                                    <div class="auth-link-copy mb-3 mb-sm-0">
                                        ¿Vas a registrar asistencia?
                                        <span class="d-block text-gray-600">Accede al módulo operativo sin entrar al panel administrativo.</span>
                                    </div>
                                    <a class="btn btn-outline-primary auth-secondary-btn" href="<?= htmlspecialchars(site_url('marcar'), ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="fas fa-qrcode mr-2"></i>
                                        Ir a asistencia
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