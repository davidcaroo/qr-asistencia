<?php

declare(strict_types=1);

namespace App\Core;

use App\Infrastructure\Repositories\AdminUserRepository;

final class Auth
{
    public static function admin(): ?array
    {
        static $cachedAdmin = null;
        static $hasCache = false;

        if ($hasCache) {
            return $cachedAdmin;
        }

        $sessionAdmin = Session::get('admin_user');
        if (!is_array($sessionAdmin) || !isset($sessionAdmin['id'])) {
            $cachedAdmin = null;
            $hasCache = true;

            return null;
        }

        $freshAdmin = (new AdminUserRepository())->findById((int) $sessionAdmin['id']);
        if ($freshAdmin === null) {
            Session::forget('admin_user');
            $cachedAdmin = null;
            $hasCache = true;

            return null;
        }

        $freshAdmin['role'] = self::normalizeRole((string) ($freshAdmin['role'] ?? ''));
        Session::set('admin_user', [
            'id' => (int) $freshAdmin['id'],
            'name' => $freshAdmin['name'],
            'email' => $freshAdmin['email'],
            'role' => $freshAdmin['role'],
        ]);

        $cachedAdmin = $freshAdmin;
        $hasCache = true;

        return $cachedAdmin;
    }

    public static function checkAdmin(): bool
    {
        return self::admin() !== null;
    }

    public static function requireAdmin(): void
    {
        if (!self::checkAdmin()) {
            Response::redirect('/login');
        }
    }

    public static function role(): ?string
    {
        $admin = self::admin();

        if ($admin === null) {
            return null;
        }

        return self::normalizeRole((string) ($admin['role'] ?? ''));
    }

    public static function normalizeRole(string $role): ?string
    {
        $role = strtolower(trim($role));

        if ($role === '') {
            return null;
        }

        if ($role === 'admin') {
            return 'superadmin';
        }

        return $role;
    }

    public static function isSuperadmin(): bool
    {
        return self::role() === 'superadmin';
    }

    public static function hasRole(string|array $roles): bool
    {
        $role = self::role();

        if ($role === null) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];
        $roles = array_map(static fn(string $value): string => strtolower($value), $roles);

        return in_array($role, $roles, true);
    }

    public static function can(string $permission): bool
    {
        if (self::isSuperadmin()) {
            return true;
        }

        $role = self::role();
        if ($role === null) {
            return false;
        }

        $permissions = self::permissionsForRole($role);

        return in_array($permission, $permissions, true);
    }

    public static function requirePermission(string $permission): void
    {
        self::requireAdmin();

        if (!self::can($permission)) {
            Response::error(403, [
                'details' => 'Tu rol no tiene acceso a esta acción.',
                'actionLabel' => 'Volver al panel',
                'actionUrl' => site_url('admin'),
            ]);
        }
    }

    public static function permissionsForRole(string $role): array
    {
        $role = self::normalizeRole($role) ?? '';

        if ($role === 'superadmin') {
            return [
                'dashboard.view',
                'groups.manage',
                'employees.view',
                'employees.manage',
                'schedules.manage',
                'reports.view',
                'qr.view',
                'audit.view',
            ];
        }

        if ($role === 'rrhh') {
            return [
                'dashboard.view',
                'groups.manage',
                'employees.view',
                'employees.manage',
                'schedules.manage',
                'reports.view',
                'qr.view',
            ];
        }

        if ($role === 'supervisor') {
            return [
                'dashboard.view',
                'employees.view',
                'reports.view',
                'qr.view',
            ];
        }

        if ($role === 'operator') {
            return [
                'dashboard.view',
                'qr.view',
            ];
        }

        return [];
    }
}
