<?php

declare(strict_types=1);

namespace App\Domains\AccessControl\Middleware;

use App\Domains\HumanResource\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menentukan "pegawai penilai" untuk rute pengisian KPI.
 *
 * Dua jalur akses (sesuai aturan: pegawai TIDAK wajib punya akun login):
 *  1. Sesi OTP kuesioner (marker session, TANPA login User). Prioritas utama.
 *  2. Akun login biasa (web guard) — untuk admin / pegawai ber-akun.
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

        // Jalur 2: fallback akun login biasa.
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
