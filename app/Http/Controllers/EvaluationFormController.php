<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\Evaluation\Models\KpiScore;
use App\Domains\Evaluation\Strategies\StrategyFactory;
use App\Domains\MasterKpi\Models\KpiSubcriteria;
use App\Domains\MasterKpi\Models\RatingScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class EvaluationFormController extends Controller
{
    /**
     * Tampilkan lembar form penilaian berdasarkan tipe penilai (P1/P2/P3/SELF).
     * Akses dibatasi: hanya evaluator assignment yang bersangkutan.
     */
    public function show(Request $request, KpiAssignment $assignment): View|RedirectResponse
    {
        $me = $request->attributes->get('kpi_employee');

        if (! $me || $me->id !== $assignment->evaluator_id) {
            return redirect()->route('questionnaire.start')
                ->with('error', 'Sesi tidak valid atau Anda tidak memiliki akses ke form ini.');
        }

        $assignment->load(['evaluatee.office', 'evaluatee.division', 'evaluatee.position', 'period', 'scores']);
        $strategy = StrategyFactory::make($assignment->evaluator_type);
        $subcriteriaList = $strategy->validSubcriteria();
        $maxScore = $this->maxScoreFor($assignment->period_id);
        $allowNotObserved = (bool) ($assignment->period?->setting('allow_not_observed') ?? true);
        $scales = RatingScale::query()
            ->where('period_id', $assignment->period_id)
            ->orderBy('min_value')
            ->get();

        return view('evaluations.form', compact('assignment', 'subcriteriaList', 'maxScore', 'scales', 'allowNotObserved'));
    }

    /**
     * Batas atas skala penilaian periode (dinamis dari rating_scales, fallback 10).
     */
    private function maxScoreFor(int $periodId): float
    {
        return (float) (RatingScale::query()->where('period_id', $periodId)->max('max_value') ?: 10);
    }

    /**
     * Simpan nilai input subkriteria dan hitung weighted score.
     * Mendukung opsi "Tidak Diamati" (not observed) dan komentar kualitatif per subkriteria.
     */
    public function submit(Request $request, KpiAssignment $assignment): RedirectResponse
    {
        // Validasi kepemilikan: hanya evaluator assignment yang bisa submit.
        $me = $request->attributes->get('kpi_employee');

        if (! $me || $me->id !== $assignment->evaluator_id) {
            return redirect()->route('questionnaire.start')
                ->with('error', 'Sesi tidak valid atau Anda tidak memiliki akses ke form ini.');
        }

        $maxScore = $this->maxScoreFor($assignment->period_id);
        $allowNotObserved = (bool) ($assignment->period?->setting('allow_not_observed') ?? true);

        $validated = $request->validate([
            'not_observed' => ['sometimes', 'array'],
            'not_observed.*' => ['boolean'],
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:0', "max:{$maxScore}"],
            'comments' => ['sometimes', 'array'],
            'comments.*' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($validated['scores'] as $subcriteriaId => $rawScore) {
            $isNotObserved = $allowNotObserved && ! empty($validated['not_observed'][$subcriteriaId]);

            if (! $isNotObserved && $rawScore === null) {
                return back()->withErrors(["scores.{$subcriteriaId}" => 'Nilai wajib diisi atau tandai "Tidak Diamati".'])->withInput();
            }
        }

        DB::transaction(function () use ($validated, $assignment, $allowNotObserved) {
            foreach ($validated['scores'] as $subcriteriaId => $rawScore) {
                $subcriteria = KpiSubcriteria::query()->findOrFail($subcriteriaId);
                $isNotObserved = $allowNotObserved && ! empty($validated['not_observed'][$subcriteriaId]);

                // "Tidak Diamati": raw_score null → dikeluarkan dari pembobotan (normalisasi bobot di service).
                $weightedScore = $isNotObserved || $rawScore === null
                    ? 0
                    : round(((float) $rawScore * (float) $subcriteria->weight) / 100.0, 2);

                KpiScore::query()->updateOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'subcriteria_id' => $subcriteriaId,
                    ],
                    [
                        'raw_score' => $isNotObserved ? null : $rawScore,
                        'weighted_score' => $weightedScore,
                        'comment' => $validated['comments'][$subcriteriaId] ?? null,
                    ]
                );
            }

            $assignment->update(['status' => 'submitted']);
        });

        return redirect()->route('questionnaire.form')->with('success', 'Form penilaian berhasil disubmit.');
    }
}
