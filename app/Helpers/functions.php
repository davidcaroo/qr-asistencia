<?php

declare(strict_types=1);

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $basePath = dirname(__DIR__, 2);

        return $path === '' ? $basePath : $basePath . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : ''));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : ''));
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($basePath === '' || $basePath === '/') {
            return '';
        }

        return preg_replace('#/public$#', '', $basePath) ?? $basePath;
    }
}

if (!function_exists('site_url')) {
    function site_url(string $path = ''): string
    {
        $basePath = app_base_path();
        $normalizedPath = '/' . ltrim($path, '/');

        return ($basePath === '' ? '' : $basePath) . $normalizedPath;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        $normalized = strtolower((string) $value);

        return match ($normalized) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}

if (!function_exists('config')) {
    function config(string $file, ?string $key = null, mixed $default = null): mixed
    {
        static $cache = [];

        if (!array_key_exists($file, $cache)) {
            $configFile = base_path('app/Config/' . $file . '.php');
            $cache[$file] = is_file($configFile) ? require $configFile : [];
        }

        if ($key === null) {
            return $cache[$file];
        }

        return $cache[$file][$key] ?? $default;
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        return site_url('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $value = null): mixed
    {
        if ($value === null) {
            return App\Core\Session::getFlash($key);
        }

        App\Core\Session::flash($key, $value);

        return null;
    }
}

if (!function_exists('app_bootstrap')) {
    function app_bootstrap(): void
    {
        $envFile = base_path('.env');
        if (is_file($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

            foreach ($lines as $line) {
                $trimmed = trim($line);

                if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                    continue;
                }

                [$key, $value] = array_map('trim', explode('=', $trimmed, 2));
                $value = trim($value, "\"'");

                if ($key !== '') {
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                    putenv($key . '=' . $value);
                }
            }
        }

        date_default_timezone_set((string) env('APP_TIMEZONE', 'UTC'));
    }
}
