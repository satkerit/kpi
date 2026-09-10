<?php

declare(strict_types=1);

namespace App\Domains\AccessControl\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole(...$roles)) {
            abort(403, 'Anda tidak memiliki peran untuk mengakses modul ini.');
        }

        return $next($request);
    }
}
