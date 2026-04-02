<?php

$flashMessages = [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?= htmlspecialchars($pageTitle ?? 'Error', ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars(config('app', 'name'), ENT_QUOTES, 'UTF-8') ?></title>

    <link href="<?= htmlspecialchars(asset_url('vendor/fontawesome-free/css/all.min.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars(asset_url('css/sb-admin-2.min.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <link href="<?= htmlspecialchars(asset_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
</head>

<body class="bg-gradient-primary">
    <?= $content ?>

    <script src="<?= htmlspecialchars(asset_url('vendor/jquery/jquery.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('vendor/bootstrap/js/bootstrap.bundle.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('vendor/jquery-easing/jquery.easing.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(asset_url('js/sb-admin-2.min.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>

</html>