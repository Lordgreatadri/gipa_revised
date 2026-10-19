<?php

namespace App\Modules\Shared\Presentation\Http\Controllers;

use App\Modules\Shared\Application\RequestContext;
use Illuminate\Http\JsonResponse;

/**
 * Public liveness payload for /api/v1. Kept as a controller so the route
 * file can be cached; it exposes no business data.
 */
class ApiStatusController
{
    public function __invoke(RequestContext $context): JsonResponse
    {
        return response()->json([
            'data' => [
                'api_version' => 'v1',
                'status' => 'ok',
            ],
            'meta' => ['request_id' => $context->requestId()],
        ]);
    }
}
