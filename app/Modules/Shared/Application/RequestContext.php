<?php

namespace App\Modules\Shared\Application;

use App\Modules\Shared\Domain\RequestSource;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

/**
 * Correlation data for the current unit of work.
 *
 * Backed by Laravel's Context so the request id survives into queued jobs
 * (ADR-0009 request correlation across HTTP → service → job → audit).
 */
final class RequestContext
{
    public const REQUEST_ID_KEY = 'request_id';

    public const SOURCE_KEY = 'source';

    public function requestId(): string
    {
        $id = Context::get(self::REQUEST_ID_KEY);

        if (! is_string($id) || $id === '') {
            $id = (string) Str::uuid();
            Context::add(self::REQUEST_ID_KEY, $id);
        }

        return $id;
    }

    public function setRequestId(string $requestId): void
    {
        Context::add(self::REQUEST_ID_KEY, $requestId);
    }

    public function source(): RequestSource
    {
        $source = Context::get(self::SOURCE_KEY);

        if (is_string($source) && ($case = RequestSource::tryFrom($source)) !== null) {
            return $case;
        }

        return app()->runningInConsole() ? RequestSource::Cli : RequestSource::Ui;
    }

    public function setSource(RequestSource $source): void
    {
        Context::add(self::SOURCE_KEY, $source->value);
    }
}
