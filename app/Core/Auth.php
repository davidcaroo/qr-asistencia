<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function admin(): ?array
    {
        return Session::get('admin_user');
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
}
