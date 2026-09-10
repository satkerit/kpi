<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\HumanResource\Models\Position;

final class PositionController extends MasterDataController
{
    protected string $viewDir = 'admin.positions';

    protected string $moduleLabel = 'Jabatan';

    protected string $routeName = 'admin.positions';

    /** @var class-string<Position> */
    protected string $modelClass = Position::class;

    /**
     * @return array<string, mixed>
     */
    protected function rules(?int $recordId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1', 'max:20'],
            'is_active' => ['boolean'],
        ];
    }

    protected function indexQuery()
    {
        return Position::query()->withCount('employees')->orderBy('level')->orderBy('name');
    }
}
