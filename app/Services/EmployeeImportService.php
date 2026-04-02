<?php

declare(strict_types=1);

namespace App\Services;

use App\Infrastructure\Repositories\EmployeeRepository;
use App\Infrastructure\Repositories\GroupRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use RuntimeException;

final class EmployeeImportService
{
    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly GroupRepository $groupRepository,
    ) {}

    public function import(string $filePath): array
    {
        $rows = $this->loadRows($filePath);

        if (count($rows) < 2) {
            throw new RuntimeException('El archivo no contiene filas suficientes para importar.');
        }

        $headers = $this->normalizeHeaders(array_shift($rows));
        $summary = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($rows as $index => $row) {
            if ($this->isEmptyRow($row)) {
                $summary['skipped']++;
                continue;
            }

            $rowNumber = $index + 2;
            $data = $this->rowToAssoc($headers, $row);
            $cedula = trim((string) ($data['cedula'] ?? ''));
            $fullName = trim((string) ($data['full_name'] ?? $data['nombre'] ?? $data['name'] ?? ''));
            $email = trim((string) ($data['email'] ?? $data['correo'] ?? ''));
            $groupValue = trim((string) ($data['group_id'] ?? $data['group'] ?? $data['grupo'] ?? ''));
            $active = $this->parseBoolean($data['active'] ?? $data['estado'] ?? $data['activo'] ?? 1);

            if ($cedula === '' || $fullName === '') {
                $summary['errors'][] = 'Fila ' . $rowNumber . ': cédula y nombre son obligatorios.';
                continue;
            }

            $groupId = $this->resolveGroupId($groupValue);
            $payload = [
                'group_id' => $groupId,
                'cedula' => $cedula,
                'full_name' => $fullName,
                'email' => $email !== '' ? $email : null,
                'active' => $active ? 1 : 0,
                'pin_hash' => password_hash(bin2hex(random_bytes(4)), PASSWORD_DEFAULT),
            ];

            $existing = $this->employeeRepository->findByCedulaAnyState($cedula);

            if ($existing !== null) {
                $this->employeeRepository->update((int) $existing['id'], $payload);
                $summary['updated']++;
                continue;
            }

            $this->employeeRepository->create($payload);
            $summary['created']++;
        }

        return $summary;
    }

    private function loadRows(string $filePath): array
    {
        try {
            $readerType = IOFactory::identify($filePath);
        } catch (\Throwable) {
            $readerType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        }

        $readerType = strtolower((string) $readerType);

        if ($readerType === 'csv') {
            $reader = new Csv();
            $reader->setDelimiter($this->detectDelimiter($filePath));
            $encoding = $this->detectEncoding($filePath);

            if (method_exists($reader, 'setInputEncoding')) {
                $reader->setInputEncoding($encoding);
            }

            $spreadsheet = $reader->load($filePath);
        } elseif (in_array($readerType, ['xlsx', 'xls', 'xl'], true)) {
            try {
                $spreadsheet = IOFactory::load($filePath);
            } catch (\Throwable) {
                throw new RuntimeException('No se pudo leer el archivo. Verifica que el CSV, XLS o XLSX no esté corrupto.');
            }
        } else {
            throw new RuntimeException('Formato no soportado. Use CSV, XLS o XLSX.');
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, false, false, false);

        return $this->normalizeRowsEncoding($rows);
    }

    private function detectDelimiter(string $filePath): string
    {
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            return ',';
        }

        $line = fgets($handle) ?: '';
        fclose($handle);

        $semicolons = substr_count($line, ';');
        $commas = substr_count($line, ',');

        return $semicolons > $commas ? ';' : ',';
    }

    private function detectEncoding(string $filePath): string
    {
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            return 'UTF-8';
        }

        $sample = fread($handle, 4096) ?: '';
        fclose($handle);

        $encoding = mb_detect_encoding($sample, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);

        return $encoding !== false ? $encoding : 'UTF-8';
    }

    private function normalizeRowsEncoding(array $rows): array
    {
        return array_map(
            fn(array $row): array => array_map([$this, 'normalizeCellEncoding'], $row),
            $rows
        );
    }

    private function normalizeCellEncoding(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @mb_convert_encoding($value, 'UTF-8', ['Windows-1252', 'ISO-8859-1', 'UTF-8']);

        return $converted !== false ? $converted : $value;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(
            static function ($header): string {
                $value = strtolower(trim((string) $header));
                $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
                $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? $value;

                return trim($value, '_');
            },
            $headers
        );
    }

    private function rowToAssoc(array $headers, array $row): array
    {
        $assoc = [];

        foreach ($headers as $index => $header) {
            $assoc[$header] = $row[$index] ?? null;
        }

        return $assoc;
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function resolveGroupId(string $value): ?int
    {
        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            $group = $this->groupRepository->findById((int) $value);

            return $group ? (int) $group['id'] : null;
        }

        $group = $this->groupRepository->findByNameOrSlug($value);

        return $group ? (int) $group['id'] : null;
    }

    private function parseBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'y', 'si', 'sí', 'activo', 'active'], true);
    }
}
