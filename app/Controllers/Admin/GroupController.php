<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Infrastructure\Repositories\GroupRepository;

final class GroupController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requireAdmin();

        $groups = (new GroupRepository())->all();

        $this->view('admin/groups/index', [
            'pageTitle' => 'Grupos',
            'groups' => $groups,
            'groupCount' => count($groups),
        ]);
    }

    public function create(Request $request): void
    {
        Auth::requireAdmin();

        $this->renderForm('Nuevo grupo', site_url('admin/grupos'), null);
    }

    public function store(Request $request): void
    {
        Auth::requireAdmin();

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/grupos/nuevo');
        }

        $payload = $this->groupPayloadFromRequest($request);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $this->renderForm('Nuevo grupo', site_url('admin/grupos'), $payload, $errors);
            return;
        }

        $repository = new GroupRepository();

        if ($repository->findByName($payload['name']) !== null) {
            $this->renderForm('Nuevo grupo', site_url('admin/grupos'), $payload, ['Ya existe un grupo con ese nombre.']);
            return;
        }

        if ($repository->findBySlug($payload['slug']) !== null) {
            $this->renderForm('Nuevo grupo', site_url('admin/grupos'), $payload, ['Ya existe un grupo con ese identificador.']);
            return;
        }

        $repository->create($payload);
        flash('success', 'Grupo creado correctamente.');
        Response::redirect('/admin/grupos');
    }

    public function edit(Request $request): void
    {
        Auth::requireAdmin();

        $id = (int) $request->param('id', 0);
        $group = (new GroupRepository())->findById($id);

        if ($group === null) {
            Response::error(404, [
                'details' => 'No se encontró el grupo solicitado.',
                'actionLabel' => 'Volver a grupos',
                'actionUrl' => site_url('admin/grupos'),
            ]);
        }

        $this->renderForm('Editar grupo', site_url('admin/grupos/' . $id . '/actualizar'), $group);
    }

    public function update(Request $request): void
    {
        Auth::requireAdmin();

        $id = (int) $request->param('id', 0);

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/grupos/' . $id . '/editar');
        }

        $repository = new GroupRepository();
        $existing = $repository->findById($id);

        if ($existing === null) {
            Response::error(404, [
                'details' => 'No se encontró el grupo solicitado.',
                'actionLabel' => 'Volver a grupos',
                'actionUrl' => site_url('admin/grupos'),
            ]);
        }

        $payload = $this->groupPayloadFromRequest($request);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $payload['id'] = $id;
            $this->renderForm('Editar grupo', site_url('admin/grupos/' . $id . '/actualizar'), array_merge($existing, $payload), $errors);
            return;
        }

        $duplicateName = $repository->findByName($payload['name']);
        if ($duplicateName !== null && (int) $duplicateName['id'] !== $id) {
            $payload['id'] = $id;
            $this->renderForm('Editar grupo', site_url('admin/grupos/' . $id . '/actualizar'), array_merge($existing, $payload), ['Ya existe otro grupo con ese nombre.']);
            return;
        }

        $duplicateSlug = $repository->findBySlug($payload['slug']);
        if ($duplicateSlug !== null && (int) $duplicateSlug['id'] !== $id) {
            $payload['id'] = $id;
            $this->renderForm('Editar grupo', site_url('admin/grupos/' . $id . '/actualizar'), array_merge($existing, $payload), ['Ya existe otro grupo con ese identificador.']);
            return;
        }

        $repository->update($id, $payload);
        flash('success', 'Grupo actualizado correctamente.');
        Response::redirect('/admin/grupos');
    }

    public function destroy(Request $request): void
    {
        Auth::requireAdmin();

        $id = (int) $request->param('id', 0);

        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/admin/grupos');
        }

        $repository = new GroupRepository();
        if ($repository->findById($id) === null) {
            Response::error(404, [
                'details' => 'No se encontró el grupo solicitado.',
                'actionLabel' => 'Volver a grupos',
                'actionUrl' => site_url('admin/grupos'),
            ]);
        }

        $repository->delete($id);
        flash('success', 'Grupo eliminado correctamente.');
        Response::redirect('/admin/grupos');
    }

    private function renderForm(string $pageTitle, string $actionUrl, ?array $group = null, array $errors = []): void
    {
        $this->view('admin/groups/form', [
            'pageTitle' => $pageTitle,
            'formTitle' => $pageTitle,
            'formAction' => $actionUrl,
            'group' => $group,
            'errors' => $errors,
        ]);
    }

    private function groupPayloadFromRequest(Request $request): array
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
            $errors[] = 'El nombre del grupo es obligatorio.';
        }

        if ($payload['slug'] === '') {
            $errors[] = 'El identificador del grupo es obligatorio.';
        }

        if (strlen($payload['name']) > 120) {
            $errors[] = 'El nombre no puede superar 120 caracteres.';
        }

        if (strlen($payload['slug']) > 120) {
            $errors[] = 'El identificador no puede superar 120 caracteres.';
        }

        if (strlen($payload['description']) > 255) {
            $errors[] = 'La descripción no puede superar 255 caracteres.';
        }

        return $errors;
    }

    private function slugify(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;

        return trim($value, '-') ?: '';
    }
}
