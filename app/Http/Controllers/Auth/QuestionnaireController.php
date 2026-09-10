<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domains\Evaluation\Services\QuestionnaireAssignmentService;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\MasterKpi\Models\KpiPeriod;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class QuestionnaireController extends Controller
{
    /**
     * Halaman awal: input NIK pegawai.
     */
    public function start(): View
    {
        return view('auth.questionnaire_start');
    }

    /**
     * Validasi NIK lalu kirim OTP ke email pegawai yang terdaftar.
     */
    public function requestOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:30'],
        ]);

        $employee = Employee::query()->where('nik', $validated['nik'])->first();

        if (! $employee) {
            return back()->withErrors(['nik' => 'NIK tidak ditemukan.'])->onlyInput('nik');
        }

        if (! $employee->email) {
            return back()->withErrors(['nik' => 'Email pegawai belum terdaftar. Hubungi administrator.'])->onlyInput('nik');
        }

        $otp = (string) random_int(100000, 999999);

        // Simpan OTP 10 menit, terikat NIK + email penerima.
        Cache::put("questionnaire.otp.{$employee->nik}", [
            'otp' => $otp,
            'email' => $employee->email,
            'expires_at' => now()->addMinutes(10),
        ], now()->addMinutes(10));

        MailSettingController::applyMailConfig();

        Mail::to($employee->email)->send(new OtpMail($otp, $employee->name));

        // Di lingkungan non-produksi, tampilkan OTP agar mudah diuji (mailer log).
        if (! app()->environment('production')) {
            session()->flash('dev_otp', $otp);
        }

        return redirect()
            ->route('questionnaire.verify.form')
            ->with('status', 'OTP telah dikirim ke email '.maskEmail($employee->email).'. Berlaku 10 menit.')
            ->with('otp_nik', $employee->nik);
    }

    /**
     * Form input OTP.
     */
    public function showVerify(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('otp_nik')) {
            return redirect()->route('questionnaire.start');
        }

        return view('auth.questionnaire_verify', ['nik' => (string) $request->session()->get('otp_nik')]);
    }

    /**
     * Verifikasi OTP → login otomatis → arahkan ke formulir KPI.
     */
    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:30'],
            'otp' => ['required', 'digits:6'],
        ]);

        $record = Cache::get("questionnaire.otp.{$validated['nik']}");

        if (! $record || ! hash_equals((string) $record['otp'], $validated['otp'])) {
            return back()->withErrors(['otp' => 'OTP tidak valid atau telah kedaluwarsa.'])->onlyInput('nik');
        }

        Cache::forget("questionnaire.otp.{$validated['nik']}");

        $employee = Employee::query()->where('nik', $validated['nik'])->firstOrFail();

        // Pegawai tanpa akun login tidak bisa masuk web guard (guard users).
        if (! $employee->user_id) {
            return redirect()->route('questionnaire.start')
                ->withErrors(['nik' => 'Pegawai ini belum memiliki akun login. Hubungi administrator.']);
        }

        Auth::loginUsingId($employee->user_id, remember: false);
        $request->session()->regenerate();

        return redirect()->intended(route('questionnaire.form'));
    }

    /**
     * Formulir pengisian KPI: pastikan penugasan ada untuk periode aktif.
     */
    public function form(QuestionnaireAssignmentService $service): View|RedirectResponse
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        $me = $authUser->employee;

        if (! $me) {
            Auth::logout();

            return redirect()->route('login')->with('error', 'Akun ini tidak terhubung dengan data pegawai.');
        }

        $period = KpiPeriod::query()
            ->where('status', 'active')
            ->latest('id')
            ->first()
            ?? KpiPeriod::query()->latest('id')->first();

        if (! $period) {
            return redirect()->route('kpi.index')->with('error', 'Belum ada periode KPI yang dibuka.');
        }

        $service->ensureFor($me, $period->id);

        $assignments = $me->assignmentsAsEvaluator()
            ->where('period_id', $period->id)
            ->with(['evaluatee.position', 'evaluatee.office', 'evaluatee.department', 'scores'])
            ->get()
            ->groupBy('evaluator_type');

        return view('questionnaire.form', [
            'period' => $period,
            'groups' => $assignments,
            'labels' => [
                'P1' => 'Penilaian Bawahan',
                'P2' => 'Penilaian Rekan Selevel',
                'P3' => 'Penilaian Atasan Langsung',
            ],
        ]);
    }
}
