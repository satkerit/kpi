<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Base controller CRUD untuk master data HR (Office, Division, Position, Department).
 * Subclass hanya perlu meng-override konfigurasi & rules().
 */
abstract class MasterDataController extends Controller
{
    /** @var string Nama folder view, mis. 'admin.offices' */
    protected string $viewDir = '';

    /** @var string Label modul untuk judul halaman */
    protected string $moduleLabel = '';

    /** @var string Prefix nama route (mis. 'admin.offices') */
    protected string $routeName = '';

    /** @var class-string<Model> */
    protected string $modelClass = '';

    /**
     * Aturan validasi; $recordId null saat create.
     *
     * @return array<string, mixed>
     */
    abstract protected function rules(?int $recordId = null): array;

    /**
     * Data pendukung untuk view (dropdown, dsb.).
     *
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [];
    }

    /**
     * Query listing (override untuk withCount, dst.).
     */
    protected function indexQuery()
    {
        return $this->modelClass::query()->orderBy('name');
    }

    public function index(): View
    {
        $records = $this->indexQuery()->paginate(15);

        return view("{$this->viewDir}.index", array_merge([
            'records' => $records,
            'moduleLabel' => $this->moduleLabel,
        ], $this->viewData()));
    }

    public function create(): View
    {
        return view("{$this->viewDir}.form", array_merge([
            'record' => new ($this->modelClass),
            'moduleLabel' => $this->moduleLabel,
        ], $this->viewData()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $this->modelClass::create($data);

        return redirect()->route("{$this->routeName}.index")
            ->with('success', "{$this->moduleLabel} berhasil ditambahkan.");
    }

    public function edit(int $id): View
    {
        $record = $this->modelClass::findOrFail($id);

        return view("{$this->viewDir}.form", array_merge([
            'record' => $record,
            'moduleLabel' => $this->moduleLabel,
        ], $this->viewData()));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = $this->modelClass::findOrFail($id);
        $data = $request->validate($this->rules($id));
        $record->update($data);

        return redirect()->route("{$this->routeName}.index")
            ->with('success', "{$this->moduleLabel} berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $record = $this->modelClass::findOrFail($id);
        $record->delete();

        return redirect()->route("{$this->routeName}.index")
            ->with('success', "{$this->moduleLabel} berhasil dihapus.");
    }
}
