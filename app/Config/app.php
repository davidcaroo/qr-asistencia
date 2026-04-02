<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'QR Asistencia'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'qr_window_seconds' => (int) env('QR_WINDOW_SECONDS', 30),
    'attendance_lock_minutes' => (int) env('ATTENDANCE_LOCK_MINUTES', 5),
];
