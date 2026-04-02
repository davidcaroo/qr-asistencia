<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Auth;
use App\Infrastructure\Repositories\AdminUserRepository;

final class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        $this->view('auth/login', [
            'pageTitle' => 'Acceso administrativo',
        ], 'layouts/auth');
    }

    public function login(Request $request): void
    {
        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/login');
        }

        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            flash('error', 'Debes indicar correo y contraseña.');
            Response::redirect('/login');
        }

        $repository = new AdminUserRepository();
        $user = $repository->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            flash('error', 'Credenciales inválidas.');
            Response::redirect('/login');
        }

        Session::regenerate();
        Session::set('admin_user', [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => Auth::normalizeRole((string) $user['role']) ?? 'rrhh',
        ]);

        $repository->touchLastLogin((int) $user['id']);

        Response::redirect('/admin');
    }

    public function logout(Request $request): void
    {
        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            Response::redirect('/admin');
        }

        Session::forget('admin_user');
        Response::redirect('/login');
    }
}
