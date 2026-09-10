<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Evaluation\Models\EvaluationRule;
use App\Domains\Evaluation\Services\RuleBasedAssignmentService;
use App\Domains\HumanResource\Models\Division;
use App\Domains\HumanResource\Models\Office;
use App\Domains\HumanResource\Models\Position;
use App\Domains\MasterKpi\Models\KpiPeriod;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EvaluationRuleController extends Controller
{
    public function index(): View
    {
        $records = EvaluationRule::query()
            ->with(['evaluatorPosition', 'evaluateePosition', 'scopeOffice', 'scopeDivision'])
            ->orderBy('evaluator_type')
            ->orderBy('id')
            ->paginate(20);

        $periods = KpiPeriod::query()->orderByDesc('year')->orderByDesc('id')->get();

        return view('admin.evaluation_rules.index', compact('records', 'periods'));
    }

    public function create(): View
    {
        return view('admin.evaluation_rules.form', $this->viewData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        EvaluationRule::create($data);

        return redirect()->route('admin.evaluation-rules.index')
            ->with('success', 'Rule penilaian berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $record = EvaluationRule::findOrFail($id);

        return view('admin.evaluation_rules.form', array_merge($this->viewData(), ['record' => $record]));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = EvaluationRule::findOrFail($id);
        $data = $request->validate($this->rules());
        $record->update($data);

        return redirect()->route('admin.evaluation-rules.index')
            ->with('success', 'Rule penilaian berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        EvaluationRule::findOrFail($id)->delete();

        return redirect()->route('admin.evaluation-rules.index')
            ->with('success', 'Rule penilaian berhasil dihapus.');
    }

    /**
     * Generate KpiAssignment dari semua rule aktif untuk periode tertentu.
     */
    public function generate(Request $request, RuleBasedAssignmentService $service): RedirectResponse
    {
        $request->validate([
            'period_id' => ['required', 'integer', 'exists:kpi_periods,id'],
        ]);

        $result = $service->generateForPeriod((int) $request->period_id);

        return redirect()->route('admin.evaluation-rules.index')
            ->with('success', "Generate selesai: {$result['created']} penugasan baru, {$result['skipped']} dilewati (duplikat).");
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'evaluator_type' => ['required', 'string', 'in:P1,P2,P3,SELF'],
            'evaluator_position_id' => ['required', 'integer', 'exists:positions,id'],
            'evaluatee_position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'scope_office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'scope_division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(): array
    {
        return [
            'record' => new EvaluationRule,
            'positions' => Position::query()->where('is_active', true)->orderBy('level')->orderBy('name')->get(),
            'offices' => Office::query()->where('is_active', true)->orderBy('name')->get(),
            'divisions' => Division::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
