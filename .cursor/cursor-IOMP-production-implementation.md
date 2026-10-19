You are continuing the IOMP production implementation.

The discovery and architecture reconciliation work has now been completed and reviewed.

IMPORTANT:
- Work only in gipa_prod/.
- gipa_demo/ is reference material only.
- Do not modify gipa_demo/* except the developer wants you to make changes specifically..
- Do not make Git commits, branches, merges, rebases, resets, pushes, or other Git-history changes.
- Do not silently invent business rules. Developer will convey client changes to you to apply as you proceed.
- Do not implement unresolved ambiguities merely because they appear in the original TOR.
- Do not introduce packages, frameworks, infrastructure, or architectural patterns that contradict the accepted ADRs/rules.
- Human approval remains required for architectural decisions, destructive operations, production changes, and Git operations.

## Authoritative documentation to read first

Before changing application code, read and reconcile these sources in this order:

1. gipa_prod/docs/architecture/discovery/architectural-discovery-report.md
2. gipa_prod/docs/architecture/discovery/reconciliation-report.md
3. gipa_prod/documentation/investor-access-implementation-brief.md
4. gipa_prod/.cursor/docs/requirements/project-requirements.md
5. gipa_prod/.cursor/docs/requirements/implementation-decisions.md
6. gipa_prod/.cursor/docs/requirements/requirements-ambiguities.md
7. gipa_prod/.cursor/docs/requirements/requirements-traceability.md
8. gipa_prod/.cursor/docs/architecture/adr/0001-*.md through the latest ADR
9. gipa_prod/.cursor/rules/*
10. Existing application structure under gipa_prod/

Also inspect the existing production skeleton and relevant prototype material in gipa_demo/ only where necessary to understand prior intent.

The production documentation and accepted ADRs are authoritative over the prototype.

## Current confirmed investor journey

The following is confirmed and must be treated as an implementation requirement:

Public visitor
    ↓
Investor registration
    ↓
Email verification
    ↓
Phone verification
    ↓
Verified investor account
    ↓
Investor dashboard
    ↓
Approved AND active investment opportunities
    ↓
Investor reviews opportunities
    ↓
Investor expresses interest and/or submits an inquiry
    ↓
Authorized GIPA management personnel review
    ↓
Management approval, rejection, or other processing
    ↓
Further engagement, matchmaking, and aftercare

Important:

- Registration alone does not grant protected opportunity access.
- Email verification alone does not grant protected opportunity access.
- Phone verification alone does not grant protected opportunity access.
- Both email and phone verification are required.
- Verified investors may access opportunities only when the opportunity is BOTH approved AND active.
- Matching must NOT hide the approved-and-active catalog until matching rules are formally defined.
- Interest and inquiry are separate concepts.
- Management review is separate from the opportunity lifecycle.
- Engagement approval must never be stored as an opportunity status.
- Public opportunity data must not leak through API, map, search, exports, cached responses, or alternate endpoints.
- Supporting documents are not automatically exposed to investors.
- Investor-visible fields remain subject to AMB-31.

## Current unresolved requirements

Do NOT invent implementation behaviour for unresolved business decisions.

At minimum, treat these as unresolved:

- AMB-01 public opportunity summary fields
- AMB-02 verification failure/expiry/attempt policy
- AMB-03 additional GIPA investor approval, if any
- AMB-04 investor profile fields
- AMB-05 matching algorithm
- AMB-06 interest state machine
- AMB-07 inquiry state machine/shared workflow question
- AMB-09 opportunity rejection destination
- AMB-10 completion conditions
- AMB-11 cancellation authority/states
- AMB-12 SLA calendar
- AMB-13 SLA pause/resume
- AMB-14 district-scoped role matrix
- AMB-15 region-level assignment
- AMB-16 internal opportunity visibility scope
- AMB-17 financial metric source/calculation/import rules
- AMB-18 exchange-rate source/additional currencies
- AMB-19 document rules
- AMB-20 ORC/GIS/payment interface contracts
- AMB-21 certificate business rules
- AMB-22 public certificate verification payload
- AMB-23 admin messaging rules
- AMB-24 authoritative offline writes
- AMB-25 reporting formulas
- AMB-26 retention
- AMB-27 legal privacy basis
- AMB-28 aftercare/further-engagement state machine
- AMB-29 AI assistant release approval
- AMB-30 spatial defaults
- AMB-31 investor-visible fields/documents
- AMB-32 activation authority and preconditions

If implementation encounters one of these, stop at the boundary and report exactly what decision is required.

Do not create fake states such as "approved", "rejected", "matched", "qualified", "engaged", "aftercare", etc. merely to make code easier.

## Architecture baseline

The accepted implementation direction is:

- Laravel 12
- PHP
- Blade
- Alpine.js
- Vanilla JavaScript where appropriate
- Tailwind CSS
- Domain-Driven Design
- Modular monolith
- PostgreSQL
- PostGIS
- Docker
- Versioned REST API
- JWT authentication

Frontend:
Uses default of light mode and toggle dark mode enabled

- Blade
- Alpine.js
- Tailwind
- ECharts
- Chart.js
- @tanstack/table-core
- Day.js
- Axios
- Lucide
- MapLibre GL
- QRCode
- SweetAlert2

Do not introduce:

- React
- Vue
- Inertia
- Livewire
- Django
- another frontend framework

unless an explicit architectural decision is made.

## Core architectural constraints

Follow the accepted ADRs.

In particular:

### Workflow

Workflow status must never be mutated arbitrarily.

Every material transition must have:

- source state
- target state
- actor
- authorization
- timestamp
- reason/comment where required
- audit information
- SLA implications

Do not write direct status assignments from arbitrary controllers/services.

Use explicit transition/application/domain mechanisms consistent with the accepted workflow ADR.

### Opportunity workflow

Current lifecycle:

draft
→ pending approval
→ approved
→ active
→ completed

Cancellation is a separate applicable terminal path.

Do not invent rejection destination or activation preconditions.

Approval does NOT automatically imply active unless the approved business rule later says so.

### District workflow
District officer identify opportunity, create/enter it for reviewers/admin aproval and publish.

draft
→ under review
→ published

Approval/rejection actions exist, but rejection destination remains unresolved.

### Authorization

JWT authentication is not authorization.

Protected endpoints require explicit authorization.

Object-level authorization is required.

District/organizational scope must be enforced server-side.

Do not duplicate authorization as a security mechanism in Alpine.

### Investor opportunity access

Protected opportunity access requires:

1. authenticated investor
2. email verified
3. phone verified
4. opportunity approved
5. opportunity active

This must be enforced consistently across:

- Blade pages
- REST APIs
- map endpoints
- GeoJSON
- nearby queries
- bounding-box queries
- polygon queries
- exports
- downloads
- search
- filters
- any future read model/cache

### API

Use:

/api/v1/

API resources are contracts, not direct database serialization.

Every incoming request must be server-side validated.

Collections must be paginated/bounded.

Filtering and sorting fields must be allowlisted.

Errors must be stable and safe.

Keep OpenAPI documentation synchronized with API changes.

Visitors use the searchable reference at `/api/documentation`, with cURL, PHP, and Node.js samples and every response status. `GET /api/v1/openapi.json` remains the machine-readable contract for tests and is not linked from that page. Access tokens are JWTs signed with `JWT_SIGNING_KEY` (HS256, 15 minutes). Refresh tokens are opaque, stored as a SHA-256 hash, rotated on use, and revocable. The external surface is status, geography and catalogue reference data, the authenticated account, and the verified-investor view of active opportunities, the map, inquiries, and expressions of interest. Drafts, contact details, staff workflow, SMS credentials, and audit payloads are not in that contract. See `.cursor/docs/architecture/api-v1.md`.

### Geospatial

Use PostgreSQL/PostGIS.

Use appropriate spatial indexes.

Do not load the complete geographic dataset into application memory.

Map viewport requests must be bounded.

A caller must never receive map points for opportunities they could not access through the corresponding detail endpoint.

The illustrative 5 km value in ADR-0007 is NOT a confirmed business rule.

### Audit

Material workflow/domain actions require audit records.

Audit information should include the accepted audit fields such as:

- actor
- action
- resource
- resource ID
- before
- after
- IP
- user agent
- request ID
- timestamp
- authentication context
- source/API/UI

Never log:

- passwords
- JWTs
- verification secrets
- sensitive authentication material

### Notifications

Notifications must react to meaningful domain/application events.

Do not couple domain workflow directly to email/SMS providers.

Use queues where appropriate.

SMS setup, including Frog and Twilio, balance thresholds, delivery-report callbacks, the transmission log, and bulk campaigns, is recorded in `.cursor/sms-delivery.md`. Gateway credentials live on `sms_provider_configs`. The earlier `sms_settings` row has been dropped.

### Performance

Target:

95% of API requests within 2 seconds under the agreed workload.

Do not claim compliance without measurement.

Avoid:

- N+1 queries
- unbounded queries
- unnecessary joins
- unnecessary serialization
- oversized API responses

### Operations

Use reproducible Docker artifacts.

Never hardcode credentials.

Keep local/testing/staging/production separated.

Production destructive changes require human approval.

Do not make destructive production changes.

### Testing

Tests are part of implementation.

For each feature, include appropriate:

- unit tests
- feature tests
- authorization tests
- workflow tests
- API tests
- database tests
- integration tests where applicable

Test negative/security paths, not only happy paths.

## Implementation strategy

Do NOT immediately build the entire application.

Proceed feature-by-feature using vertical slices.

For every feature:

1. Re-read the relevant requirements.
2. Identify the bounded context.
3. Identify the applicable ADRs.
4. Identify unresolved ambiguities that could affect the feature.
5. Define the smallest implementation boundary.
6. Explain any architectural boundary crossing.
7. Implement only the confirmed behaviour.
8. Add/update tests.
9. Run relevant tests/static analysis/formatting.
10. Inspect the resulting changes.
11. Report what was implemented and what remains blocked.

Do not perform unrelated refactoring.

Do not modify unrelated files.

## Phase 0 — implementation readiness audit

Before writing application code, perform one final implementation-readiness audit.

Inspect:

- current Laravel skeleton
- existing namespaces/modules
- existing authentication structure
- existing routes
- existing config
- existing database configuration
- existing frontend structure
- existing tests
- existing Docker setup
- existing API setup
- existing OpenAPI setup, if present
- existing authorization infrastructure
- existing audit infrastructure
- existing queue infrastructure
- existing storage configuration

Then produce:

docs/architecture/discovery/implementation-readiness-report.md

The report must contain:

1. Current production skeleton
2. Existing reusable infrastructure
3. Missing infrastructure
4. Confirmed features ready to implement
5. Features blocked by unresolved requirements
6. ADRs applicable to each ready feature
7. Security boundaries
8. Data boundaries
9. Testing requirements
10. Proposed implementation sequence
11. Risks
12. Questions requiring human/client decision

Do not modify application code during this Phase 0 audit.

Do not install packages during this audit unless an existing documented requirement explicitly requires package inspection; preferably identify missing packages without installing them.

Do not create migrations/controllers/services during this phase.

## Proposed initial implementation sequence

After the readiness audit is reviewed, the implementation should generally proceed in this order:

### Slice 1 — Identity and investor onboarding

Implement only confirmed behaviour:

- investor registration
- email verification
- phone verification integration boundary
- verified/unverified account facts
- authentication boundary
- staff invitation boundary according to ADR-0005
- audit events for registration and verification

Do not invent:

- verification retry limits
- expiry policy
- lockout rules
- extra GIPA approval
- profile requirements

If provider selection is unresolved, create an abstraction/interface and a test implementation boundary rather than choosing a provider silently.

### Slice 2 — Opportunity lifecycle

Implement the confirmed opportunity workflow:

District officer identify new opportunity in the field, create/enter it in draft state pending of reviewer/admin aproval and publishing.

draft
→ pending approval
→ approved
→ active
→ completed

plus cancellation where the existing architecture permits it.

Use explicit transition handling.

Do not invent:

- rejection destination
- cancellation authority
- activation preconditions

If those are required before implementation can safely proceed, stop and report the exact blocker.

### Slice 3 — Authorization gate

Build the reusable authorization rule for protected opportunity access:

verified investor
AND
approved opportunity
AND
active opportunity

Ensure the rule is reusable across:

- web
- API
- map
- GeoJSON
- search
- filters
- exports

Do not create a second mutable "public" flag for opportunity visibility.

### Slice 4 — Investor dashboard and opportunity discovery

Implement:

- verified investor dashboard
- paginated opportunity list
- opportunity detail
- allowlisted search
- allowlisted filters
- allowlisted sorting
- protected map access
- bounded viewport queries

Do not expose unresolved fields.

Do not expose documents unless explicitly allowed.

Do not implement matchmaking as an access filter.

### Slice 5 — Interest and inquiry capture

Implement the distinction between:

- expression of interest
- inquiry

Do not collapse them into one entity.

Do not invent their final state machines.

If a minimal persistence state is required to support the confirmed submission workflow, stop and identify the smallest state that requires client approval rather than silently inventing one.

### Slice 6 — Management review

Implement the management-side review boundary only to the extent confirmed.

Enforce:

- explicit management permission
- object-level authorization
- district/organizational scope
- audit
- notifications

Do not invent final approval/rejection semantics where the business rules are unresolved.

### Slice 7 — Geography

Implement:

- district boundaries
- PostGIS storage
- spatial indexes
- MapLibre integration
- bounded viewport queries
- GeoJSON contracts

Do not hardcode the number of Ghana districts.

Do not hardcode the nearby radius until AMB-30 is resolved.

### Slice 8 — Certificates

Only proceed once AMB-21 defines the business rules sufficiently.

The technical architecture already requires:

- certificate code
- verification URL
- QR path
- server-side QR generation
- certificate verification
- certificate lifecycle/audit

invent certificate types, issuers, triggers, numbering, validity, or public response fields. Reference gipa_demo/ certification models for tips.

### Slice 9 — Matchmaking and aftercare

Only proceed after:

- AMB-05 is resolved
- AMB-28 is resolved

Do not create a hidden matching algorithm.

Do not invent aftercare states.

### Slice 10 — External integrations

Only implement ORC/GIS/payment integrations once AMB-20 provides sufficient contract detail.

### Slice 11 — AI assistant

Do not implement until AMB-29 confirms it is approved for this release.

## Critical implementation rule

When documentation conflicts:

1. Explicit client decision
2. Accepted ADR
3. Current requirements baseline
4. Reconciled discovery documentation
5. Original TOR
6. Prototype/demo

Do not silently resolve a conflict.

If two authoritative documents conflict, report the conflict and stop at the affected boundary.

## First task now

Do ONLY Phase 0.

Do not implement application code yet.

Do not install packages.

Do not create migrations.

Do not create controllers.

Do not modify Git.

Inspect the production skeleton and documentation, then create:

gipa_prod/docs/architecture/discovery/implementation-readiness-report.md

The report must clearly separate:

- confirmed and ready
- confirmed but blocked by infrastructure
- unresolved business requirements
- architectural risks
- required human/client decisions
- recommended implementation sequence

At the end, provide a concise summary of whether Slice 1 can begin safely and identify any blocker that must be resolved first.

STOP after the readiness report.

Wait for human review before beginning Slice 1.
