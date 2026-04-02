<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

if (is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    require dirname(__DIR__) . '/vendor/autoload.php';
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require $file;
        }
    });

    require dirname(__DIR__) . '/app/Helpers/functions.php';
}

$router = require base_path('app/Routes/web.php');

app_bootstrap();
Session::start();

$errorCode = (int) ($_GET['error'] ?? 0);
if ($errorCode >= 400) {
    Response::error($errorCode);
}

try {
    $router->dispatch(new Request());
} catch (\Throwable $throwable) {
    $details = config('app', 'debug')
        ? $throwable->getMessage() . ' in ' . $throwable->getFile() . ':' . $throwable->getLine()
        : null;

    http_response_code(500);

    try {
        View::render('errors/show', [
            'statusCode' => 500,
            'pageTitle' => 'Error interno del servidor',
            'title' => 'Error interno del servidor',
            'message' => 'Ocurrió un problema inesperado al cargar la aplicación.',
            'details' => $details,
            'actionLabel' => 'Ir al marcador',
            'actionUrl' => site_url('marcar'),
            'helpText' => 'Si estás en desarrollo, revisa el detalle técnico mostrado abajo.',
        ], 'layouts/error');
    } catch (\Throwable) {
        echo '500 Internal Server Error';
    }

    exit;
}
