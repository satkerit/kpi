<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set security headers di setiap respons HTTP.
 *
 * Mencegah:
 * - Clickjacking / frame injection → X-Frame-Options: DENY
 * - MIME sniffing → X-Content-Type-Options: nosniff
 * - Info disclosure via server version → X-Powered-By dihapus
 * - Referrer leak → Referrer-Policy: strict-origin-when-cross-origin
 * - Clickjacking tambahan → Permissions-Policy
 *
 * Catatan:
 * - HSTS (Strict-Transport-Security) diset HANYA bila request sudah HTTPS,
 *   agar tidak memaksa browser yang masih di HTTP lokal (Laragon) ke HTTPS
 *   secara permanen. Di production (di balik reverse proxy HTTPS), set
 *   APP_USE_HTTPS=true di .env agar header ini ikut dikirim.
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Hapus header X-Powered-By (default Laravel) untuk mencegah fingerprinting.
        $response->headers->remove('X-Powered-By');

        // HSTS hanya aktif bila request sudah HTTPS, agar tidak mengunci
        // browser di localhost HTTP ke HTTPS selamanya (max-age 1 tahun).
        if ($request->secure() || (bool) config('app.use_https')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
