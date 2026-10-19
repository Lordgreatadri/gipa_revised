<?php

namespace App\Modules\Identity\Presentation\Http\Middleware;

use App\Modules\Identity\Domain\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Staff workspace. Investors are refused even if a role was attached to them.
 */
class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isStaff()) {
            abort(403);
        }

        return $next($request);
    }
}
