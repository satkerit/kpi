<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\HumanResource\Models\Division;
use App\Domains\HumanResource\Models\Employee;
use Illuminate\Validation\Rule;

final class DivisionController extends MasterDataController
{
    protected string $viewDir = 'admin.divisions';

    protected string $moduleLabel = 'Divisi';

    protected string $routeName = 'admin.divisions';

    /** @var class-string<Division> */
    protected string $modelClass = Division::class;

    /**
     * @return array<string, mixed>
     */
    protected function rules(?int $recordId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:divisions,code'.($recordId ? ",{$recordId}" : '')],
            'parent_id' => ['nullable', 'integer', 'exists:divisions,id', Rule::notIn($recordId ? [$recordId] : [])],
            'head_id' => ['nullable', 'integer', 'exists:employees,id'],
            'is_active' => ['boolean'],
        ];
    }

    protected function indexQuery()
    {
        return Division::query()
            ->withCount('employees')
            ->with(['head:id,name', 'parent:id,name'])
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
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'nik']),
            'divisions' => Division::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }
}
