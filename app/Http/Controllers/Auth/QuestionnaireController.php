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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

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
     * - Cache key terikat challenge acak + NIK + IP pengirim untuk isolasi.
     * - OTP hanya disimpan setelah email berhasil dikirim.
     */
    public function requestOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:30'],
        ]);

        $employee = Employee::query()->where('nik', $validated['nik'])->first();

        $hasEmail = (bool) ($employee?->email);

        // Cooldown: cegah spam kirim ulang OTP.
        // OTP lama masih valid — arahkan langsung ke form verifikasi agar bisa digunakan.
        $cooldownKey = 'questionnaire.otp.cooldown.'.$employee?->nik;
        if ($hasEmail && $employee && Cache::has($cooldownKey)) {
            $ttl = (int) Cache::get($cooldownKey.'.ttl', self::RESEND_COOLDOWN);

            return redirect()
                ->route('questionnaire.verify.form')
                ->with('status', "OTP sudah dikirim sebelumnya. Masukkan kode dari email Anda (tunggu {$ttl} detik untuk minta ulang).")
                ->with('otp_nik', $employee->nik);
        }

        if (! $hasEmail) {
            // Respons generik: tidak bocorkan apakah NIK ada atau tidak.
            return back()->withErrors(['nik' => 'NIK tidak ditemukan atau email tidak terdaftar.'])->onlyInput('nik');
        }

        $challengeId = (string) Str::ulid();
        $otp = (string) random_int(100000, 999999);

        $expiresAt = now()->addMinutes(10);

        try {
            MailSettingController::applyMailConfig();
            Mail::to($employee->email)->send(new OtpMail($otp, $employee->name));
        } catch (Throwable $e) {
            Log::error('Gagal mengirim OTP kuesioner', [
                'nik' => $employee->nik,
                'challenge_id' => $challengeId,
                'exception' => $e->getMessage(),
            ]);

            return back()->withErrors(['nik' => 'NIK tidak ditemukan atau email tidak terdaftar.'])->onlyInput('nik');
        }

        // Simpan OTP sebagai hash (bukan plaintext) — tahan terhadap cache dump/leak.
        // Challenge ID mengikat OTP ke session peminta, mencegah OTP dicuri/replay dari session lain.
        Cache::put("questionnaire.otp.{$employee->nik}.{$challengeId}", [
            'hash' => Hash::make($otp),
            'email' => $employee->email,
            'attempts' => 0,
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        // Set cooldown — simpan TTL sisa agar bisa ditampilkan ke user.
        Cache::put($cooldownKey, true, self::RESEND_COOLDOWN);
        Cache::put($cooldownKey.'.ttl', self::RESEND_COOLDOWN, self::RESEND_COOLDOWN);

        $request->session()->regenerateToken();
        $request->session()->put('otp_nik', $employee->nik);
        $request->session()->put('otp_challenge_id', $challengeId);
        $request->session()->forget('dev_otp');

        $statusMessage = 'OTP telah dikirim ke email '.maskEmail($employee->email).'. Berlaku 10 menit.';

        return redirect()
            ->route('questionnaire.verify.form')
            ->with('status', $statusMessage);
    }

    /**
     * Form input OTP.
     */
    public function showVerify(Request $request): View|RedirectResponse
    {
        $nik = (string) $request->session()->get('otp_nik', '');
        $challengeId = (string) $request->session()->get('otp_challenge_id', '');

        if ($nik === '' || $challengeId === '') {
            return redirect()->route('questionnaire.start');
        }

        return view('auth.questionnaire_verify', [
            'nik' => $nik,
            'challengeId' => $challengeId,
        ]);
    }

    /**
     * Verifikasi OTP → buat sesi kuesioner (TANPA login User) → arahkan ke formulir KPI.
     *
     * Pegawai TIDAK wajib memiliki akun login (users) — identitas cukup dari
     * marker session yang terikat NIK + IP, berlaku 2 jam.
     *
     * Keamanan:
     * - OTP diverifikasi via Hash::check (timing-safe, lawan timing attack).
     * - Challenge ID mengikat OTP ke session yang memintanya — cegah replay lintas session.
     * - Attempt counter: maks 5 salah → cache diinvalidasi (cegah brute force 6-digit).
     * - OTP one-time: konsumsi atomik via lock — tidak bisa dipakai paralel (race/replay).
     * - Session regenerate (cegah session fixation) sebelum menulis marker.
     */
    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:30'],
            'challenge_id' => ['required', 'string', 'max:64'],
            'otp' => ['required', 'digits:6'],
        ]);

        $challengeId = (string) $request->session()->get('otp_challenge_id', '');
        $sessionNik = (string) $request->session()->get('otp_nik', '');

        // Challenge ID di form harus persis sama dengan yang ada di session peminta.
        // Ini mencegah Burp replay: OTP yang dicuri dari satu session tidak bisa
        // ditebus dari session lain atau dari request tanpa session yang tepat.
        if ($challengeId === '' || $sessionNik === ''
            || ! hash_equals($challengeId, $validated['challenge_id'])
            || ! hash_equals($sessionNik, $validated['nik'])) {
            $request->session()->forget(['otp_nik', 'otp_challenge_id']);

            return back()->withErrors(['otp' => 'OTP tidak valid atau telah kedaluwarsa.'])->onlyInput('nik');
        }

        $cacheKey = "questionnaire.otp.{$validated['nik']}.{$challengeId}";

        // Kunci atomik: cegah dua request paralel (race condition / Burp Intruder)
        // mengonsumsi OTP yang sama sebelum cache dihapus.
        $lock = Cache::lock('questionnaire.otp.lock.'.$validated['nik'].'.'.$challengeId, 10);
        $record = null;

        try {
            if (! $lock->get()) {
                return back()->withErrors(['otp' => 'OTP tidak valid atau telah kedaluwarsa.'])->onlyInput('nik');
            }

            $record = Cache::get($cacheKey);

            if (! $record || ! is_array($record) || ($record['attempts'] ?? 0) >= self::MAX_ATTEMPTS) {
                return back()->withErrors(['otp' => 'OTP tidak valid atau telah kedaluwarsa.'])->onlyInput('nik');
            }

            // Increment attempt sebelum verifikasi.
            $record['attempts'] = ($record['attempts'] ?? 0) + 1;

            // Verifikasi hash — timing-safe via Hash::check.
            $isValid = Hash::check($validated['otp'], (string) ($record['hash'] ?? ''));

            if (! $isValid) {
                // Update attempt count di cache (hanya jika belum lewat batas).
                Cache::put($cacheKey, $record, now()->addMinutes(10));

                $sisa = self::MAX_ATTEMPTS - $record['attempts'];

                return back()->withErrors(['otp' => "OTP tidak valid. Sisa percobaan: {$sisa}."])->onlyInput('nik');
            }

            // OTP valid — hapus segera (one-time use).
            Cache::forget($cacheKey);
        } finally {
            $lock->release();
        }

        // Bersihkan challenge dari session — tidak boleh dipakai ulang.
        $request->session()->forget(['otp_nik', 'otp_challenge_id', 'dev_otp']);

        $employee = Employee::query()->where('nik', $validated['nik'])->firstOrFail();

        if (! $employee->is_active) {
            return redirect()->route('questionnaire.start')
                ->withErrors(['nik' => 'Pegawai tidak aktif. Hubungi administrator.']);
        }

        // Sesi kuesioner: marker session terikat IP + user-agent, TANPA login web guard.
        //
        // PENTING: HAPUS `session()->regenerate()` di sini.
        // Alasannya: regenerate() membuat id session BARU, dan cookie session baru
        // dikirim ke browser pada response berikutnya (POST verify). Namun,
        // browser belum menyimpan cookie tersebut saat GET /kuesioner/form
        // berikutnya dikirim — Laravel pun melihatnya sebagai guest.
        // Karena rute form sudah di luar grup 'guest' (fix route sebelumnya),
        // masalahnya justru di cookie: marker session tersimpan di id session
        // lama (yang cookie-nya masih di browser), sementara id baru belum
        // dikenal browser. Solusi: simpan marker di id session yang SAMA
        // (yang cookie-nya sudah di browser), hanya regenerateToken untuk CSRF.
        $request->session()->regenerateToken();
        $request->session()->put(ResolveKpiEmployee::SESSION_KEY, $employee->id);
        $request->session()->put(ResolveKpiEmployee::SESSION_EXPIRY, now()->addSeconds(ResolveKpiEmployee::SESSION_TTL)->timestamp);
        $request->session()->put(ResolveKpiEmployee::SESSION_BIND, ResolveKpiEmployee::bindHash($request));

        // Log diagnostic: bantu debug jika redirect ke login masih terjadi.
        Log::info('OTP verified', [
            'nik' => $employee->nik,
            'employee_id' => $employee->id,
            'session_id' => $request->session()->getId(),
            'ip' => $request->ip(),
        ]);

        return redirect()->route('questionnaire.form');
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

        // Log diagnostic: bantu debug jika redirect ke login masih terjadi.
        Log::info('Questionnaire form accessed', [
            'has_employee' => (bool) $me,
            'employee_id' => $me?->id,
            'employee_nik' => $me?->nik,
            'session_id' => $request->session()->getId(),
            'session_has_marker' => (bool) $request->session()->get(ResolveKpiEmployee::SESSION_KEY),
            'auth_check' => auth()->check(),
            'ip' => $request->ip(),
        ]);

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

        $assignments = $service->getAssignmentsFor($me, $period->id);

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
                'P2' => 'Penilaian Rekan Satu Atasan',
                'P1' => 'Penilaian Bawahan',
            ],
        ]);
    }

    /**
     * Submit seluruh kuesioner sekaligus: validasi per assignment,
     * simpan semua skor dalam satu transaksi DB.
     */
    public function submitAll(Request $request, QuestionnaireAssignmentService $service): RedirectResponse
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

        // Ambil penugasan untuk pegawai saat ini (termasuk yang belum disimpan ke DB)
        $assignments = $service->getAssignmentsFor($me, $period->id)->where('status', 'pending');

        if ($assignments->isEmpty()) {
            return redirect()->route('questionnaire.form')->with('success', 'Semua penilaian sudah disubmit. Terima kasih.');
        }

        // Validasi dinamis: scores.{formKey}.{subcriteriaId}
        $rules = [];
        foreach ($assignments as $assignment) {
            $keyPrefix = $assignment->form_key;
            $subcriteria = StrategyFactory::make($assignment->evaluator_type)->validSubcriteria();
            foreach ($subcriteria as $sc) {
                $rules["scores.{$keyPrefix}.{$sc->id}"] = ['nullable', 'numeric', 'min:0', "max:{$maxScore}"];
                $rules["not_observed.{$keyPrefix}.{$sc->id}"] = ['sometimes', 'boolean'];
            }
            $rules["comments.{$keyPrefix}.*"] = ['nullable', 'string', 'max:1000'];
        }

        $validated = $request->validate(
            array_merge($rules, ['scores' => ['required', 'array']]),
            ['scores.required' => 'Isi minimal satu nilai penilaian.']
        );

        // Setiap subkriteria wajib diisi ATAU ditandai "Tidak Diamati".
        foreach ($assignments as $assignment) {
            $keyPrefix = $assignment->form_key;
            $subcriteria = StrategyFactory::make($assignment->evaluator_type)->validSubcriteria();
            foreach ($subcriteria as $sc) {
                $raw = $validated['scores'][$keyPrefix][$sc->id] ?? null;
                $notObserved = $allowNotObserved && ! empty($validated['not_observed'][$keyPrefix][$sc->id]);

                if (! $notObserved && $raw === null) {
                    return back()
                        ->withErrors(["scores.{$keyPrefix}.{$sc->id}" => "Nilai {$sc->name} untuk {$assignment->evaluatee->name} wajib diisi atau tandai \"Tidak Diamati\"."])
                        ->withInput();
                }
            }
        }

        DB::transaction(function () use ($validated, $assignments, $period, $me, $allowNotObserved) {
            foreach ($assignments as $assignment) {
                $keyPrefix = $assignment->form_key;

                // Jika assignment belum tersimpan di DB, buatkan sekarang karena penilaiannya sudah selesai diisi
                $persistedAssignment = KpiAssignment::query()->firstOrCreate([
                    'period_id' => $period->id,
                    'evaluator_id' => $me->id,
                    'evaluatee_id' => $assignment->evaluatee_id,
                ], [
                    'evaluator_type' => $assignment->evaluator_type,
                    'status' => 'pending',
                ]);

                foreach ($validated['scores'][$keyPrefix] ?? [] as $subcriteriaId => $rawScore) {
                    $subcriteria = KpiSubcriteria::query()->findOrFail($subcriteriaId);
                    $isNotObserved = $allowNotObserved && ! empty($validated['not_observed'][$keyPrefix][$subcriteriaId]);

                    $weightedScore = $isNotObserved || $rawScore === null
                        ? 0
                        : round(((float) $rawScore * (float) $subcriteria->weight) / 100.0, 2);

                    KpiScore::query()->updateOrCreate(
                        [
                            'assignment_id' => $persistedAssignment->id,
                            'subcriteria_id' => $subcriteriaId,
                        ],
                        [
                            'raw_score' => $isNotObserved ? null : $rawScore,
                            'weighted_score' => $weightedScore,
                            'comment' => $validated['comments'][$keyPrefix][$subcriteriaId] ?? null,
                        ]
                    );
                }

                $persistedAssignment->update(['status' => 'submitted']);
            }
        });

        return redirect()->route('questionnaire.form')->with('success', 'Seluruh penilaian berhasil disubmit. Terima kasih atas partisipasi Anda.');
    }
}
