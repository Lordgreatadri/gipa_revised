<?php

namespace App\Modules\InvestorOnboarding\Presentation\Http\Middleware;

use App\Modules\Identity\Domain\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Investor-only routes. Staff accounts are refused outright (403), never
 * redirected into the investor onboarding flow.
 */
class EnsureUserIsInvestor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isInvestor()) {
            if ($request->is('api/*')) {
                abort(403, 'This resource is available to investor accounts.');
            }

            abort(403);
        }

        return $next($request);
    }
}
