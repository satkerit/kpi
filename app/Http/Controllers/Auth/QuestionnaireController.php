<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domains\AccessControl\Middleware\ResolveKpiEmployee;
use App\Domains\Evaluation\Models\KpiScore;
use App\Domains\Evaluation\Services\QuestionnaireAssignmentService;
use App\Domains\Evaluation\Strategies\StrategyFactory;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\MasterKpi\Models\KpiPeriod;
use App\Domains\MasterKpi\Models\KpiSubcriteria;
use App\Domains\MasterKpi\Models\RatingScale;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        // OTP lama masih valid — arahkan langsung ke form verifikasi agar bisa digunakan.
        $cooldownKey = 'questionnaire.otp.cooldown.'.$employee->nik;
        if (Cache::has($cooldownKey)) {
            $ttl = (int) Cache::get($cooldownKey.'.ttl', self::RESEND_COOLDOWN);

            return redirect()
                ->route('questionnaire.verify.form')
                ->with('status', "OTP sudah dikirim sebelumnya. Masukkan kode dari email Anda (tunggu {$ttl} detik untuk minta ulang).")
                ->with('otp_nik', $employee->nik);
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
     * Verifikasi OTP → buat sesi kuesioner (TANPA login User) → arahkan ke formulir KPI.
     *
     * Pegawai TIDAK wajib memiliki akun login (users) — identitas cukup dari
     * marker session yang terikat NIK + IP, berlaku 2 jam.
     *
     * Keamanan:
     * - OTP diverifikasi via Hash::check (timing-safe, lawan timing attack).
     * - Attempt counter: maks 5 salah → cache diinvalidasi (cegah brute force 6-digit).
     * - OTP one-time: langsung dihapus setelah verifikasi sukses.
     * - Session regenerate (cegah session fixation) sebelum menulis marker.
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

        if (! $employee->is_active) {
            return redirect()->route('questionnaire.start')
                ->withErrors(['nik' => 'Pegawai tidak aktif. Hubungi administrator.']);
        }

        // Sesi kuesioner: marker session terikat IP + user-agent, TANPA login web guard.
        $request->session()->regenerate();
        $request->session()->put(ResolveKpiEmployee::SESSION_KEY, $employee->id);
        $request->session()->put(ResolveKpiEmployee::SESSION_EXPIRY, now()->addSeconds(ResolveKpiEmployee::SESSION_TTL)->timestamp);
        $request->session()->put(ResolveKpiEmployee::SESSION_BIND, ResolveKpiEmployee::bindHash($request));

        return redirect()->intended(route('questionnaire.form'));
    }

    /**
     * Satu halaman kuesioner: SEMUA penugasan (atasan, bawahan, rekan, diri)
     * tampil sekaligus, submit sekali. Identitas dari request attribute
     * 'kpi_employee' yang diset middleware ResolveKpiEmployee.
     */
    public function form(Request $request, QuestionnaireAssignmentService $service): View|RedirectResponse
    {
        /** @var Employee|null $me */
        $me = $request->attributes->get('kpi_employee');

        if (! $me) {
            return redirect()->route('questionnaire.start')
                ->with('error', 'Sesi kuesioner tidak valid atau telah berakhir. Silakan masukkan NIK kembali.');
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
            ->get();

        $sections = [];
        foreach ($assignments as $assignment) {
            $sections[] = [
                'assignment' => $assignment,
                'subcriteria' => StrategyFactory::make($assignment->evaluator_type)->validSubcriteria(),
            ];
        }

        $maxScore = (float) (RatingScale::query()->where('period_id', $period->id)->max('max_value') ?: 10);
        $allowNotObserved = (bool) ($period->setting('allow_not_observed') ?? true);
        $pendingCount = $assignments->where('status', 'pending')->count();

        return view('questionnaire.form', [
            'period' => $period,
            'me' => $me,
            'sections' => $sections,
            'maxScore' => $maxScore,
            'allowNotObserved' => $allowNotObserved,
            'pendingCount' => $pendingCount,
            'labels' => [
                'P3' => 'Penilaian Atasan Langsung',
                'P2' => 'Penilaian Rekan Selevel',
                'P1' => 'Penilaian Bawahan',
                'SELF' => 'Penilaian Diri Sendiri',
            ],
        ]);
    }

    /**
     * Submit seluruh kuesioner sekaligus: validasi per assignment,
     * simpan semua skor dalam satu transaksi DB.
     */
    public function submitAll(Request $request): RedirectResponse
    {
        /** @var Employee|null $me */
        $me = $request->attributes->get('kpi_employee');

        if (! $me) {
            return redirect()->route('questionnaire.start')
                ->with('error', 'Sesi kuesioner tidak valid atau telah berakhir.');
        }

        $period = KpiPeriod::query()
            ->where('status', 'active')
            ->latest('id')
            ->first()
            ?? KpiPeriod::query()->latest('id')->first();

        if (! $period) {
            return back()->with('error', 'Belum ada periode KPI yang dibuka.');
        }

        $maxScore = (float) (RatingScale::query()->where('period_id', $period->id)->max('max_value') ?: 10);
        $allowNotObserved = (bool) ($period->setting('allow_not_observed') ?? true);

        $assignments = $me->assignmentsAsEvaluator()
            ->where('period_id', $period->id)
            ->where('status', 'pending')
            ->get();

        if ($assignments->isEmpty()) {
            return redirect()->route('questionnaire.form')->with('success', 'Semua penilaian sudah disubmit. Terima kasih.');
        }

        // Validasi dinamis: scores.{assignmentId}.{subcriteriaId}
        $rules = [];
        foreach ($assignments as $assignment) {
            $subcriteria = StrategyFactory::make($assignment->evaluator_type)->validSubcriteria();
            foreach ($subcriteria as $sc) {
                $key = "scores.{$assignment->id}.{$sc->id}";
                $rules[$key] = ['nullable', 'numeric', 'min:0', "max:{$maxScore}"];
                $rules["not_observed.{$assignment->id}.{$sc->id}"] = ['sometimes', 'boolean'];
            }
            $rules["comments.{$assignment->id}.*"] = ['nullable', 'string', 'max:1000'];
        }

        $validated = $request->validate(
            array_merge($rules, ['scores' => ['required', 'array']]),
            ['scores.required' => 'Isi minimal satu nilai penilaian.']
        );

        // Setiap subkriteria wajib diisi ATAU ditandai "Tidak Diamati".
        foreach ($assignments as $assignment) {
            $subcriteria = StrategyFactory::make($assignment->evaluator_type)->validSubcriteria();
            foreach ($subcriteria as $sc) {
                $raw = $validated['scores'][$assignment->id][$sc->id] ?? null;
                $notObserved = $allowNotObserved && ! empty($validated['not_observed'][$assignment->id][$sc->id]);

                if (! $notObserved && $raw === null) {
                    return back()
                        ->withErrors(["scores.{$assignment->id}.{$sc->id}" => "Nilai {$sc->name} untuk {$assignment->evaluatee->name} wajib diisi atau tandai \"Tidak Diamati\"."])
                        ->withInput();
                }
            }
        }

        DB::transaction(function () use ($validated, $assignments, $allowNotObserved) {
            foreach ($assignments as $assignment) {
                foreach ($validated['scores'][$assignment->id] ?? [] as $subcriteriaId => $rawScore) {
                    $subcriteria = KpiSubcriteria::query()->findOrFail($subcriteriaId);
                    $isNotObserved = $allowNotObserved && ! empty($validated['not_observed'][$assignment->id][$subcriteriaId]);

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
                            'comment' => $validated['comments'][$assignment->id][$subcriteriaId] ?? null,
                        ]
                    );
                }

                $assignment->update(['status' => 'submitted']);
            }
        });

        return redirect()->route('questionnaire.form')->with('success', 'Seluruh penilaian berhasil disubmit. Terima kasih atas partisipasi Anda.');
    }
}
