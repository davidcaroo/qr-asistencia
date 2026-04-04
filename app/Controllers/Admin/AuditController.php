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

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'actor_type' => trim((string) $request->input('actor_type', '')),
            'action' => trim((string) $request->input('action', '')),
            'entity' => trim((string) $request->input('entity', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $perPage = (int) $request->input('per_page', 10);
        $allowedPerPage = [10, 25, 50];

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $repository = new AuditLogRepository();
        $totalCount = $repository->countDetailed($filters);

        $currentPage = max(1, (int) $request->input('page', 1));
        $totalPages = max(1, (int) ceil($totalCount / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        $logs = $repository->paginateDetailed($perPage, $offset, $filters);

        $this->view('admin/audit/index', [
            'pageTitle' => 'Auditoría',
            'logs' => $logs,
            'logCount' => $totalCount,
            'filters' => $filters,
            'pagination' => [
                'currentPage' => $currentPage,
                'perPage' => $perPage,
                'totalPages' => $totalPages,
                'totalCount' => $totalCount,
                'from' => $totalCount > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $totalCount),
            ],
        ]);
    }
}
