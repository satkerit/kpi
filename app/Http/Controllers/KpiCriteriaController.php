<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\MasterKpi\Models\KpiCriteria;
use App\Domains\MasterKpi\Models\KpiSubcriteria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class KpiCriteriaController extends Controller
{
    public function index(): View
    {
        $criteria = KpiCriteria::query()
            ->with('subcriteria')
            ->withCount('subcriteria')
            ->get();

        return view('kpi.criteria.index', compact('criteria'));
    }

    public function storeSubcriteria(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'criteria_id' => ['required', 'integer', 'exists:kpi_criteria,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'evaluator_type' => ['required', 'in:P1,P2,P3,SELF'],
        ]);

        KpiSubcriteria::query()->create($validated);

        return back()->with('success', 'Subkriteria berhasil ditambahkan.');
    }

    public function updateSubcriteria(Request $request, KpiSubcriteria $subcriteria): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'evaluator_type' => ['required', 'in:P1,P2,P3,SELF'],
        ]);

        $subcriteria->update($validated);

        return back()->with('success', 'Subkriteria berhasil diperbarui.');
    }

    public function destroySubcriteria(KpiSubcriteria $subcriteria): RedirectResponse
    {
        if ($subcriteria->scores()->exists()) {
            return back()->withErrors(['subcriteria' => 'Subkriteria tidak dapat dihapus karena sudah memiliki nilai penilaian.']);
        }

        $subcriteria->delete();

        return back()->with('success', 'Subkriteria berhasil dihapus.');
    }
}
