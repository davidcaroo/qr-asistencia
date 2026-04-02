<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Infrastructure\Repositories\ScheduleRepository;

final class ScheduleController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requireAdmin();

        $repository = new ScheduleRepository();

        $this->view('admin/schedules/index', [
            'pageTitle' => 'Horarios por grupo',
            'schedules' => $repository->all(),
        ]);
    }
}
