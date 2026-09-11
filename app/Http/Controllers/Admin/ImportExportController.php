<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

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
            'positions' => ['name', 'level', 'is_active'],
            'employees' => ['nik', 'name', 'email', 'office_code', 'division_code', 'position_code', 'direct_supervisor_nik', 'manager_nik', 'phone', 'is_active'],
            default => abort(404),
        };

        $sample = match ($type) {
            'offices' => ['Kantor Kas Rungkut', 'KAS-RGT', 'kas', 'SBY', 'Jl. Rungkut No.1 Surabaya', '031-8760001', '1'],
            'divisions' => ['Teknologi Informasi', 'DTI', '', '1'],
            'positions' => ['Manager', '5', '1'],
            'employees' => ['199001012025011001', 'Budi Santoso', 'budi@kpi360.co.id', 'PUSAT', 'DTI', 'Manager', '', '', '081234567890', '1'],
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

        // Auto-deteksi delimiter: Excel Indonesia menyimpan CSV dengan ';' bukan ','.
        // Tanpa ini seluruh baris terbaca satu kolom sehingga header tidak match dan
        // nilai office/divisi hilang (fallback ke kantor default).
        $firstLine = fgets($handle);
        rewind($handle);
        $delim = substr_count((string) $firstLine, ';') > substr_count((string) $firstLine, ',') ? ';' : ',';

        $header = fgetcsv($handle, 0, $delim);
        if ($header === false || count($header) === 0) {
            fclose($handle);

            return back()->with('error', 'File CSV kosong atau header tidak valid.');
        }

        // Clean UTF-8 BOM from header if present
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
            $header[0] = trim($header[0], "\xEF\xBB\xBF");
        }

        // Normalisasi header: lowercase + spasi/strip jadi underscore + alias.
        // Tanpa ini, header seperti "Office_Code" tidak terbaca sebagai office_code,
        // lookup gagal, lalu fallback diam-diam menimpa office_id/division_id yang salah.
        $header = array_map(fn ($h) => strtolower(str_replace([' ', '-'], '_', trim((string) $h))), $header);
        $aliases = [
            'supervisor' => 'direct_supervisor_nik',
            'supervisor_nik' => 'direct_supervisor_nik',
            'supervisors' => 'direct_supervisor_nik',
            'atasan' => 'direct_supervisor_nik',
            'atasan_nik' => 'direct_supervisor_nik',
            'direct_supervisor' => 'direct_supervisor_nik',
            'manager' => 'manager_nik',
            'manajer' => 'manager_nik',
            'office' => 'office_code',
            'kantor' => 'office_code',
            'kode_kantor' => 'office_code',
            'officecode' => 'office_code',
            'kodekantor' => 'office_code',
            'division' => 'division_code',
            'divisi' => 'division_code',
            'kode_divisi' => 'division_code',
            'divisioncode' => 'division_code',
            'kodedivisi' => 'division_code',
            'position' => 'position_code',
            'jabatan' => 'position_code',
            'kode_jabatan' => 'position_code',
            'positioncode' => 'position_code',
            'kodejabatan' => 'position_code',
            'nama' => 'name',
            'nama_lengkap' => 'name',
            'nip' => 'nik',
            'telp' => 'phone',
            'telepon' => 'phone',
            'no_hp' => 'phone',
            'hp' => 'phone',
            'nohp' => 'phone',
            'active' => 'is_active',
            'status' => 'is_active',
            'aktif' => 'is_active',
            'isactive' => 'is_active',
        ];
        foreach ($header as $i => $h) {
            $key = strtolower($h);
            if (isset($aliases[$key])) {
                $header[$i] = $aliases[$key];
            }
        }

        $imported = 0;
        $errors = 0;
        $firstError = null;

        while (($row = fgetcsv($handle, 0, $delim)) !== false) {
            // Ignore empty rows
            if (empty(array_filter($row, fn ($val) => trim((string) $val) !== ''))) {
                continue;
            }

            if (count($header) !== count($row)) {
                // Adjust row length if needed
                if (count($row) < count($header)) {
                    $row = array_pad($row, count($header), '');
                } else {
                    $row = array_slice($row, 0, count($header));
                }
            }

            $data = array_combine($header, $row);
            if ($data === false) {
                $errors++;

                continue;
            }

            try {
                match ($type) {
                    'offices' => $this->importOffice($data),
                    'divisions' => $this->importDivision($data),
                    'positions' => $this->importPosition($data),
                    'employees' => $this->importEmployee($data),
                    default => throw new \InvalidArgumentException('Tipe import tidak valid.'),
                };
                $imported++;
            } catch (\Exception $e) {
                $errors++;
                if ($firstError === null) {
                    $firstError = $e->getMessage();
                }
            }
        }

        fclose($handle);

        $msg = "Berhasil mengimpor {$imported} data.";
        if ($errors > 0) {
            $msg .= " ({$errors} baris gagal/lewati).";
            if ($firstError) {
                $msg .= " Detail error pertama: {$firstError}";
            }
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
        $divisionCode = trim($data['division_code'] ?? '');
        $supervisorNik = trim($data['direct_supervisor_nik'] ?? '');
        $managerNik = trim($data['manager_nik'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if ($nik === '' || $name === '') {
            throw new \InvalidArgumentException('Kolom NIK dan Nama wajib diisi.');
        }

        $existing = Employee::where('nik', $nik)->first();

        // Kantor: wajib untuk pegawai baru; untuk update, kolom kosong = pertahankan lama.
        // Kode diisi tapi tidak ketemu = error (jangan fallback diam-diam ke kantor lain).
        $office = null;
        if ($officeCode !== '') {
            $office = ctype_digit($officeCode)
                ? Office::find((int) $officeCode)
                : Office::whereRaw('UPPER(code) = ?', [strtoupper($officeCode)])->first()
                ?? Office::where('name', $officeCode)->first();
        }
        if (! $office) {
            if ($officeCode === '' && $existing) {
                $office = $existing->office;
            } elseif ($officeCode === '') {
                throw new \InvalidArgumentException("Pegawai baru [{$nik}] wajib mengisi office_code.");
            } else {
                throw new \InvalidArgumentException("Kantor [{$officeCode}] tidak ditemukan.");
            }
        }

        // Jabatan: sama — kolom kosong = pertahankan lama; kode tak dikenal = buat baru.
        $position = null;
        if ($positionCode !== '') {
            $position = ctype_digit($positionCode)
                ? Position::find((int) $positionCode)
                : Position::where('name', $positionCode)->first()
                ?? Position::where('name', 'LIKE', "%{$positionCode}%")->first();
            $position ??= Position::create(['name' => $positionCode, 'level' => 1, 'is_active' => true]);
        }
        $position ??= $existing?->position ?? Position::first();
        if (! $position) {
            throw new \InvalidArgumentException("Jabatan [{$positionCode}] tidak ditemukan.");
        }

        // Divisi: opsional; kolom kosong = pertahankan lama; kode tak dikenal = error.
        $division = null;
        if ($divisionCode !== '') {
            $division = ctype_digit($divisionCode)
                ? Division::find((int) $divisionCode)
                : Division::whereRaw('UPPER(code) = ?', [strtoupper($divisionCode)])->first()
                ?? Division::where('name', $divisionCode)->first();
            if (! $division) {
                throw new \InvalidArgumentException("Divisi [{$divisionCode}] tidak ditemukan.");
            }
        } else {
            $division = $existing?->division;
        }

        // Atasan Langsung & Manajer: opsional; kolom kosong/tak dikenal tidak menimpa data lama.
        $supervisor = $supervisorNik !== '' ? Employee::where('nik', $supervisorNik)->first() : null;
        $manager = $managerNik !== '' ? Employee::where('nik', $managerNik)->first() : null;
        if ($supervisorNik !== '' && ! $supervisor) {
            throw new \InvalidArgumentException("Atasan [{$supervisorNik}] tidak ditemukan.");
        }
        if ($managerNik !== '' && ! $manager) {
            throw new \InvalidArgumentException("Manajer [{$managerNik}] tidak ditemukan.");
        }

        $isActive = $existing?->is_active ?? true;
        if (isset($data['is_active']) && trim((string) $data['is_active']) !== '') {
            $parsed = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($parsed !== null) {
                $isActive = $parsed;
            }
        }

        $payload = [
            'user_id' => $existing?->user_id,
            'name' => $name,
            'email' => $email !== '' ? $email : $existing?->email,
            'office_id' => $office->id,
            'division_id' => $division?->id,
            'position_id' => $position->id,
            'phone' => $phone !== '' ? $phone : $existing?->phone,
            'is_active' => $isActive,
        ];

        if ($supervisor) {
            $payload['direct_supervisor_id'] = $supervisor->id;
        }
        if ($manager) {
            $payload['manager_id'] = $manager->id;
        }

        Employee::updateOrCreate(['nik' => $nik], $payload);
    }
}
