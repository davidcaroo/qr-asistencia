<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Infrastructure\Repositories\GroupRepository;
use App\Infrastructure\Repositories\GroupScheduleAssignmentRepository;
use App\Infrastructure\Repositories\ScheduleRepository;

final class ScheduleController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requireAdmin();

        $repository = new ScheduleRepository();
        $assignmentRepository = new GroupScheduleAssignmentRepository();
        $groupRepository = new GroupRepository();
        $schedules = $repository->all();
        $assignments = $assignmentRepository->allDetailed();
        $groups = $groupRepository->all();

        $this->view('admin/schedules/index', [
            'pageTitle' => 'Horarios por grupo',
            'schedules' => $schedules,
            'assignments' => $assignments,
            'groups' => $groups,
            'scheduleOptions' => $schedules,
            'scheduleCount' => count($schedules),
            'assignmentCount' => count($assignments),
            'groupCount' => count($groups),
        ]);
    }

    public function create(Request $request): void
    {
        Auth::requireAdmin();

        $this->renderForm('Nuevo horario', site_url('admin/horarios'), null);
    }

    public function store(Request $request): void
    {
        Auth::requireAdmin();

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/horarios/nuevo');
        }

        $payload = $this->schedulePayloadFromRequest($request);
        $errors = $this->validateSchedulePayload($payload);

        if ($errors !== []) {
            $this->renderForm('Nuevo horario', site_url('admin/horarios'), $payload, $errors);
            return;
        }

        (new ScheduleRepository())->create($payload);
        flash('success', 'Horario creado correctamente.');
        Response::redirect('/admin/horarios');
    }

    public function edit(Request $request): void
    {
        Auth::requireAdmin();

        $id = (int) $request->param('id', 0);
        $schedule = (new ScheduleRepository())->findById($id);

        if ($schedule === null) {
            Response::error(404, [
                'details' => 'No se encontró el horario solicitado.',
                'actionLabel' => 'Volver a horarios',
                'actionUrl' => site_url('admin/horarios'),
            ]);
        }

        $this->renderForm('Editar horario', site_url('admin/horarios/' . $id . '/actualizar'), $schedule);
    }

    public function update(Request $request): void
    {
        Auth::requireAdmin();

        $id = (int) $request->param('id', 0);

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/horarios/' . $id . '/editar');
        }

        $repository = new ScheduleRepository();
        if ($repository->findById($id) === null) {
            Response::error(404, [
                'details' => 'No se encontró el horario solicitado.',
                'actionLabel' => 'Volver a horarios',
                'actionUrl' => site_url('admin/horarios'),
            ]);
        }

        $payload = $this->schedulePayloadFromRequest($request);
        $errors = $this->validateSchedulePayload($payload);

        if ($errors !== []) {
            $payload['id'] = $id;
            $this->renderForm('Editar horario', site_url('admin/horarios/' . $id . '/actualizar'), $payload, $errors);
            return;
        }

        $repository->update($id, $payload);
        flash('success', 'Horario actualizado correctamente.');
        Response::redirect('/admin/horarios');
    }

    public function destroy(Request $request): void
    {
        Auth::requireAdmin();

        $id = (int) $request->param('id', 0);

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/horarios');
        }

        $repository = new ScheduleRepository();
        if ($repository->findById($id) === null) {
            Response::error(404, [
                'details' => 'No se encontró el horario solicitado.',
                'actionLabel' => 'Volver a horarios',
                'actionUrl' => site_url('admin/horarios'),
            ]);
        }

        $repository->delete($id);
        flash('success', 'Horario eliminado correctamente.');
        Response::redirect('/admin/horarios');
    }

    public function destroyMany(Request $request): void
    {
        Auth::requireAdmin();

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/horarios');
        }

        $ids = $this->normalizeSelectedIds($request->input('selected_ids', []));

        if ($ids === []) {
            flash('error', 'Debes seleccionar al menos un horario.');
            Response::redirect('/admin/horarios');
        }

        $deleted = (new ScheduleRepository())->deleteMany($ids);
        flash('success', 'Se eliminaron ' . $deleted . ' horario' . ($deleted === 1 ? '' : 's') . '.');
        Response::redirect('/admin/horarios');
    }

    public function assignStore(Request $request): void
    {
        Auth::requireAdmin();

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/horarios');
        }

        $scheduleId = (int) $request->input('schedule_id', 0);
        $groupId = (int) $request->input('group_id', 0);
        $dayOfWeekInput = $request->input('day_of_week', []);
        $validFrom = trim((string) $request->input('valid_from', ''));
        $validTo = trim((string) $request->input('valid_to', ''));
        $active = (int) ((string) $request->input('active', '1') === '1');

        $scheduleRepository = new ScheduleRepository();
        $groupRepository = new GroupRepository();
        $assignmentRepository = new GroupScheduleAssignmentRepository();

        if ($scheduleRepository->findById($scheduleId) === null || $groupRepository->findById($groupId) === null) {
            flash('error', 'Debes seleccionar un horario y un grupo válidos.');
            Response::redirect('/admin/horarios');
        }

        $selectedDays = $this->normalizeSelectedDays($dayOfWeekInput);

        if ($selectedDays === []) {
            flash('error', 'Debes seleccionar al menos un día para la asignación.');
            Response::redirect('/admin/horarios');
        }

        if ($validFrom !== '' && $validTo !== '' && $validFrom > $validTo) {
            flash('error', 'La fecha inicial no puede ser mayor que la fecha final.');
            Response::redirect('/admin/horarios');
        }

        foreach ($selectedDays as $dayOfWeek) {
            $assignmentRepository->create([
                'schedule_id' => $scheduleId,
                'group_id' => $groupId,
                'day_of_week' => $dayOfWeek,
                'valid_from' => $validFrom === '' ? null : $validFrom,
                'valid_to' => $validTo === '' ? null : $validTo,
                'active' => $active,
            ]);
        }

        flash('success', 'Horario asignado al grupo correctamente para ' . count($selectedDays) . ' día' . (count($selectedDays) === 1 ? '' : 's') . '.');
        Response::redirect('/admin/horarios');
    }

    public function unassign(Request $request): void
    {
        Auth::requireAdmin();

        $id = (int) $request->param('id', 0);

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/horarios');
        }

        $assignmentRepository = new GroupScheduleAssignmentRepository();
        if ($assignmentRepository->findById($id) === null) {
            Response::error(404, [
                'details' => 'No se encontró la asignación solicitada.',
                'actionLabel' => 'Volver a horarios',
                'actionUrl' => site_url('admin/horarios'),
            ]);
        }

        $assignmentRepository->delete($id);
        flash('success', 'Asignación eliminada correctamente.');
        Response::redirect('/admin/horarios');
    }

    private function renderForm(string $pageTitle, string $actionUrl, ?array $schedule = null, array $errors = []): void
    {
        $this->view('admin/schedules/form', [
            'pageTitle' => $pageTitle,
            'formTitle' => $pageTitle,
            'formAction' => $actionUrl,
            'schedule' => $schedule,
            'errors' => $errors,
        ]);
    }

    private function schedulePayloadFromRequest(Request $request): array
    {
        return [
            'name' => trim((string) $request->input('name', '')),
            'start_time' => trim((string) $request->input('start_time', '')),
            'end_time' => trim((string) $request->input('end_time', '')),
            'tolerance_before_minutes' => max(0, (int) $request->input('tolerance_before_minutes', 0)),
            'tolerance_after_minutes' => max(0, (int) $request->input('tolerance_after_minutes', 0)),
            'active' => (int) ((string) $request->input('active', '1') === '1'),
        ];
    }

    private function validateSchedulePayload(array $payload): array
    {
        $errors = [];

        if ($payload['name'] === '') {
            $errors[] = 'El nombre del horario es obligatorio.';
        }

        if ($payload['start_time'] === '' || $payload['end_time'] === '') {
            $errors[] = 'Debes indicar hora de inicio y fin.';
        }

        if ($payload['start_time'] !== '' && $payload['end_time'] !== '' && $payload['start_time'] >= $payload['end_time']) {
            $errors[] = 'La hora de inicio debe ser menor que la hora de fin.';
        }

        return $errors;
    }

    private function normalizeSelectedDays(mixed $selectedDays): array
    {
        if (!is_array($selectedDays)) {
            $selectedDays = $selectedDays === null || $selectedDays === '' ? [] : [$selectedDays];
        }

        $normalized = [];

        foreach ($selectedDays as $selectedDay) {
            $day = (int) $selectedDay;
            if ($day >= 1 && $day <= 7) {
                $normalized[$day] = $day;
            }
        }

        return array_values($normalized);
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
}
