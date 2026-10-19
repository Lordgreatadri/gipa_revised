<?php

namespace App\Modules\Shared\Presentation\Http\Middleware;

use App\Modules\Shared\Infrastructure\Api\JwtTokenIssuer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function __construct(private readonly JwtTokenIssuer $tokens) {}

    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (! is_string($bearer) || $bearer === '') {
            abort(401, 'An access token is required.');
        }

        $user = $this->tokens->userFromAccessToken($bearer);

        if ($user === null) {
            abort(401, 'The access token is invalid.');
        }

        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }
}
