<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\AccessControl\Models\Role;
use App\Domains\HumanResource\Models\Department;
use App\Domains\HumanResource\Models\Division;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\HumanResource\Models\Office;
use App\Domains\HumanResource\Models\Position;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class EmployeeAdminController extends Controller
{
    public function index(Request $request): View
    {
        $employees = Employee::query()
            ->with(['office:id,name', 'division:id,name', 'department:id,name', 'position:id,name,level', 'supervisor:id,name', 'user:id,email'])
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $search = $request->string('search');
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($request->filled('office_id'), fn ($q) => $q->where('office_id', $request->integer('office_id')))
            ->when($request->filled('division_id'), fn ($q) => $q->where('division_id', $request->integer('division_id')))
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->integer('department_id')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.employees.index', [
            'employees' => $employees,
            'offices' => Office::orderBy('name')->get(['id', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'office_id', 'division_id', 'department_id']),
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

        DB::transaction(function () use ($data) {
            // Buat akun user jika email + password disediakan
            $userId = null;
            if (! empty($data['email']) && ! empty($data['password'])) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt($data['password']),
                ]);
                $userId = $user->id;

                if (! empty($data['roles'])) {
                    $user->syncRoles($data['roles']);
                }
            }

            Employee::create([
                'user_id' => $userId,
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'nik' => $data['nik'] ?? null,
                'phone' => $data['phone'] ?? null,
                'office_id' => $data['office_id'] ?? null,
                'division_id' => $data['division_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'position_id' => $data['position_id'] ?? null,
                'direct_supervisor_id' => $data['direct_supervisor_id'] ?? null,
                'is_active' => true,
            ]);
        });

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

        DB::transaction(function () use ($data, $employee) {
            // Update data employee
            $employee->update([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'nik' => $data['nik'] ?? null,
                'phone' => $data['phone'] ?? null,
                'office_id' => $data['office_id'] ?? null,
                'division_id' => $data['division_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'position_id' => $data['position_id'] ?? null,
                'direct_supervisor_id' => $data['direct_supervisor_id'] ?? null,
            ]);

            // Sinkronisasi akun user jika ada
            if ($employee->user) {
                $employee->user->update(['name' => $data['name']]);

                if (! empty($data['password'])) {
                    $employee->user->update(['password' => bcrypt($data['password'])]);
                }

                if (isset($data['roles'])) {
                    $employee->user->syncRoles($data['roles']);
                }
            } elseif (! empty($data['email']) && ! empty($data['password'])) {
                // Buat akun user baru dan kaitkan
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt($data['password']),
                ]);

                if (! empty($data['roles'])) {
                    $user->syncRoles($data['roles']);
                }

                $employee->update(['user_id' => $user->id]);
            }
        });

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
            'password' => [$employeeId ? 'nullable' : 'nullable', 'string', 'min:8'],
            'nik' => ['nullable', 'string', 'max:30', $uniqueNik],
            'phone' => ['nullable', 'string', 'max:30'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'direct_supervisor_id' => ['nullable', 'integer', 'exists:employees,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,slug'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function masterOptions(Employee $employee): array
    {
        return [
            'offices' => Office::orderBy('name')->get(['id', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'positions' => Position::orderBy('level')->orderBy('name')->get(['id', 'name', 'level']),
            'supervisors' => Employee::when(
                $employee->exists,
                fn ($q) => $q->whereKeyNot($employee->id),
            )->orderBy('name')->get(['id', 'name', 'nik']),
            'roles' => Role::orderBy('name')->get(['id', 'name', 'slug']),
        ];
    }
}
