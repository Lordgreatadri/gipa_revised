# ADR-0014: Module Layout, Audit Store and Test Database

- Status: Accepted
- Date: 2026-09-28
- Decision Type: Architecture / Engineering Conventions
- Scope: System-wide
- Origin: Decisions D-1, D-3 and D-5 of `docs/architecture/discovery/implementation-readiness-report.md`, approved by the developer on 2026-09-28.

## Context

ADR-0001 and ADR-0002 define a modular monolith with bounded contexts but did not fix where module code lives, which audit store implements ADR-0009, or how tests exercise PostgreSQL-specific behaviour required by ADR-0003. The readiness audit identified these as blockers for Slice 1 because the first module written would otherwise set undocumented conventions.

## Decision

### Module layout (D-1)

Each bounded context is a module under `app/Modules/{Context}` with the namespace `App\Modules\{Context}` and four layers:

```
app/Modules/{Context}/
├── Domain/          models, enums, events, rules, exceptions, audit action constants
├── Application/     use cases, contracts (interfaces), DTOs, listeners
├── Infrastructure/  providers, persistence, notifications, external adapters, rate limits
└── Presentation/    Http/Controllers, Http/Requests, Http/Middleware, Routes, Views
```

Rules:

- A module registers itself through `Infrastructure/Providers/{Context}ServiceProvider` listed in `bootstrap/providers.php`. Providers load the module's routes (`Presentation/Routes/web.php`, later `api.php`) and views (namespace = kebab-case context, e.g. `investor-onboarding::`).
- Cross-module dependencies point only at another module's `Domain` models/events or `Application` contracts. Never at another module's `Infrastructure` or `Presentation`.
- `Shared` is a small kernel: UUID route-key concern, request context, reference data (`Country`), request-ID and security-header middleware, throttle helpers. Anything with business meaning belongs to a context, not to `Shared`.
- Eloquent models live in `Domain/Models` (pragmatic DDD; no separate persistence entities unless an invariant requires it).
- Every table has bigint `id` as primary key and a `uuid` column used for route binding and all external representations (ADR-0003), via `App\Modules\Shared\Domain\Concerns\HasUuidRouteKey`.
- Framework-level application code that is not module-specific stays under `app/Providers` and `bootstrap/`.
- Layouts and generic UI components live in `resources/views/components/`; module views live inside the module.
- Tests mirror modules: `tests/Feature/Modules/{Context}`, `tests/Unit/Modules/{Context}`, `tests/Feature/Api/V{n}`.

Modules created in Slice 0/1: `Shared`, `Audit`, `Identity` (Identity & Access), `InvestorOnboarding`.

### Audit store (D-5)

`spatie/laravel-activitylog` is the audit store, extended to satisfy ADR-0009:

- Table `audit_records` (renamed from `activity_log`) with additional columns `uuid`, `request_id`, `source` (`UI|API|CLI|JOB`, check-constrained), `ip_address`, `user_agent`, `auth_context`.
- Append-only: model events reject update/delete (`AuditRecordIsImmutable`) and a PostgreSQL trigger rejects `UPDATE`/`DELETE` at the database.
- Other contexts write only through `App\Modules\Audit\Application\Contracts\AuditRecorder` with an `AuditEntry` (action, subject, causer, before, after, metadata, reason). Payloads pass through `SensitiveDataRedactor` (configurable key denylist in `config/audit.php`) before storage.
- Retention is configurable (`AUDIT_RETENTION_DAYS`) and nothing deletes automatically; a clean-up job requires an approved policy (AMB-26).
- Request correlation uses Laravel `Context`, so the originating `request_id` propagates into queued jobs; jobs record `source = JOB`.

### Test database (D-3)

Automated tests run against PostgreSQL (`gipa_prod_test`, same server and credentials as `.env`, database name overridden in `phpunit.xml`). SQLite is not used: check constraints, triggers and later PostGIS behaviour must be exercised by tests. The database is created once by a human (`CREATE DATABASE gipa_prod_test`).

## Consequences

- New contexts follow one layout; reviewers can locate use cases, contracts and adapters without reading the whole tree.
- Audit becomes uniform and tamper-resistant from the first feature; the cost is that every material action must explicitly call the recorder.
- Tests are slower than SQLite in-memory but faithful to production integrity rules.
- `spatie/laravel-permission` is installed (ADR-0006) with `HasRoles` on `User`; role/permission names are not seeded until D-7 is approved.

## Related

ADR-0001, ADR-0002, ADR-0003, ADR-0006, ADR-0009, `docs/architecture/discovery/implementation-readiness-report.md`, `docs/architecture/discovery/slice-0-1-implementation-notes.md`.
