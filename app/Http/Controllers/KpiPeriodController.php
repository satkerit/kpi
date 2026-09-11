<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\MasterKpi\Models\KpiPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class KpiPeriodController extends Controller
{
    public function index(): View
    {
        $periods = KpiPeriod::query()->orderByDesc('year')->orderByDesc('id')->paginate(15);

        return view('kpi.periods.index', compact('periods'));
    }

    public function create(): View
    {
        return view('kpi.periods.form', ['period' => new KpiPeriod]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        KpiPeriod::query()->create($validated);

        return redirect()->route('periods.index')->with('success', 'Periode KPI berhasil dibuat.');
    }

    public function edit(KpiPeriod $period): View
    {
        return view('kpi.periods.form', compact('period'));
    }

    public function update(Request $request, KpiPeriod $period): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $period->update($validated);

        return redirect()->route('periods.index')->with('success', 'Periode KPI berhasil diperbarui.');
    }

    public function destroy(KpiPeriod $period): RedirectResponse
    {
        if ($period->assignments()->exists()) {
            return back()->withErrors(['period' => 'Periode tidak dapat dihapus karena sudah memiliki penugasan penilaian.']);
        }

        $period->delete();

        return redirect()->route('periods.index')->with('success', 'Periode KPI berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:draft,active,closed'],
        ];
    }
}
