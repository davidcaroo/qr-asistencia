<?php

declare(strict_types=1);

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\GroupController;
use App\Controllers\Admin\EmployeeController;
use App\Controllers\Admin\ReportController;
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
$router->get('/admin/grupos', [GroupController::class, 'index']);
$router->get('/admin/grupos/nuevo', [GroupController::class, 'create']);
$router->post('/admin/grupos', [GroupController::class, 'store']);
$router->get('/admin/grupos/{id}/editar', [GroupController::class, 'edit']);
$router->post('/admin/grupos/{id}/actualizar', [GroupController::class, 'update']);
$router->post('/admin/grupos/{id}/eliminar', [GroupController::class, 'destroy']);
$router->get('/admin/empleados', [EmployeeController::class, 'index']);
$router->get('/admin/empleados/nuevo', [EmployeeController::class, 'create']);
$router->post('/admin/empleados', [EmployeeController::class, 'store']);
$router->post('/admin/empleados/eliminar-masivo', [EmployeeController::class, 'destroyMany']);
$router->get('/admin/empleados/{id}/ver', [EmployeeController::class, 'show']);
$router->get('/admin/empleados/{id}/editar', [EmployeeController::class, 'edit']);
$router->post('/admin/empleados/{id}/actualizar', [EmployeeController::class, 'update']);
$router->post('/admin/empleados/{id}/eliminar', [EmployeeController::class, 'destroy']);
$router->get('/admin/empleados/importar', [EmployeeController::class, 'importForm']);
$router->post('/admin/empleados/importar', [EmployeeController::class, 'importStore']);
$router->get('/admin/horarios', [ScheduleController::class, 'index']);
$router->get('/admin/horarios/nuevo', [ScheduleController::class, 'create']);
$router->post('/admin/horarios', [ScheduleController::class, 'store']);
$router->post('/admin/horarios/eliminar-masivo', [ScheduleController::class, 'destroyMany']);
$router->get('/admin/horarios/{id}/editar', [ScheduleController::class, 'edit']);
$router->post('/admin/horarios/{id}/actualizar', [ScheduleController::class, 'update']);
$router->post('/admin/horarios/{id}/eliminar', [ScheduleController::class, 'destroy']);
$router->post('/admin/horarios/asignar', [ScheduleController::class, 'assignStore']);
$router->post('/admin/horarios/asignaciones/{id}/eliminar', [ScheduleController::class, 'unassign']);
$router->get('/admin/qr', [QrController::class, 'index']);
$router->get('/api/qr/current', [QrController::class, 'current']);
$router->get('/admin/reportes/asistencia', [ReportController::class, 'attendance']);
$router->get('/admin/reportes/asistencia/exportar', [ReportController::class, 'export']);

return $router;
