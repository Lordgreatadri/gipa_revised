<?php

namespace App\Modules\Shared\Presentation\Http\Api;

use App\Modules\Shared\Application\RequestContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

final class ApiEnvelope
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function data(mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => ['request_id' => app(RequestContext::class)->requestId()] + $meta,
        ], $status);
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $page
     * @param  callable(mixed): mixed  $map
     */
    public static function page(LengthAwarePaginator $page, callable $map): JsonResponse
    {
        return self::data(
            $page->getCollection()->map($map)->values()->all(),
            [
                'page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        );
    }
}
