<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Infrastructure\Repositories\AttendanceRepository;
use App\Infrastructure\Repositories\EmployeeRepository;
use App\Infrastructure\Repositories\QrSessionRepository;
use App\Infrastructure\Repositories\ScheduleRepository;
use App\Services\AttendanceService;
use App\Services\QrTokenService;
use App\Services\ScheduleService;
use DateTimeImmutable;

final class AttendanceController extends Controller
{
    public function show(Request $request): void
    {
        $token = (string) $request->input('token', '');
        $qrService = new QrTokenService(
            (string) env('APP_KEY', 'qr-asistencia-secret'),
            (int) config('app', 'qr_window_seconds', 30),
            site_url('')
        );
        $payload = $qrService->issueToken(new DateTimeImmutable('now'));

        $this->view('attendance/scan', [
            'pageTitle' => 'Registrar asistencia',
            'incomingToken' => $token,
            'currentQrUrl' => $payload['url'],
            'currentQrToken' => $payload['token'],
            'currentQrExpiresAt' => $payload['expires_at']->format(DATE_ATOM),
        ], 'layouts/auth');
    }

    public function store(Request $request): void
    {
        if (!\App\Core\Csrf::validate((string) $request->input('_csrf', ''))) {
            flash('error', 'Token de seguridad inválido.');
            Response::redirect('/marcar?token=' . urlencode((string) $request->input('token', '')));
        }

        $cedula = trim((string) $request->input('cedula', ''));
        $token = trim((string) $request->input('token', ''));

        if ($cedula === '' || $token === '') {
            flash('error', 'Debes completar la cédula y el token QR.');
            Response::redirect('/marcar?token=' . urlencode($token));
        }

        $service = new AttendanceService(
            new EmployeeRepository(),
            new AttendanceRepository(),
            new QrSessionRepository(),
            new ScheduleService(new ScheduleRepository()),
            new QrTokenService((string) env('APP_KEY', 'qr-asistencia-secret'), (int) config('app', 'qr_window_seconds', 30), site_url(''))
        );

        $result = $service->register($cedula, $token, $request->ip(), $request->userAgent());

        if (!$result['ok']) {
            flash('error', $result['message']);
            Response::redirect('/marcar?token=' . urlencode($token));
        }

        flash('success', $result['message']);
        Response::redirect('/marcar?token=' . urlencode($token));
    }
}
