<?php

declare(strict_types=1);

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\EmployeeController;
use App\Controllers\Admin\QrController;
use App\Controllers\Admin\ScheduleController;
use App\Controllers\AttendanceController;
use App\Controllers\AuthController;
use App\Core\Response;
use App\Core\Router;

$router = new Router();

$router->get('/', function (): void {
    Response::redirect('/marcar');
});

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/marcar', [AttendanceController::class, 'show']);
$router->post('/marcar', [AttendanceController::class, 'store']);

$router->get('/admin', [DashboardController::class, 'index']);
$router->get('/admin/empleados', [EmployeeController::class, 'index']);
$router->get('/admin/horarios', [ScheduleController::class, 'index']);
$router->get('/admin/qr', [QrController::class, 'index']);
$router->get('/api/qr/current', [QrController::class, 'current']);

return $router;
