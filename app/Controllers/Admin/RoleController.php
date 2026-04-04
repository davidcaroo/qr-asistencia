<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Infrastructure\Repositories\PermissionRepository;
use App\Infrastructure\Repositories\RoleRepository;
use App\Services\AuditLogger;

final class RoleController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requirePermission('roles.manage');

        $repository = new RoleRepository();
        $roles = $repository->all();
        $permissionCounts = [];

        foreach ($roles as $role) {
            $permissionCounts[(int) $role['id']] = count($repository->permissionsForRole((int) $role['id']));
        }

        $this->view('admin/roles/index', [
            'pageTitle' => 'Roles y permisos',
            'roles' => $roles,
            'permissionCounts' => $permissionCounts,
        ]);
    }

    public function create(Request $request): void
    {
        Auth::requirePermission('roles.manage');

        $this->renderForm('Nuevo rol', site_url('admin/roles'), null, [], []);
    }

    public function store(Request $request): void
    {
        Auth::requirePermission('roles.manage');

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad invalido.');
            Response::redirect('/admin/roles/nuevo');
        }

        $payload = $this->rolePayloadFromRequest($request);
        $errors = $this->validatePayload($payload);
        $permissionIds = $this->normalizeSelectedIds($request->input('permission_ids', []));

        if ($errors !== []) {
            $this->renderForm('Nuevo rol', site_url('admin/roles'), $payload, $errors, $permissionIds);
            return;
        }

        $repository = new RoleRepository();

        if ($repository->findByName($payload['name']) !== null) {
            $this->renderForm('Nuevo rol', site_url('admin/roles'), $payload, ['Ya existe un rol con ese nombre.'], $permissionIds);
            return;
        }

        if ($repository->findBySlug($payload['slug']) !== null) {
            $this->renderForm('Nuevo rol', site_url('admin/roles'), $payload, ['Ya existe un rol con ese identificador.'], $permissionIds);
            return;
        }

        $roleId = $repository->create($payload);
        $repository->assignPermissions($roleId, $permissionIds);

        AuditLogger::recordAdmin('role.created', 'role', $roleId, [
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'permissions' => $permissionIds,
        ], $request->ip());

        flash('success', 'Rol creado correctamente.');
        Response::redirect('/admin/roles');
    }

    public function edit(Request $request): void
    {
        Auth::requirePermission('roles.manage');

        $id = (int) $request->param('id', 0);
        $repository = new RoleRepository();
        $role = $repository->findById($id);

        if ($role === null) {
            Response::error(404, [
                'details' => 'No se encontro el rol solicitado.',
                'actionLabel' => 'Volver a roles',
                'actionUrl' => site_url('admin/roles'),
            ]);
        }

        $permissionRows = $repository->permissionsForRole($id);
        $permissionIds = array_map(static fn(array $row): int => (int) $row['id'], $permissionRows);

        $this->renderForm('Editar rol', site_url('admin/roles/' . $id . '/actualizar'), $role, [], $permissionIds);
    }

    public function update(Request $request): void
    {
        Auth::requirePermission('roles.manage');

        $id = (int) $request->param('id', 0);

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad invalido.');
            Response::redirect('/admin/roles/' . $id . '/editar');
        }

        $repository = new RoleRepository();
        $existing = $repository->findById($id);

        if ($existing === null) {
            Response::error(404, [
                'details' => 'No se encontro el rol solicitado.',
                'actionLabel' => 'Volver a roles',
                'actionUrl' => site_url('admin/roles'),
            ]);
        }

        $payload = $this->rolePayloadFromRequest($request);
        $errors = $this->validatePayload($payload);
        $permissionIds = $this->normalizeSelectedIds($request->input('permission_ids', []));

        if ($errors !== []) {
            $payload['id'] = $id;
            $this->renderForm('Editar rol', site_url('admin/roles/' . $id . '/actualizar'), array_merge($existing, $payload), $errors, $permissionIds);
            return;
        }

        $duplicateName = $repository->findByName($payload['name']);
        if ($duplicateName !== null && (int) $duplicateName['id'] !== $id) {
            $this->renderForm('Editar rol', site_url('admin/roles/' . $id . '/actualizar'), array_merge($existing, $payload), ['Ya existe otro rol con ese nombre.'], $permissionIds);
            return;
        }

        $duplicateSlug = $repository->findBySlug($payload['slug']);
        if ($duplicateSlug !== null && (int) $duplicateSlug['id'] !== $id) {
            $this->renderForm('Editar rol', site_url('admin/roles/' . $id . '/actualizar'), array_merge($existing, $payload), ['Ya existe otro rol con ese identificador.'], $permissionIds);
            return;
        }

        $repository->update($id, $payload);
        $repository->assignPermissions($id, $permissionIds);

        AuditLogger::recordAdmin('role.updated', 'role', $id, [
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'permissions' => $permissionIds,
        ], $request->ip());

        flash('success', 'Rol actualizado correctamente.');
        Response::redirect('/admin/roles');
    }

    public function destroy(Request $request): void
    {
        Auth::requirePermission('roles.manage');

        $id = (int) $request->param('id', 0);

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad invalido.');
            Response::redirect('/admin/roles');
        }

        $repository = new RoleRepository();
        $role = $repository->findById($id);

        if ($role === null) {
            Response::error(404, [
                'details' => 'No se encontro el rol solicitado.',
                'actionLabel' => 'Volver a roles',
                'actionUrl' => site_url('admin/roles'),
            ]);
        }

        if ($repository->countAdminsWithRole($id) > 0) {
            flash('error', 'No puedes eliminar un rol con usuarios asignados.');
            Response::redirect('/admin/roles');
        }

        $repository->delete($id);
        AuditLogger::recordAdmin('role.deleted', 'role', $id, [
            'name' => $role['name'] ?? null,
            'slug' => $role['slug'] ?? null,
        ], $request->ip());

        flash('success', 'Rol eliminado correctamente.');
        Response::redirect('/admin/roles');
    }

    private function renderForm(string $pageTitle, string $actionUrl, ?array $role = null, array $errors = [], array $selectedPermissions = []): void
    {
        $permissions = (new PermissionRepository())->all();

        $this->view('admin/roles/form', [
            'pageTitle' => $pageTitle,
            'formTitle' => $pageTitle,
            'formAction' => $actionUrl,
            'role' => $role,
            'errors' => $errors,
            'permissions' => $permissions,
            'selectedPermissions' => $selectedPermissions,
        ]);
    }

    private function rolePayloadFromRequest(Request $request): array
    {
        $name = trim((string) $request->input('name', ''));
        $slugInput = trim((string) $request->input('slug', ''));

        return [
            'name' => $name,
            'slug' => $slugInput !== '' ? $this->slugify($slugInput) : $this->slugify($name),
            'description' => trim((string) $request->input('description', '')),
            'active' => (int) ((string) $request->input('active', '1') === '1'),
        ];
    }

    private function validatePayload(array $payload): array
    {
        $errors = [];

        if ($payload['name'] === '') {
            $errors[] = 'El nombre del rol es obligatorio.';
        }

        if ($payload['slug'] === '') {
            $errors[] = 'El identificador del rol es obligatorio.';
        }

        if (strlen($payload['name']) > 120) {
            $errors[] = 'El nombre no puede superar 120 caracteres.';
        }

        if (strlen($payload['slug']) > 120) {
            $errors[] = 'El identificador no puede superar 120 caracteres.';
        }

        if (strlen($payload['description']) > 255) {
            $errors[] = 'La descripcion no puede superar 255 caracteres.';
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

    private function slugify(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;

        return trim($value, '-') ?: '';
    }
}
