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
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class EmployeeAdminController extends Controller
{
    public function index(Request $request): View
    {
        $employees = Employee::query()
            ->with(['office:id,name', 'division:id,name', 'position:id,name,level', 'supervisor:id,name', 'manager:id,name', 'user:id'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->input('search').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('nik', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($request->filled('office_id'), fn ($q) => $q->where('office_id', $request->integer('office_id')))
            ->when($request->filled('division_id'), fn ($q) => $q->where('division_id', $request->integer('division_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.employees.index', [
            'employees' => $employees,
            'offices' => Office::orderBy('name')->get(['id', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'office_id', 'division_id']),
        ]);
    }

    public function create(): View
    {
        return view('admin.employees.form', array_merge(
            ['employee' => new Employee],
            $this->masterOptions(new Employee),
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        Employee::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'nik' => $data['nik'] ?? null,
            'phone' => $data['phone'] ?? null,
            'office_id' => $data['office_id'] ?? null,
            'division_id' => $data['division_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'direct_supervisor_id' => $data['direct_supervisor_id'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(Employee $employee): View
    {
        return view('admin.employees.form', array_merge(
            ['employee' => $employee->load('user')],
            $this->masterOptions($employee),
        ));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $data = $request->validate($this->rules($employee->id));

        $employee->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'nik' => $data['nik'] ?? null,
            'phone' => $data['phone'] ?? null,
            'office_id' => $data['office_id'] ?? null,
            'division_id' => $data['division_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'direct_supervisor_id' => $data['direct_supervisor_id'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        DB::transaction(function () use ($employee) {
            // Lepas kaitan user tapi jangan hapus akun user
            $employee->update(['user_id' => null]);
            $employee->delete();
        });

        return redirect()->route('admin.employees.index')
            ->with('success', 'Pegawai berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?int $employeeId = null): array
    {
        $uniqueEmail = 'unique:employees,email'.($employeeId ? ",{$employeeId}" : '');
        $uniqueNik = 'unique:employees,nik'.($employeeId ? ",{$employeeId}" : '');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', $uniqueEmail],
            'nik' => ['nullable', 'string', 'max:30', $uniqueNik],
            'phone' => ['nullable', 'string', 'max:30'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'direct_supervisor_id' => ['nullable', 'integer', 'exists:employees,id'],
            'manager_id' => ['nullable', 'integer', 'different:direct_supervisor_id', 'exists:employees,id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function masterOptions(Employee $employee): array
    {
        return [
            'offices' => Office::orderBy('name')->get(['id', 'name', 'code', 'type', 'branch_code']),
            'divisions' => Division::orderByRaw('COALESCE(parent_id, id)')->orderBy('parent_id')->orderBy('name')->get(['id', 'name', 'parent_id']),
            'positions' => Position::orderBy('level')->orderBy('name')->get(['id', 'name', 'level']),
            'supervisors' => Employee::with('office:id,code,type,branch_code')->when(
                $employee->exists,
                fn ($q) => $q->whereKeyNot($employee->id),
            )->orderBy('name')->get(['id', 'name', 'nik', 'office_id', 'division_id']),
            'managers' => Employee::with('office:id,code,type,branch_code')->when(
                $employee->exists,
                fn ($q) => $q->whereKeyNot($employee->id),
            )->orderBy('name')->get(['id', 'name', 'nik', 'office_id', 'division_id']),
        ];
    }
}
