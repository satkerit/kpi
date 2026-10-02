<?php

declare(strict_types=1);

namespace App\Domains\AccessControl\Middleware;

use App\Domains\HumanResource\Models\Employee;
use Closure;
use Illuminate\Cache\CacheManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menentukan "pegawai penilai" untuk rute pengisian KPI.
 *
 * Tiga jalur akses (sesuai aturan: pegawai TIDAK wajib punya akun login):
 *  1. Sesi OTP kuesioner via marker session (prioritas utama).
 *  2. Token verifikasi di cache (fallback jika cookie session hilang).
 *  3. Akun login biasa (web guard) — untuk admin / pegawai ber-akun.
 *
 * Hasil ditulis ke request attribute:
 *  - kpi_employee : Employee|null  (pegawai yang sedang menilai)
 *  - kpi_via_otp  : bool           (apakah lewat sesi OTP → wajib cek kepemilikan)
 */
final class ResolveKpiEmployee
{
    /** Umur sesi OTP kuesioner setelah verifikasi sukses (detik). */
    public const SESSION_TTL = 7200; // 2 jam

    public const SESSION_KEY = 'questionnaire.employee_id';

    public const SESSION_EXPIRY = 'questionnaire.expires_at';

    public const SESSION_BIND = 'questionnaire.bind';

    /** Prefix cache key untuk token verifikasi (fallback jika session hilang). */
    public const CACHE_TOKEN_PREFIX = 'questionnaire.verify_token.';

    public function __construct(protected CacheManager $cache) {}

    public function handle(Request $request, Closure $next): Response
    {
        $employee = null;
        $viaOtp = false;

        $employeeId = $request->session()->get(self::SESSION_KEY);
        $expiry = (int) $request->session()->get(self::SESSION_EXPIRY, 0);
        $bind = $request->session()->get(self::SESSION_BIND);

        // Jalur 1: sesi OTP valid (belum kedaluwarsa + terikat IP).
        if ($employeeId && $expiry > now()->timestamp && $bind === $this->bindHash($request)) {
            $candidate = Employee::query()->find($employeeId);

            if ($candidate && $candidate->is_active) {
                $employee = $candidate;
                $viaOtp = true;
            } else {
                $this->forget($request);
            }
        } elseif ($employeeId && ($expiry <= now()->timestamp || $bind !== $this->bindHash($request))) {
            // Sesi OTP kedaluwarsa / IP berubah → bersihkan.
            $this->forget($request);
        }

        // Jalur 2 (FALLBACK): token verifikasi di cache.
        // Ini menangani kasus di mana cookie session tidak sampai ke browser
        // (misal: reverse proxy stripping Set-Cookie, CDN, atau browser yang
        // memblokir third-party cookie). Token ini disimpan oleh
        // QuestionnaireController saat verify() / verifyLink() sukses.
        if (! $employee) {
            $tokenId = (string) $request->session()->get('questionnaire.token_id', '');

            if ($tokenId !== '') {
                $tokenData = $this->cache->get(self::CACHE_TOKEN_PREFIX.$tokenId);

                if (
                    is_array($tokenData)
                    && ($tokenData['expiry'] ?? 0) > now()->timestamp
                    && ($tokenData['bind'] ?? '') === $this->bindHash($request)
                ) {
                    $candidate = Employee::query()->find($tokenData['employee_id'] ?? null);

                    if ($candidate && $candidate->is_active) {
                        $employee = $candidate;
                        $viaOtp = true;

                        // Sync token ke session marker agar request berikutnya
                        // tidak perlu cek cache lagi (lebih cepat).
                        $request->session()->put(self::SESSION_KEY, $candidate->id);
                        $request->session()->put(self::SESSION_EXPIRY, $tokenData['expiry']);
                        $request->session()->put(self::SESSION_BIND, $tokenData['bind']);
                    }
                } else {
                    // Token kedaluwarsa / IP berubah → bersihkan.
                    $this->cache->forget(self::CACHE_TOKEN_PREFIX.$tokenId);
                    $request->session()->forget('questionnaire.token_id');
                }
            }
        }

        // Jalur 3: fallback akun login biasa.
        if (! $employee && ($user = $request->user())) {
            $employee = $user->employee;
        }

        $request->attributes->set('kpi_employee', $employee);
        $request->attributes->set('kpi_via_otp', $viaOtp);

        return $next($request);
    }

    /**
     * Hash pengikat sesi (IP + user-agent) untuk meredam session replay/hijack.
     */
    public static function bindHash(Request $request): string
    {
        return hash('sha256', $request->ip().'|'.$request->userAgent());
    }

    /**
     * Hapus marker sesi kuesioner.
     */
    public static function forget(Request $request): void
    {
        $request->session()->forget([
            self::SESSION_KEY,
            self::SESSION_EXPIRY,
            self::SESSION_BIND,
        ]);
    }
}
