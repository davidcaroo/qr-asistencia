<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Infrastructure\Repositories\EmployeeRepository;

final class EmployeeController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requireAdmin();

        $repository = new EmployeeRepository();

        $this->view('admin/employees/index', [
            'pageTitle' => 'Empleados',
            'employees' => $repository->all(),
        ]);
    }
}
