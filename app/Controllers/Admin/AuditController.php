<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Infrastructure\Repositories\AuditLogRepository;

final class AuditController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requirePermission('audit.view');

        $logs = (new AuditLogRepository())->recentDetailed(150);

        $this->view('admin/audit/index', [
            'pageTitle' => 'Auditoría',
            'logs' => $logs,
            'logCount' => count($logs),
        ]);
    }
}
