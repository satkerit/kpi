<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\HumanResource\Models\Office;

final class OfficeController extends MasterDataController
{
    protected string $viewDir = 'admin.offices';

    protected string $moduleLabel = 'Kantor';

    protected string $routeName = 'admin.offices';

    /** @var class-string<Office> */
    protected string $modelClass = Office::class;

    /**
     * @return array<string, mixed>
     */
    protected function rules(?int $recordId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:offices,code'.($recordId ? ",{$recordId}" : '')],
            'type' => ['required', 'string', 'in:head_office,branch,kpo,kas'],
            'branch_code' => ['nullable', 'string', 'max:20', 'required_if:type,kas'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ];
    }

    protected function indexQuery()
    {
        return Office::query()->withCount('employees')->orderBy('name');
    }
}
