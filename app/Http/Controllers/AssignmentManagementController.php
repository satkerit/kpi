<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\Evaluation\Services\AssignmentGeneratorService;
use App\Domains\Evaluation\Strategies\StrategyFactory;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\MasterKpi\Models\KpiPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AssignmentManagementController extends Controller
{
    public function __construct(
        private readonly AssignmentGeneratorService $generator,
    ) {}

    /**
     * Tugaskan penilai untuk seorang pegawai dengan validasi relasi 1 tingkat.
     */
    public function assign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_id' => ['required', 'integer', 'exists:kpi_periods,id'],
            'evaluatee_id' => ['required', 'integer', 'exists:employees,id'],
            'evaluator_id' => ['required', 'integer', 'exists:employees,id', 'different:evaluatee_id'],
            'evaluator_type' => ['required', 'string', 'in:P1,P2,P3'],
        ]);

        $strategy = StrategyFactory::make($validated['evaluator_type']);
        $isValidRelationship = $strategy->validateRelationship(
            (int) $validated['evaluator_id'],
            (int) $validated['evaluatee_id']
        );

        if (! $isValidRelationship) {
            return back()->withErrors([
                'evaluator_id' => "Hubungan antara penilai dan yang dinilai tidak memenuhi kriteria {$validated['evaluator_type']} (maksimal 1 tingkat langsung).",
            ]);
        }

        KpiAssignment::query()->firstOrCreate([
            'period_id' => $validated['period_id'],
            'evaluatee_id' => $validated['evaluatee_id'],
            'evaluator_id' => $validated['evaluator_id'],
            'evaluator_type' => $validated['evaluator_type'],
        ], [
            'status' => 'pending',
        ]);

        return back()->with('success', "Penilai {$validated['evaluator_type']} berhasil ditugaskan.");
    }

    /**
     * Daftar penugasan per periode + UI generate & assign manual.
     */
    public function index(Request $request): View
    {
        $periods = KpiPeriod::query()->orderByDesc('year')->orderByDesc('id')->get();
        $selectedPeriodId = $request->query('period_id', $periods->first()?->id);

        $assignments = KpiAssignment::query()
            ->with(['evaluator.office', 'evaluator.division', 'evaluatee.office', 'evaluatee.division', 'period'])
            ->where('status', 'submitted')
            ->when($selectedPeriodId, fn ($q) => $q->where('period_id', $selectedPeriodId))
            ->latest()
            ->paginate(20);

        $employees = Employee::query()
            ->with(['office', 'division', 'position'])
            ->orderBy('name')
            ->get();

        return view('kpi.assignments.index', compact('periods', 'selectedPeriodId', 'assignments', 'employees'));
    }

    /**
     * Generate otomatis penugasan P1/P3 (atasan-bawahan langsung) untuk satu periode.
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_id' => ['required', 'integer', 'exists:kpi_periods,id'],
        ]);

        $result = $this->generator->generateForPeriod((int) $validated['period_id']);

        return back()->with('success', "Generate P1: {$result['p1']} penugasan, P3: {$result['p3']} penugasan baru.");
    }

    /**
     * Generate otomatis penugasan P2 (rekan sejawat selevel) untuk satu periode.
     */
    public function generatePeers(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_id' => ['required', 'integer', 'exists:kpi_periods,id'],
        ]);

        $created = $this->generator->generatePeersForPeriod((int) $validated['period_id']);

        return back()->with('success', "Generate P2: {$created} penugasan rekan sejawat baru.");
    }

    /**
     * Hapus satu penugasan.
     */
    public function destroy(KpiAssignment $assignment): RedirectResponse
    {
        if ($assignment->scores()->exists()) {
            return back()->withErrors(['assignment' => 'Penugasan tidak dapat dihapus karena sudah ada nilai yang diinput.']);
        }

        $assignment->delete();

        return back()->with('success', 'Penugasan berhasil dihapus.');
    }
}
