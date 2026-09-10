<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\HumanResource\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmployeeController extends Controller
{
    /**
     * List semua pegawai.
     */
    public function index(): JsonResponse
    {
        $employees = Employee::with(['office', 'division', 'position'])->get();

        return response()->json($employees);
    }

    /**
     * Buat pegawai baru.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:employees,email'],
            'nik' => ['required', 'string', 'unique:employees,nik'],
            'phone' => ['nullable', 'string', 'max:30'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'direct_supervisor_id' => ['nullable', 'integer', 'exists:employees,id'],
        ]);

        $employee = Employee::create($data);

        return response()->json($employee, 201);
    }

    /**
     * Tampilkan detail pegawai.
     */
    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['office', 'division', 'position', 'supervisor', 'subordinates']);

        return response()->json($employee);
    }

    /**
     * Update pegawai.
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:employees,email,'.$employee->id],
            'nik' => ['sometimes', 'string', 'unique:employees,nik,'.$employee->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'direct_supervisor_id' => ['nullable', 'integer', 'exists:employees,id'],
        ]);

        $employee->update($data);

        return response()->json($employee);
    }

    /**
     * Hapus pegawai.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(['message' => 'Pegawai dihapus.']);
    }
}
