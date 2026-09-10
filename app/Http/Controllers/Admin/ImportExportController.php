<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\HumanResource\Models\Department;
use App\Domains\HumanResource\Models\Division;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\HumanResource\Models\Office;
use App\Domains\HumanResource\Models\Position;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ImportExportController extends Controller
{
    /**
     * Download CSV template for specific type.
     */
    public function template(string $type): StreamedResponse
    {
        $headers = match ($type) {
            'offices' => ['name', 'code', 'type', 'branch_code', 'address', 'phone', 'is_active'],
            'divisions' => ['name', 'code', 'parent_code', 'is_active'],
            'departments' => ['name', 'code', 'division_code', 'office_code', 'parent_code', 'category', 'description', 'is_active'],
            'positions' => ['name', 'level', 'is_active'],
            'employees' => ['nik', 'name', 'email', 'office_code', 'division_code', 'department_code', 'position_code', 'phone', 'is_active'],
            default => abort(404),
        };

        $sample = match ($type) {
            'offices' => ['Kantor Kas Rungkut', 'KAS-RGT', 'kas', 'SBY', 'Jl. Rungkut No.1 Surabaya', '031-8760001', '1'],
            'divisions' => ['Teknologi Informasi', 'DTI', '', '1'],
            'departments' => ['Pengembangan Perangkat Lunak', 'PPL', 'DTI', 'PUSAT', '', 'operasional', 'Bagian dev software', '1'],
            'positions' => ['Manager', '5', '1'],
            'employees' => ['199001012025011001', 'Budi Santoso', 'budi@kpi360.co.id', 'PUSAT', 'DTI', 'PPL', 'Manager', '081234567890', '1'],
            default => [],
        };

        $filename = "template_{$type}.csv";

        return response()->streamDownload(function () use ($headers, $sample) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, $sample);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Handle CSV import for specific type.
     */
    public function import(Request $request, string $type): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->with('error', 'Gagal membaca file yang diunggah.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);

            return back()->with('error', 'File CSV kosong.');
        }

        $imported = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            if ($data === false) {
                $errors++;

                continue;
            }

            try {
                match ($type) {
                    'offices' => $this->importOffice($data),
                    'divisions' => $this->importDivision($data),
                    'departments' => $this->importDepartment($data),
                    'positions' => $this->importPosition($data),
                    'employees' => $this->importEmployee($data),
                    default => throw new \InvalidArgumentException('Tipe import tidak valid.'),
                };
                $imported++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        $msg = "Berhasil mengimpor {$imported} data.";
        if ($errors > 0) {
            $msg .= " ({$errors} baris gagal/lewati).";
        }

        return back()->with('success', $msg);
    }

    private function importOffice(array $data): void
    {
        $type = trim($data['type'] ?? 'branch');
        $branchCode = trim($data['branch_code'] ?? '');

        if ($type === 'kas' && $branchCode === '') {
            throw new \InvalidArgumentException('Kantor Kas wajib memiliki kode cabang induk.');
        }

        Office::updateOrCreate(
            ['code' => strtoupper(trim($data['code']))],
            [
                'name' => trim($data['name']),
                'type' => $type,
                'branch_code' => $branchCode !== '' ? strtoupper($branchCode) : null,
                'address' => trim($data['address'] ?? ''),
                'phone' => trim($data['phone'] ?? ''),
                'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ]
        );
    }

    private function importDivision(array $data): void
    {
        $parent = Division::where('code', trim($data['parent_code'] ?? ''))->first();

        Division::updateOrCreate(
            ['code' => trim($data['code'])],
            [
                'name' => trim($data['name']),
                'parent_id' => $parent?->id,
                'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ]
        );
    }

    private function importDepartment(array $data): void
    {
        $division = Division::where('code', trim($data['division_code'] ?? ''))->first();
        $office = Office::where('code', trim($data['office_code'] ?? ''))->first();
        $parent = Department::where('code', trim($data['parent_code'] ?? ''))->first();

        Department::updateOrCreate(
            ['code' => trim($data['code'])],
            [
                'name' => trim($data['name']),
                'division_id' => $division?->id,
                'office_id' => $office?->id,
                'parent_id' => $parent?->id,
                'category' => trim($data['category'] ?? '') !== '' ? trim($data['category']) : null,
                'description' => trim($data['description'] ?? ''),
                'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ]
        );
    }

    private function importPosition(array $data): void
    {
        Position::updateOrCreate(
            ['name' => trim($data['name'])],
            [
                'level' => (int) ($data['level'] ?? 1),
                'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ]
        );
    }

    private function importEmployee(array $data): void
    {
        $nik = trim($data['nik'] ?? '');
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $officeCode = trim($data['office_code'] ?? '');
        $positionCode = trim($data['position_code'] ?? '');
        $isActiveRaw = $data['is_active'] ?? null;

        if ($nik === '' || $name === '' || $email === '' || $officeCode === '' || $positionCode === '' || $isActiveRaw === null || $isActiveRaw === '') {
            throw new \InvalidArgumentException('Kolom nik, name, email, office_code, position_code, dan is_active wajib diisi.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Format email [{$email}] tidak valid.");
        }

        $office = Office::where('code', strtoupper($officeCode))->orWhere('code', $officeCode)->first();
        if (! $office) {
            throw new \InvalidArgumentException("Kantor dengan kode [{$officeCode}] tidak ditemukan.");
        }

        $position = Position::where('name', $positionCode)->orWhere('id', $positionCode)->first();
        if (! $position) {
            throw new \InvalidArgumentException("Jabatan [{$positionCode}] tidak ditemukan.");
        }

        $division = Division::where('code', trim($data['division_code'] ?? ''))->first();
        $department = Department::where('code', trim($data['department_code'] ?? ''))->first();

        $existing = Employee::where('nik', $nik)->first();

        Employee::updateOrCreate(
            ['nik' => $nik],
            [
                'user_id' => $existing?->user_id,
                'name' => $name,
                'email' => $email,
                'office_id' => $office->id,
                'division_id' => $division?->id,
                'department_id' => $department?->id,
                'position_id' => $position->id,
                'phone' => trim($data['phone'] ?? ''),
                'is_active' => filter_var($isActiveRaw, FILTER_VALIDATE_BOOLEAN),
            ]
        );
    }
}
