<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Infrastructure\Repositories\AdminUserRepository;
use App\Infrastructure\Repositories\RoleRepository;
use App\Services\AuditLogger;

final class AdminUserController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requirePermission('users.manage');

        $users = (new AdminUserRepository())->all(200);

        $this->view('admin/users/index', [
            'pageTitle' => 'Usuarios administradores',
            'users' => $users,
        ]);
    }

    public function create(Request $request): void
    {
        Auth::requirePermission('users.manage');

        $this->renderForm('Nuevo usuario', site_url('admin/usuarios'), null, []);
    }

    public function store(Request $request): void
    {
        Auth::requirePermission('users.manage');

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad invalido.');
            Response::redirect('/admin/usuarios/nuevo');
        }

        $payload = $this->userPayloadFromRequest($request, true);
        $errors = $this->validatePayload($payload, true);

        if ($errors !== []) {
            $this->renderForm('Nuevo usuario', site_url('admin/usuarios'), $payload, $errors);
            return;
        }

        $repository = new AdminUserRepository();
        if ($repository->findByEmailAnyState($payload['email']) !== null) {
            $this->renderForm('Nuevo usuario', site_url('admin/usuarios'), $payload, ['Ya existe un usuario con ese correo.']);
            return;
        }

        $roleRepository = new RoleRepository();
        $role = $roleRepository->findById((int) $payload['role_id']);
        if ($role === null) {
            $this->renderForm('Nuevo usuario', site_url('admin/usuarios'), $payload, ['Debes seleccionar un rol valido.']);
            return;
        }

        $userId = $repository->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password_hash' => password_hash($payload['password'], PASSWORD_DEFAULT),
            'role' => (string) $role['slug'],
            'active' => $payload['active'],
        ]);
        $repository->setRole($userId, (int) $role['id']);

        AuditLogger::recordAdmin('admin_user.created', 'admin_user', $userId, [
            'name' => $payload['name'],
            'email' => $payload['email'],
            'role_id' => (int) $role['id'],
        ], $request->ip());

        flash('success', 'Usuario creado correctamente.');
        Response::redirect('/admin/usuarios');
    }

    public function edit(Request $request): void
    {
        Auth::requirePermission('users.manage');

        $id = (int) $request->param('id', 0);
        $repository = new AdminUserRepository();
        $user = $repository->findById($id);

        if ($user === null) {
            Response::error(404, [
                'details' => 'No se encontro el usuario solicitado.',
                'actionLabel' => 'Volver a usuarios',
                'actionUrl' => site_url('admin/usuarios'),
            ]);
        }

        $this->renderForm('Editar usuario', site_url('admin/usuarios/' . $id . '/actualizar'), $user, []);
    }

    public function update(Request $request): void
    {
        Auth::requirePermission('users.manage');

        $id = (int) $request->param('id', 0);

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad invalido.');
            Response::redirect('/admin/usuarios/' . $id . '/editar');
        }

        $repository = new AdminUserRepository();
        $existing = $repository->findById($id);

        if ($existing === null) {
            Response::error(404, [
                'details' => 'No se encontro el usuario solicitado.',
                'actionLabel' => 'Volver a usuarios',
                'actionUrl' => site_url('admin/usuarios'),
            ]);
        }

        $payload = $this->userPayloadFromRequest($request, false);
        $errors = $this->validatePayload($payload, false);

        if ($errors !== []) {
            $payload['id'] = $id;
            $this->renderForm('Editar usuario', site_url('admin/usuarios/' . $id . '/actualizar'), array_merge($existing, $payload), $errors);
            return;
        }

        $duplicate = $repository->findByEmailAnyState($payload['email']);
        if ($duplicate !== null && (int) $duplicate['id'] !== $id) {
            $this->renderForm('Editar usuario', site_url('admin/usuarios/' . $id . '/actualizar'), array_merge($existing, $payload), ['Ya existe otro usuario con ese correo.']);
            return;
        }

        $roleRepository = new RoleRepository();
        $role = $roleRepository->findById((int) $payload['role_id']);
        if ($role === null) {
            $this->renderForm('Editar usuario', site_url('admin/usuarios/' . $id . '/actualizar'), array_merge($existing, $payload), ['Debes seleccionar un rol valido.']);
            return;
        }

        $repository->update($id, [
            'name' => $payload['name'],
            'email' => $payload['email'],
            'role' => (string) $role['slug'],
            'active' => $payload['active'],
        ]);
        $repository->setRole($id, (int) $role['id']);

        if ($payload['password'] !== '') {
            $repository->updatePassword($id, password_hash($payload['password'], PASSWORD_DEFAULT));
        }

        AuditLogger::recordAdmin('admin_user.updated', 'admin_user', $id, [
            'name' => $payload['name'],
            'email' => $payload['email'],
            'role_id' => (int) $role['id'],
        ], $request->ip());

        flash('success', 'Usuario actualizado correctamente.');
        Response::redirect('/admin/usuarios');
    }

    public function destroy(Request $request): void
    {
        Auth::requirePermission('users.manage');

        $id = (int) $request->param('id', 0);

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad invalido.');
            Response::redirect('/admin/usuarios');
        }

        $admin = Auth::admin();
        if ($admin !== null && (int) $admin['id'] === $id) {
            flash('error', 'No puedes eliminar tu propio usuario.');
            Response::redirect('/admin/usuarios');
        }

        $repository = new AdminUserRepository();
        $user = $repository->findById($id);

        if ($user === null) {
            Response::error(404, [
                'details' => 'No se encontro el usuario solicitado.',
                'actionLabel' => 'Volver a usuarios',
                'actionUrl' => site_url('admin/usuarios'),
            ]);
        }

        $repository->delete($id);
        AuditLogger::recordAdmin('admin_user.deleted', 'admin_user', $id, [
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
        ], $request->ip());

        flash('success', 'Usuario eliminado correctamente.');
        Response::redirect('/admin/usuarios');
    }

    private function renderForm(string $pageTitle, string $actionUrl, ?array $user = null, array $errors = []): void
    {
        $roles = (new RoleRepository())->all();

        $this->view('admin/users/form', [
            'pageTitle' => $pageTitle,
            'formTitle' => $pageTitle,
            'formAction' => $actionUrl,
            'user' => $user,
            'roles' => $roles,
            'errors' => $errors,
        ]);
    }

    private function userPayloadFromRequest(Request $request, bool $requirePassword): array
    {
        $password = (string) $request->input('password', '');
        $passwordConfirm = (string) $request->input('password_confirm', '');

        return [
            'name' => trim((string) $request->input('name', '')),
            'email' => trim((string) $request->input('email', '')),
            'role_id' => (int) $request->input('role_id', 0),
            'active' => (int) ((string) $request->input('active', '1') === '1'),
            'password' => $password,
            'password_confirm' => $passwordConfirm,
            'require_password' => $requirePassword,
        ];
    }

    private function validatePayload(array $payload, bool $requirePassword): array
    {
        $errors = [];

        if ($payload['name'] === '') {
            $errors[] = 'El nombre es obligatorio.';
        }

        if ($payload['email'] === '' || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electronico no tiene un formato valido.';
        }

        if ($payload['role_id'] <= 0) {
            $errors[] = 'Debes seleccionar un rol.';
        }

        $password = $payload['password'];
        $passwordConfirm = $payload['password_confirm'];

        if ($requirePassword || $password !== '' || $passwordConfirm !== '') {
            if ($password === '' || $passwordConfirm === '') {
                $errors[] = 'Debes completar la contrasena y su confirmacion.';
            } elseif ($password !== $passwordConfirm) {
                $errors[] = 'La contrasena y la confirmacion no coinciden.';
            } elseif (strlen($password) < 8) {
                $errors[] = 'La contrasena debe tener al menos 8 caracteres.';
            }
        }

        return $errors;
    }
}
