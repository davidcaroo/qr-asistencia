<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Infrastructure\Repositories\GroupScheduleAssignmentRepository;
use App\Infrastructure\Repositories\EmployeeRepository;
use App\Infrastructure\Repositories\GroupRepository;
use App\Services\ScheduleService;
use App\Infrastructure\Repositories\ScheduleRepository;
use App\Services\EmployeeImportService;
use App\Services\AuditLogger;
use App\Core\Response;
use DateTimeImmutable;

final class EmployeeController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requirePermission('employees.view');

        $repository = new EmployeeRepository();

        $this->view('admin/employees/index', [
            'pageTitle' => 'Empleados',
            'employees' => $repository->all(1000),
            'groups' => (new GroupRepository())->all(),
        ]);
    }

    public function create(Request $request): void
    {
        Auth::requirePermission('employees.manage');

        $this->renderForm('Nuevo empleado', site_url('admin/empleados'), null);
    }

    public function store(Request $request): void
    {
        Auth::requirePermission('employees.manage');

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/empleados/nuevo');
        }

        $payload = $this->employeePayloadFromRequest($request);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $this->renderForm('Nuevo empleado', site_url('admin/empleados'), $payload, $errors);
            return;
        }

        $repository = new EmployeeRepository();

        if ($repository->findByCedulaAnyState($payload['cedula']) !== null) {
            $this->renderForm('Nuevo empleado', site_url('admin/empleados'), $payload, ['Ya existe un empleado con esa cédula.']);
            return;
        }

        $payload['pin_hash'] = $this->generatePinHash();
        $employeeId = $repository->create($payload);
        AuditLogger::recordAdmin('employee.created', 'employee', $employeeId, $payload, $request->ip());

        flash('success', 'Empleado creado correctamente.');
        Response::redirect('/admin/empleados');
    }

    public function edit(Request $request): void
    {
        Auth::requirePermission('employees.manage');

        $id = (int) $request->param('id', 0);
        $repository = new EmployeeRepository();
        $employee = $repository->findById($id);

        if ($employee === null) {
            Response::error(404, [
                'details' => 'No se encontró el empleado solicitado.',
                'actionLabel' => 'Volver a empleados',
                'actionUrl' => site_url('admin/empleados'),
            ]);
        }

        $this->renderForm('Editar empleado', site_url('admin/empleados/' . $id . '/actualizar'), $employee);
    }

    public function show(Request $request): void
    {
        Auth::requirePermission('employees.view');

        $id = (int) $request->param('id', 0);
        $employeeRepository = new EmployeeRepository();
        $employee = $employeeRepository->findDetailedById($id);

        if ($employee === null) {
            Response::error(404, [
                'details' => 'No se encontró el empleado solicitado.',
                'actionLabel' => 'Volver a empleados',
                'actionUrl' => site_url('admin/empleados'),
            ]);
        }

        $groupId = $employee['group_id'] !== null ? (int) $employee['group_id'] : null;
        $scheduleService = new ScheduleService(new ScheduleRepository());
        $currentSchedule = $scheduleService->resolveForEmployeeGroup($groupId, new DateTimeImmutable('now'));
        $assignments = $groupId !== null ? (new GroupScheduleAssignmentRepository())->allByGroupId($groupId) : [];

        $this->view('admin/employees/show', [
            'pageTitle' => 'Detalle de empleado',
            'employee' => $employee,
            'currentSchedule' => $currentSchedule,
            'assignments' => $assignments,
        ]);
    }

    public function update(Request $request): void
    {
        Auth::requirePermission('employees.manage');

        $id = (int) $request->param('id', 0);

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/empleados/' . $id . '/editar');
        }

        $repository = new EmployeeRepository();
        $existing = $repository->findById($id);

        if ($existing === null) {
            Response::error(404, [
                'details' => 'No se encontró el empleado solicitado.',
                'actionLabel' => 'Volver a empleados',
                'actionUrl' => site_url('admin/empleados'),
            ]);
        }

        $payload = $this->employeePayloadFromRequest($request, false);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $payload['id'] = $id;
            $this->renderForm('Editar empleado', site_url('admin/empleados/' . $id . '/actualizar'), array_merge($existing, $payload), $errors);
            return;
        }

        $duplicate = $repository->findByCedulaAnyState($payload['cedula']);
        if ($duplicate !== null && (int) $duplicate['id'] !== $id) {
            $this->renderForm('Editar empleado', site_url('admin/empleados/' . $id . '/actualizar'), array_merge($existing, $payload), ['Ya existe otro empleado con esa cédula.']);
            return;
        }

        $repository->update($id, $payload);
        AuditLogger::recordAdmin('employee.updated', 'employee', $id, $payload, $request->ip());
        flash('success', 'Empleado actualizado correctamente.');
        Response::redirect('/admin/empleados');
    }

    public function destroy(Request $request): void
    {
        Auth::requirePermission('employees.manage');

        $id = (int) $request->param('id', 0);

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/empleados');
        }

        $repository = new EmployeeRepository();
        if ($repository->findById($id) === null) {
            Response::error(404, [
                'details' => 'No se encontró el empleado solicitado.',
                'actionLabel' => 'Volver a empleados',
                'actionUrl' => site_url('admin/empleados'),
            ]);
        }

        $existing = $repository->findById($id);
        $repository->delete($id);
        AuditLogger::recordAdmin('employee.deleted', 'employee', $id, [
            'cedula' => $existing['cedula'] ?? null,
            'full_name' => $existing['full_name'] ?? null,
        ], $request->ip());
        flash('success', 'Empleado eliminado correctamente.');
        Response::redirect('/admin/empleados');
    }

    public function destroyMany(Request $request): void
    {
        Auth::requirePermission('employees.manage');

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/empleados');
        }

        $ids = $this->normalizeSelectedIds($request->input('selected_ids', []));

        if ($ids === []) {
            flash('error', 'Debes seleccionar al menos un empleado.');
            Response::redirect('/admin/empleados');
        }

        $deleted = (new EmployeeRepository())->deleteMany($ids);
        AuditLogger::recordAdmin('employee.bulk_deleted', 'employee', null, [
            'ids' => $ids,
            'deleted_count' => $deleted,
        ], $request->ip());
        flash('success', 'Se eliminaron ' . $deleted . ' empleado' . ($deleted === 1 ? '' : 's') . '.');
        Response::redirect('/admin/empleados');
    }

    public function importForm(Request $request): void
    {
        Auth::requirePermission('employees.manage');

        $this->view('admin/employees/import', [
            'pageTitle' => 'Importar empleados',
        ]);
    }

    public function importStore(Request $request): void
    {
        Auth::requirePermission('employees.manage');

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/empleados/importar');
        }

        $file = $_FILES['file'] ?? null;

        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            flash('error', 'Debes seleccionar un archivo CSV, XLS o XLSX.');
            Response::redirect('/admin/empleados/importar');
        }

        if (!is_uploaded_file((string) $file['tmp_name'])) {
            flash('error', 'No se pudo leer el archivo subido.');
            Response::redirect('/admin/empleados/importar');
        }

        $service = new EmployeeImportService(new EmployeeRepository(), new GroupRepository());

        try {
            $summary = $service->import((string) $file['tmp_name']);
        } catch (\Throwable $throwable) {
            flash('error', $throwable->getMessage());
            Response::redirect('/admin/empleados/importar');
        }

        $messages = [];
        if (($summary['created'] ?? 0) > 0) {
            $messages[] = (int) $summary['created'] . ' creados';
        }
        if (($summary['updated'] ?? 0) > 0) {
            $messages[] = (int) $summary['updated'] . ' actualizados';
        }
        if (($summary['skipped'] ?? 0) > 0) {
            $messages[] = (int) $summary['skipped'] . ' filas vacías omitidas';
        }

        if ($messages === []) {
            $messages[] = 'sin cambios detectados';
        }

        flash('success', 'Importación completada: ' . implode(', ', $messages) . '.');
        if (!empty($summary['errors'])) {
            flash('error', implode(' ', array_slice($summary['errors'], 0, 3)));
        }

        AuditLogger::recordAdmin('employee.imported', 'employee', null, [
            'created' => (int) ($summary['created'] ?? 0),
            'updated' => (int) ($summary['updated'] ?? 0),
            'skipped' => (int) ($summary['skipped'] ?? 0),
            'errors' => array_slice($summary['errors'] ?? [], 0, 3),
        ], $request->ip());

        Response::redirect('/admin/empleados');
    }

    private function renderForm(string $pageTitle, string $actionUrl, ?array $employee = null, array $errors = []): void
    {
        $groups = (new GroupRepository())->all();

        $this->view('admin/employees/form', [
            'pageTitle' => $pageTitle,
            'formTitle' => $pageTitle,
            'formAction' => $actionUrl,
            'employee' => $employee,
            'groups' => $groups,
            'errors' => $errors,
        ]);
    }

    private function employeePayloadFromRequest(Request $request, bool $includePinHash = true): array
    {
        $groupId = trim((string) $request->input('group_id', ''));
        $email = trim((string) $request->input('email', ''));

        $payload = [
            'group_id' => $groupId === '' ? null : (int) $groupId,
            'cedula' => trim((string) $request->input('cedula', '')),
            'full_name' => trim((string) $request->input('full_name', '')),
            'email' => $email === '' ? null : $email,
            'active' => (int) ((string) $request->input('active', '1') === '1'),
        ];

        if ($includePinHash) {
            $payload['pin_hash'] = $this->generatePinHash();
        }

        return $payload;
    }

    private function validatePayload(array $payload): array
    {
        $errors = [];

        if ($payload['cedula'] === '') {
            $errors[] = 'La cédula es obligatoria.';
        }

        if ($payload['full_name'] === '') {
            $errors[] = 'El nombre completo es obligatorio.';
        }

        if ($payload['email'] !== null && !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no tiene un formato válido.';
        }

        return $errors;
    }

    private function normalizeSelectedIds(mixed $selectedIds): array
    {
        if (!is_array($selectedIds)) {
            $selectedIds = $selectedIds === null || $selectedIds === '' ? [] : [$selectedIds];
        }

        $normalized = [];

        foreach ($selectedIds as $selectedId) {
            $id = (int) $selectedId;
            if ($id > 0) {
                $normalized[$id] = $id;
            }
        }

        return array_values($normalized);
    }

    private function generatePinHash(): string
    {
        return password_hash(bin2hex(random_bytes(4)), PASSWORD_DEFAULT);
    }
}
