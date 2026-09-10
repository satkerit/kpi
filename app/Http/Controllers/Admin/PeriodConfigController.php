<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\MasterKpi\Models\KpiEvaluatorWeight;
use App\Domains\MasterKpi\Models\KpiPeriod;
use App\Domains\MasterKpi\Models\RatingScale;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PeriodConfigController extends Controller
{
    /**
     * Halaman konfigurasi bobot evaluator & skala penilaian per periode.
     */
    public function show(KpiPeriod $period): View
    {
        $weights = KpiEvaluatorWeight::query()
            ->where('period_id', $period->id)
            ->orderBy('evaluator_type')
            ->get()
            ->keyBy('evaluator_type');

        $scales = RatingScale::query()
            ->where('period_id', $period->id)
            ->orderBy('sort_order')
            ->orderByDesc('min_value')
            ->get();

        return view('admin.period_config.show', compact('period', 'weights', 'scales'));
    }

    /**
     * Simpan bobot evaluator (P1/P2/P3/SELF) untuk periode.
     */
    public function saveWeights(Request $request, KpiPeriod $period): RedirectResponse
    {
        $validated = $request->validate([
            'weights' => ['required', 'array'],
            'weights.P1' => ['required', 'numeric', 'min:0', 'max:100'],
            'weights.P2' => ['required', 'numeric', 'min:0', 'max:100'],
            'weights.P3' => ['required', 'numeric', 'min:0', 'max:100'],
            'weights.SELF' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($validated['weights'] as $type => $weight) {
            KpiEvaluatorWeight::query()->updateOrCreate(
                ['period_id' => $period->id, 'evaluator_type' => $type],
                ['weight' => $weight]
            );
        }

        return back()->with('success', 'Bobot evaluator berhasil disimpan.');
    }

    /**
     * Simpan skala penilaian (rating scales) untuk periode.
     * Request: scales[]=>{label, min_value, max_value, predicate, color, sort_order}
     */
    public function saveScales(Request $request, KpiPeriod $period): RedirectResponse
    {
        $validated = $request->validate([
            'scales' => ['required', 'array', 'min:1'],
            'scales.*.label' => ['required', 'string', 'max:100'],
            'scales.*.min_value' => ['required', 'numeric', 'min:0'],
            'scales.*.max_value' => ['required', 'numeric', 'gt:scales.*.min_value'],
            'scales.*.predicate' => ['required', 'string', 'max:100'],
            'scales.*.color' => ['nullable', 'string', 'max:30'],
            'scales.*.sort_order' => ['nullable', 'integer'],
        ]);

        // Hapus semua skala lama periode ini, lalu insert ulang
        RatingScale::query()->where('period_id', $period->id)->delete();

        foreach ($validated['scales'] as $i => $row) {
            RatingScale::query()->create([
                'period_id' => $period->id,
                'label' => $row['label'],
                'min_value' => $row['min_value'],
                'max_value' => $row['max_value'],
                'predicate' => $row['predicate'],
                'color' => $row['color'] ?? null,
                'sort_order' => $row['sort_order'] ?? $i,
            ]);
        }

        return back()->with('success', 'Skala penilaian berhasil disimpan.');
    }

    /**
     * Hapus satu skala penilaian.
     */
    public function destroyScale(KpiPeriod $period, RatingScale $scale): RedirectResponse
    {
        abort_if($scale->period_id !== $period->id, 404);
        $scale->delete();

        return back()->with('success', 'Skala penilaian dihapus.');
    }

    /**
     * Simpan pengaturan 360 periode (dinamis, JSON):
     * ambang anonim per grup, batas penilai P2, izin "Tidak Diamati".
     */
    public function saveSettings(Request $request, KpiPeriod $period): RedirectResponse
    {
        $validated = $request->validate([
            'min_raters_per_group' => ['required', 'integer', 'min:1', 'max:50'],
            'max_peers_per_evaluatee' => ['required', 'integer', 'min:0', 'max:100'],
            'allow_not_observed' => ['sometimes', 'boolean'],
        ]);

        $period->update([
            'settings' => [
                'min_raters_per_group' => (int) $validated['min_raters_per_group'],
                'max_peers_per_evaluatee' => (int) $validated['max_peers_per_evaluatee'],
                'allow_not_observed' => (bool) $request->boolean('allow_not_observed'),
            ],
        ]);

        return back()->with('success', 'Pengaturan penilaian berhasil disimpan.');
    }
}
