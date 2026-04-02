<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Infrastructure\Repositories\AttendanceRepository;
use App\Infrastructure\Repositories\EmployeeRepository;
use App\Infrastructure\Repositories\GroupRepository;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class ReportController extends Controller
{
    public function attendance(Request $request): void
    {
        Auth::requirePermission('reports.view');

        $report = $this->buildReport($request);

        $this->view('admin/reports/attendance', array_merge($report, [
            'pageTitle' => 'Reporte mensual de asistencia',
        ]));
    }

    public function export(Request $request): void
    {
        Auth::requirePermission('reports.view');

        $format = strtolower((string) $request->input('format', 'xlsx'));
        $allowedFormats = ['csv', 'xls', 'xlsx'];

        if (!in_array($format, $allowedFormats, true)) {
            Response::error(400, [
                'details' => 'Formato no soportado. Usa CSV, XLS o XLSX.',
                'actionLabel' => 'Volver al reporte',
                'actionUrl' => site_url('admin/reportes/asistencia'),
            ]);
        }

        $report = $this->buildReport($request);
        $spreadsheet = $this->buildSpreadsheet($report, $format !== 'csv');
        $filename = 'reporte-asistencia-' . $report['monthValue'];

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $writer = match ($format) {
            'csv' => new Csv($spreadsheet),
            'xls' => new Xls($spreadsheet),
            default => new Xlsx($spreadsheet),
        };

        if ($format === 'csv') {
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setSheetIndex(0);
            $writer->setUseBOM(true);
            $writer->setExcelCompatibility(true);
            $writer->setLineEnding("\r\n");
        }

        $contentType = match ($format) {
            'csv' => 'text/csv',
            'xls' => 'application/vnd.ms-excel',
            default => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
        } else {
            header('Content-Type: ' . $contentType);
        }
        header('Content-Disposition: attachment; filename="' . $filename . '.' . $format . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer->save('php://output');
        exit;
    }

    private function buildReport(Request $request): array
    {
        $monthValue = $this->sanitizeMonth((string) $request->input('month', (new DateTimeImmutable('first day of this month'))->format('Y-m')));
        $monthStart = DateTimeImmutable::createFromFormat('!Y-m-d', $monthValue . '-01') ?: new DateTimeImmutable('first day of this month');
        $monthEnd = $monthStart->modify('last day of this month');

        $employeeId = (int) $request->input('employee_id', 0);
        $groupId = (int) $request->input('group_id', 0);

        $employeeRepository = new EmployeeRepository();
        $attendanceRepository = new AttendanceRepository();
        $groupRepository = new GroupRepository();

        $summaryRows = $attendanceRepository->monthlyEmployeeSummary(
            $monthStart,
            $monthEnd,
            $employeeId > 0 ? $employeeId : null,
            $groupId > 0 ? $groupId : null,
        );

        $totals = [
            'employees' => count($summaryRows),
            'entries' => 0,
            'exits' => 0,
            'late_entries' => 0,
        ];

        foreach ($summaryRows as $row) {
            $totals['entries'] += (int) ($row['total_entries'] ?? 0);
            $totals['exits'] += (int) ($row['total_exits'] ?? 0);
            $totals['late_entries'] += (int) ($row['total_late_entries'] ?? 0);
        }

        return [
            'monthValue' => $monthValue,
            'monthLabel' => $this->monthLabel($monthStart),
            'summaryRows' => $summaryRows,
            'totals' => $totals,
            'filters' => [
                'month' => $monthValue,
                'employee_id' => $employeeId > 0 ? $employeeId : '',
                'group_id' => $groupId > 0 ? $groupId : '',
            ],
            'employees' => $employeeRepository->all(1000),
            'groups' => $groupRepository->all(),
        ];
    }

    private function buildSpreadsheet(array $report, bool $withBranding): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen');

        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');

        $sheet->setCellValue('A1', 'Reporte mensual de asistencia');
        $sheet->setCellValue('A2', 'Mes: ' . $report['monthLabel']);
        $sheet->setCellValue('A3', 'Generado: ' . (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'));

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('A2:A3')->getFont()->setSize(11);

        if ($withBranding) {
            $logoPath = public_path('img/logo-qr-asistencia.png');

            if (is_file($logoPath)) {
                $drawing = new Drawing();
                $drawing->setName('Logo QR Asistencia');
                $drawing->setDescription('Logo del sistema');
                $drawing->setPath($logoPath);
                $drawing->setCoordinates('H1');
                $drawing->setOffsetX(8);
                $drawing->setOffsetY(4);
                $drawing->setHeight(52);
                $drawing->setWorksheet($sheet);
                $sheet->getRowDimension(1)->setRowHeight(42);
            }
        }

        $headers = ['Cédula', 'Empleado', 'Grupo', 'Entradas', 'Salidas', 'Tardanzas', 'Primera entrada', 'Última salida', 'Último registro'];
        $rowNumber = 5;

        foreach ($headers as $columnIndex => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex + 1) . $rowNumber, $header);
        }

        $rowNumber++;

        foreach ($report['summaryRows'] as $row) {
            $sheet->setCellValue('A' . $rowNumber, (string) ($row['cedula'] ?? ''));
            $sheet->setCellValue('B' . $rowNumber, (string) ($row['full_name'] ?? ''));
            $sheet->setCellValue('C' . $rowNumber, (string) ($row['group_name'] ?? '-'));
            $sheet->setCellValue('D' . $rowNumber, (int) ($row['total_entries'] ?? 0));
            $sheet->setCellValue('E' . $rowNumber, (int) ($row['total_exits'] ?? 0));
            $sheet->setCellValue('F' . $rowNumber, (int) ($row['total_late_entries'] ?? 0));
            $sheet->setCellValue('G' . $rowNumber, $this->formatDateTimeCell($row['first_entry_at'] ?? null));
            $sheet->setCellValue('H' . $rowNumber, $this->formatDateTimeCell($row['last_exit_at'] ?? null));
            $sheet->setCellValue('I' . $rowNumber, $this->formatDateTimeCell($row['last_mark_at'] ?? null));
            $rowNumber++;
        }

        $sheet->setAutoFilter('A5:I' . max(5, $rowNumber - 1));
        $sheet->freezePane('A6');

        return $spreadsheet;
    }

    private function sanitizeMonth(string $month): string
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return (new DateTimeImmutable('first day of this month'))->format('Y-m');
        }

        $monthNumber = (int) substr($month, 5, 2);
        if ($monthNumber < 1 || $monthNumber > 12) {
            return (new DateTimeImmutable('first day of this month'))->format('Y-m');
        }

        return $month;
    }

    private function monthLabel(DateTimeImmutable $monthStart): string
    {
        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return ($months[(int) $monthStart->format('n')] ?? $monthStart->format('m')) . ' ' . $monthStart->format('Y');
    }

    private function formatDateTimeCell(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $value);

        return $dateTime ? $dateTime->format('Y-m-d H:i') : (string) $value;
    }
}
