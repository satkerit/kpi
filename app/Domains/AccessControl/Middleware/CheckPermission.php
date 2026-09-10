<?php

declare(strict_types=1);

namespace App\Domains\AccessControl\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermission(...$permissions)) {
            abort(403, 'Anda tidak memiliki izin untuk aksi ini.');
        }

        return $next($request);
    }
}
