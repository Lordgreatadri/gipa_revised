<?php

namespace App\Modules\InvestorOnboarding\Presentation\Http\Middleware;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\OnboardingStep;
use App\Modules\InvestorOnboarding\Domain\Rules\VerifiedInvestorRule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Server-side gate for the investor experience (Client-INV-04). Applies the
 * VerifiedInvestorRule; browsers are sent to the next onboarding step, API
 * style clients receive 403.
 */
class EnsureInvestorIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isInvestor()) {
            abort(403);
        }

        if (VerifiedInvestorRule::satisfiedBy($user)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            abort(403, 'Investor verification is incomplete.');
        }

        $step = OnboardingStep::nextFor($user);

        return redirect()->route($step?->routeName() ?? 'verification.notice');
    }
}
