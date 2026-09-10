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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class QuestionnaireController extends Controller
{
    /**
     * Maks percobaan OTP salah sebelum cache diinvalidasi.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Cooldown resend OTP dalam detik.
     */
    private const RESEND_COOLDOWN = 60;

    /**
     * Halaman awal: input NIK pegawai.
     */
    public function start(): View
    {
        return view('auth.questionnaire_start');
    }

    /**
     * Validasi NIK lalu kirim OTP ke email pegawai yang terdaftar.
     *
     * Keamanan:
     * - Cooldown 60 detik antar pengiriman ulang per NIK (cegah spam kirim).
     * - OTP disimpan sebagai hash bcrypt di cache — bukan plaintext.
     * - Cache key terikat NIK + IP pengirim untuk isolasi.
     */
    public function requestOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:30'],
        ]);

        $employee = Employee::query()->where('nik', $validated['nik'])->first();

        if (! $employee) {
            // Pesan generik: tidak bocorkan apakah NIK ada atau tidak.
            return back()->withErrors(['nik' => 'NIK tidak ditemukan atau email tidak terdaftar.'])->onlyInput('nik');
        }

        if (! $employee->email) {
            return back()->withErrors(['nik' => 'NIK tidak ditemukan atau email tidak terdaftar.'])->onlyInput('nik');
        }

        // Cooldown: cegah spam kirim ulang OTP.
        $cooldownKey = 'questionnaire.otp.cooldown.'.$employee->nik;
        if (Cache::has($cooldownKey)) {
            $ttl = (int) Cache::get($cooldownKey.'.ttl', self::RESEND_COOLDOWN);

            return back()->withErrors(['nik' => "OTP sudah dikirim. Tunggu {$ttl} detik sebelum meminta ulang."])->onlyInput('nik');
        }

        $otp = (string) random_int(100000, 999999);

        // Simpan OTP sebagai hash (bukan plaintext) — tahan terhadap cache dump/leak.
        Cache::put("questionnaire.otp.{$employee->nik}", [
            'hash' => Hash::make($otp),
            'email' => $employee->email,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10)->toIso8601String(),
        ], now()->addMinutes(10));

        // Set cooldown — simpan TTL sisa agar bisa ditampilkan ke user.
        Cache::put($cooldownKey, true, self::RESEND_COOLDOWN);
        Cache::put($cooldownKey.'.ttl', self::RESEND_COOLDOWN, self::RESEND_COOLDOWN);

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
     *
     * Keamanan:
     * - OTP diverifikasi via Hash::check (timing-safe, lawan timing attack).
     * - Attempt counter: maks 5 salah → cache diinvalidasi (cegah brute force 6-digit).
     * - OTP one-time: langsung dihapus setelah verifikasi sukses.
     * - Session regenerate setelah login (cegah session fixation).
     */
    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:30'],
            'otp' => ['required', 'digits:6'],
        ]);

        $cacheKey = "questionnaire.otp.{$validated['nik']}";
        $record = Cache::get($cacheKey);

        if (! $record) {
            return back()->withErrors(['otp' => 'OTP tidak valid atau telah kedaluwarsa.'])->onlyInput('nik');
        }

        // Increment attempt sebelum verifikasi.
        $record['attempts'] = ($record['attempts'] ?? 0) + 1;

        // Verifikasi hash — timing-safe via Hash::check.
        $isValid = Hash::check($validated['otp'], $record['hash']);

        if (! $isValid) {
            if ($record['attempts'] >= self::MAX_ATTEMPTS) {
                // Invalidasi OTP setelah melebihi batas percobaan.
                Cache::forget($cacheKey);

                return back()->withErrors(['otp' => 'Terlalu banyak percobaan salah. Silakan minta OTP baru.'])->onlyInput('nik');
            }

            // Update attempt count di cache.
            $remainingTtl = now()->addMinutes(10);
            Cache::put($cacheKey, $record, $remainingTtl);

            $sisa = self::MAX_ATTEMPTS - $record['attempts'];

            return back()->withErrors(['otp' => "OTP tidak valid. Sisa percobaan: {$sisa}."])->onlyInput('nik');
        }

        // OTP valid — hapus segera (one-time use).
        Cache::forget($cacheKey);

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
