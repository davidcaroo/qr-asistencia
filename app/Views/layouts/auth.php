<?php

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

    <title><?= htmlspecialchars($pageTitle ?? 'Acceso', ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars(config('app', 'name'), ENT_QUOTES, 'UTF-8') ?></title>

    <link href="<?= htmlspecialchars(asset_url('vendor/fontawesome-free/css/all.min.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars(asset_url('css/sb-admin-2.min.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <link href="<?= htmlspecialchars(asset_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
</head>

<body class="bg-gradient-primary attendance-page">
    <?= $content ?>

    <script src="<?= htmlspecialchars(asset_url('vendor/jquery/jquery.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('vendor/bootstrap/js/bootstrap.bundle.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('vendor/jquery-easing/jquery.easing.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.APP_FLASH = <?= json_encode($flashMessages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="<?= htmlspecialchars(asset_url('js/sb-admin-2.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('assets/js/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>

</html>