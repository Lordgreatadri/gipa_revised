<?php

namespace App\Modules\Shared\Presentation\Http\Middleware;

use App\Modules\Shared\Application\RequestContext;
use App\Modules\Shared\Domain\RequestSource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Assigns a correlation id to every request, records whether it arrived via
 * the UI or the API, and echoes the id back in X-Request-Id.
 *
 * Client-supplied ids are accepted only when they look like a UUID so the
 * header can never be used to inject arbitrary content into logs.
 */
class AssignRequestId
{
    public const HEADER = 'X-Request-Id';

    public function __construct(private readonly RequestContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $incoming = (string) $request->headers->get(self::HEADER, '');
        $requestId = Str::isUuid($incoming) ? strtolower($incoming) : (string) Str::uuid();

        $this->context->setRequestId($requestId);
        $this->context->setSource($request->is('api/*') ? RequestSource::Api : RequestSource::Ui);

        Log::withContext([RequestContext::REQUEST_ID_KEY => $requestId]);

        $response = $next($request);

        $response->headers->set(self::HEADER, $requestId);

        return $response;
    }
}
