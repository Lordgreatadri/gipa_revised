<?php

namespace App\Modules\Shared\Presentation\Http\Controllers\Api\V1;

use App\Modules\Shared\Infrastructure\Api\OpenApiDocument;
use Illuminate\Http\JsonResponse;

class OpenApiController
{
    public function __invoke(OpenApiDocument $document): JsonResponse
    {
        return response()->json($document->specification());
    }
}
