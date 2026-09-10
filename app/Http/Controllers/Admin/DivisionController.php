<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\HumanResource\Models\Division;
use App\Domains\HumanResource\Models\Employee;

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
            'head_id' => ['nullable', 'integer', 'exists:employees,id'],
            'is_active' => ['boolean'],
        ];
    }

    protected function indexQuery()
    {
        return Division::query()->withCount('employees')->with('head:id,name')->orderBy('name');
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'nik']),
        ];
    }
}
