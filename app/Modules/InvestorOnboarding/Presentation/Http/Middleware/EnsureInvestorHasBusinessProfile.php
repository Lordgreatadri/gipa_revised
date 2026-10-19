<?php

namespace App\Modules\InvestorOnboarding\Presentation\Http\Middleware;

use App\Modules\Identity\Domain\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * After e-mail and phone verification, an investor with no business profile
 * may only open the profile form. KYC documents are a later step.
 */
class EnsureInvestorHasBusinessProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->investorProfile !== null) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            abort(403, 'Create a business profile before using investor resources.');
        }

        return redirect()->route('investor.profile');
    }
}
