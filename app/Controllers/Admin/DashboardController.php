<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Infrastructure\Repositories\AttendanceRepository;
use App\Infrastructure\Repositories\EmployeeRepository;

final class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        Auth::requireAdmin();

        $attendanceRepository = new AttendanceRepository();
        $employeeRepository = new EmployeeRepository();

        $this->view('admin/dashboard', [
            'pageTitle' => 'Panel administrativo',
            'totalEmployees' => count($employeeRepository->all(1000)),
            'todayAttendances' => $attendanceRepository->countToday(),
            'todayEntries' => $attendanceRepository->countTodayEntries(),
            'todayExits' => $attendanceRepository->countTodayExits(),
            'todayLateEntries' => $attendanceRepository->countTodayLateEntries(),
        ]);
    }
}
