<?php

namespace App\Modules\Audit\Application;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * What a bounded context asks the Audit context to record (ADR-0009).
 *
 * `action` is a stable dotted identifier such as "investor.registered".
 * `before` / `after` hold state snapshots for material mutations.
 * `metadata` holds any other context. All three are redacted before storage.
 */
final class AuditEntry
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $action,
        public readonly ?Model $subject = null,
        public readonly ?Authenticatable $causer = null,
        public readonly ?array $before = null,
        public readonly ?array $after = null,
        public readonly array $metadata = [],
        public readonly ?string $reason = null,
    ) {}

    public static function for(string $action, ?Model $subject = null): self
    {
        return new self(action: $action, subject: $subject);
    }

    public function causedBy(?Authenticatable $causer): self
    {
        return new self($this->action, $this->subject, $causer, $this->before, $this->after, $this->metadata, $this->reason);
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function withChange(?array $before, ?array $after): self
    {
        return new self($this->action, $this->subject, $this->causer, $before, $after, $this->metadata, $this->reason);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function withMetadata(array $metadata): self
    {
        return new self($this->action, $this->subject, $this->causer, $this->before, $this->after, $metadata, $this->reason);
    }

    public function withReason(?string $reason): self
    {
        return new self($this->action, $this->subject, $this->causer, $this->before, $this->after, $this->metadata, $reason);
    }
}
