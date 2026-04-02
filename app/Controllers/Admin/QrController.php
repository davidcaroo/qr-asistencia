<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Infrastructure\Repositories\QrSessionRepository;
use App\Services\QrTokenService;

final class QrController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requireAdmin();

        $this->view('admin/qr/live', [
            'pageTitle' => 'QR global rotativo',
        ]);
    }

    public function current(Request $request): void
    {
        Auth::requireAdmin();

        $service = new QrTokenService(
            (string) env('APP_KEY', 'qr-asistencia-secret'),
            (int) config('app', 'qr_window_seconds', 30),
            site_url('')
        );

        $payload = $service->issueToken();
        $repository = new QrSessionRepository();
        $existing = $repository->findValidByTokenHash($payload['hash']);

        if ($existing === null) {
            $repository->create([
                'token_hash' => $payload['hash'],
                'window_start' => $payload['window_start']->format('Y-m-d H:i:s'),
                'window_end' => $payload['window_end']->format('Y-m-d H:i:s'),
                'expires_at' => $payload['expires_at']->format('Y-m-d H:i:s'),
            ]);
        }

        Response::json([
            'ok' => true,
            'token' => $payload['token'],
            'url' => $payload['url'],
            'expires_at' => $payload['expires_at']->format(DATE_ATOM),
        ]);
    }
}
