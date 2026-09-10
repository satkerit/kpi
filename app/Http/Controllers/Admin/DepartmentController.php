<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\HumanResource\Models\Department;
use App\Domains\HumanResource\Models\Division;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\HumanResource\Models\Office;
use Illuminate\Validation\Rule;

final class DepartmentController extends MasterDataController
{
    protected string $viewDir = 'admin.departments';

    protected string $moduleLabel = 'Bagian';

    protected string $routeName = 'admin.departments';

    /** @var class-string<Department> */
    protected string $modelClass = Department::class;

    /**
     * @return array<string, mixed>
     */
    protected function rules(?int $recordId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:departments,code'.($recordId ? ",{$recordId}" : '')],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'parent_id' => ['nullable', 'integer', 'exists:departments,id', Rule::notIn($recordId ? [$recordId] : [])],
            'category' => ['nullable', 'string', 'in:operasional,bisnis'],
            'description' => ['nullable', 'string', 'max:500'],
            'head_id' => ['nullable', 'integer', 'exists:employees,id'],
            'is_active' => ['boolean'],
        ];
    }

    protected function indexQuery()
    {
        return Department::query()
            ->withCount('employees')
            ->with(['division:id,name', 'office:id,name,type', 'parent:id,name'])
            ->orderByRaw('COALESCE(parent_id, id)')
            ->orderBy('parent_id')
            ->orderBy('name');
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'offices' => Office::orderBy('type')->orderBy('name')->get(['id', 'name', 'type']),
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'nik']),
            'parentDepartments' => Department::whereNull('parent_id')->orderBy('name')->get(['id', 'name']),
        ];
    }
}
