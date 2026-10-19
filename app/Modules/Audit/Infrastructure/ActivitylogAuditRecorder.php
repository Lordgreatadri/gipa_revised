<?php

namespace App\Modules\Audit\Infrastructure;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Audit\Application\SensitiveDataRedactor;
use App\Modules\Audit\Infrastructure\Persistence\AuditRecord;
use App\Modules\Shared\Application\RequestContext;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\Contracts\Activity;

final class ActivitylogAuditRecorder implements AuditRecorder
{
    public function __construct(
        private readonly SensitiveDataRedactor $redactor,
        private readonly RequestContext $context,
        private readonly AuthFactory $auth,
        private readonly Container $container,
    ) {}

    public function record(AuditEntry $entry): void
    {
        $causer = $entry->causer ?? $this->auth->guard()->user();
        $request = $this->currentRequest();

        $properties = array_filter([
            'before' => $entry->before !== null ? $this->redactor->redact($entry->before) : null,
            'after' => $entry->after !== null ? $this->redactor->redact($entry->after) : null,
            'metadata' => $entry->metadata !== [] ? $this->redactor->redact($entry->metadata) : null,
            'reason' => $entry->reason,
        ], static fn ($value) => $value !== null);

        /** @var ActivityLogger $logger */
        $logger = activity((string) config('audit.log_name', 'iomp'))
            ->event($entry->action)
            ->withProperties($properties)
            ->tap(function (Activity $activity) use ($causer, $request): void {
                /** @var AuditRecord $activity */
                $activity->request_id = $this->context->requestId();
                $activity->source = $this->context->source()->value;
                $activity->ip_address = $request?->ip();
                $activity->user_agent = $request ? mb_substr((string) $request->userAgent(), 0, 512) : null;
                $activity->auth_context = $causer ? $this->auth->getDefaultDriver() : null;
            });

        if ($entry->subject !== null) {
            $logger->performedOn($entry->subject);
        }

        if ($causer !== null) {
            $logger->causedBy($causer);
        }

        $logger->log($entry->action);
    }

    private function currentRequest(): ?Request
    {
        if (! $this->container->bound('request')) {
            return null;
        }

        $request = $this->container->make('request');

        return $request instanceof Request ? $request : null;
    }
}
