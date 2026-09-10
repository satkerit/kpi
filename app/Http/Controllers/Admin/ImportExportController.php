<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\HumanResource\Models\Department;
use App\Domains\HumanResource\Models\Division;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\HumanResource\Models\Office;
use App\Domains\HumanResource\Models\Position;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'divisions' => ['name', 'code', 'is_active'],
            'departments' => ['name', 'code', 'division_code', 'office_code', 'description', 'is_active'],
            'positions' => ['name', 'level', 'is_active'],
            'employees' => ['nik', 'name', 'email', 'password', 'office_code', 'division_code', 'department_code', 'position_code', 'phone'],
            default => abort(404),
        };

        $sample = match ($type) {
            'offices' => ['Kantor Kas Rungkut', 'KAS-RGT', 'kas', 'SBY', 'Jl. Rungkut No.1 Surabaya', '031-8760001', '1'],
            'divisions' => ['Teknologi Informasi', 'DTI', '1'],
            'departments' => ['Pengembangan Perangkat Lunak', 'PPL', 'DTI', 'PUSAT', 'Bagian dev software', '1'],
            'positions' => ['Manager', '5', '1'],
            'employees' => ['199001012025011001', 'Budi Santoso', 'budi@kpi360.co.id', 'password123', 'PUSAT', 'DTI', 'PPL', 'Manager', '081234567890'],
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
        Division::updateOrCreate(
            ['code' => trim($data['code'])],
            [
                'name' => trim($data['name']),
                'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ]
        );
    }

    private function importDepartment(array $data): void
    {
        $division = Division::where('code', trim($data['division_code'] ?? ''))->first();
        $office = Office::where('code', trim($data['office_code'] ?? ''))->first();

        Department::updateOrCreate(
            ['code' => trim($data['code'])],
            [
                'name' => trim($data['name']),
                'division_id' => $division?->id,
                'office_id' => $office?->id,
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
        $office = Office::where('code', trim($data['office_code'] ?? ''))->first();
        $division = Division::where('code', trim($data['division_code'] ?? ''))->first();
        $department = Department::where('code', trim($data['department_code'] ?? ''))->first();
        $position = Position::where('name', trim($data['position_code'] ?? ''))->first();

        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        // Cari atau buat akun user jika email tersedia
        $userId = null;
        if ($email !== '') {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => trim($data['name']),
                    'password' => Hash::make($password !== '' ? $password : 'password123'),
                ]
            );
            $userId = $user->id;
        }

        Employee::updateOrCreate(
            ['nik' => trim($data['nik'])],
            [
                'user_id' => $userId,
                'name' => trim($data['name']),
                'email' => $email !== '' ? $email : null,
                'office_id' => $office?->id,
                'division_id' => $division?->id,
                'department_id' => $department?->id,
                'position_id' => $position?->id,
                'phone' => trim($data['phone'] ?? ''),
            ]
        );
    }
}
