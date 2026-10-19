# IOMP Implementation Readiness Report

> Status update, 28 September 2026 (later the same day): decisions D-1, D-2, D-3, D-5 and the `APP_KEY`/package items were approved by the developer and Slice 0 + Slice 1 were implemented. See `slice-0-1-implementation-notes.md` and ADR-0014. D-4, D-6, D-7, D-8, D-9 remain open. The text below is the audit as written before implementation.

Phase 0. Audit only. No application code, packages, migrations, controllers, or Git state were changed.

Date: 28 September 2026

Target: `gipa_prod`

Sources reconciled before this audit: the discovery report, the reconciliation report, the investor-access brief, `project-requirements.md`, `implementation-decisions.md`, `requirements-ambiguities.md`, `requirements-traceability.md`, ADR-0001 through ADR-0013, and `.cursor/rules/*`. Precedence is ADR-0013.

Labels used: **Confirmed and ready**, **Confirmed but blocked by infrastructure**, **Unresolved business requirement**, **Architectural risk**, **Human/client decision required**.

---

## 1. Current production skeleton

`gipa_prod` is an unmodified Laravel 12 starter with the project name changed in `.env`.

| Area | What exists | Observation |
| --- | --- | --- |
| PHP | 8.2.26 CLI, `pdo_pgsql`, `pgsql`, `pdo_sqlite`, `gd`, `intl`, `openssl` loaded | Meets the Laravel 12 minimum. |
| Composer packages | `laravel/framework ^12`, `laravel/tinker`; dev: `phpunit ^11.5`, `pint`, `pail`, `sail`, `mockery`, `collision`, `faker` | No domain, authorization, JWT, audit, media, or spatial packages. |
| Node packages | `tailwindcss ^4`, `@tailwindcss/vite`, `vite ^7`, `laravel-vite-plugin`, `axios`, `concurrently` | `node_modules` is not installed. No Alpine.js and none of the chart, table, map, QR, or dialog libraries the frontend baseline lists. |
| Application code | `app/Http/Controllers/Controller.php`, `app/Models/User.php`, `app/Providers/AppServiceProvider.php` | No modules, no bounded-context folders, no policies, no services, no events, no jobs. |
| Routing | `routes/web.php` returns the welcome view. `routes/console.php`. `bootstrap/app.php` registers `web`, `commands`, and the `/up` health route | No `routes/api.php`. No API middleware group. No `/api/v1` prefix. |
| Middleware and exceptions | Empty closures in `bootstrap/app.php` | No security headers, no CSP, no request-ID, no maintenance whitelist. |
| Authentication | `config/auth.php` default `web` session guard over `App\Models\User` | `User` has `email_verified_at` and does not implement `MustVerifyEmail`. No phone fields. No user type. No investor or staff distinction. |
| Database config | `.env` uses `pgsql`, host `127.0.0.1`, database `gipa_prod`, user `postgres`. `.env.example` still says `sqlite` | Live probe: PostgreSQL 16.3 reachable, zero tables, no `migrations` table. PostGIS 3.4.1, `uuid-ossp`, and `pgcrypto` are available on the server and **not installed** in the `gipa_prod` database. |
| Migrations | `users`, `password_reset_tokens`, `sessions`, `cache`, `jobs`, `job_batches`, `failed_jobs` | Framework defaults. `users.id` is a bigint. No UUID column. |
| Queue, cache, session | `QUEUE_CONNECTION=database`, `CACHE_STORE=database`, `SESSION_DRIVER=database` | Framework tables exist as migrations. Nothing has been migrated. |
| Mail | `MAIL_MAILER=log` | Usable for local development. No production provider chosen. |
| Storage | `local` disk on `storage/app/private`, `public` disk, `s3` disk template | Private disk is the correct default for documents per ADR-0008. |
| Frontend | `resources/css/app.css` imports Tailwind 4, `resources/js/app.js`, `bootstrap.js` sets Axios defaults, `welcome.blade.php` | No layout, no dark-mode toggle, no components. |
| Tests | `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`, `phpunit.xml` with SQLite `:memory:` | `php artisan test` today: 1 passed, 1 failed. The feature test fails with `MissingAppKeyException` because `APP_KEY` is empty in `.env`. |
| Docker | None | No `Dockerfile`, no compose file. ADR baseline and NFR-07 require containerized artefacts. |
| OpenAPI | None | ADR-0004 makes the OpenAPI document part of the API contract. |
| Authorization | None | ADR-0006 requires Spatie permission tooling with admin-configurable roles. Not installed. |
| Audit | None | ADR-0009 requires an append-oriented audit context. No package and no custom table. |
| Documentation | `docs/architecture/discovery/`, `documentation/`, `.cursor/docs/` | Three documentation roots. Not a blocker. Worth a naming convention later. |

### Files in the tree that are not production sources

- `TechDoc.docx` and `TechDoc.txt` describe an unrelated laundry system. They are noise inside the production root.
 `10-architectureBK.mdc`, and `50-workflowsBK.mdc` are backup copies of rules. Cursor loads every `.mdc` in that folder, so two versions of the same rule are active at once. `50-workflowsBK.mdc` contains the fuller investor workflow text, while `50-workflows.mdc` is the shorter version. They do not contradict each other today, but a divergence would be silent.
- `.cursor/PostGIS-sample.log` and `.cursor/BUGBOT.md` (empty) are working notes.

These are hygiene items. Removing files is a developer action and is not done here.

---

## 2. Existing reusable infrastructure

**Confirmed and ready to reuse without modification**

- Laravel 12 application container, service provider, and configuration layout.
- PostgreSQL connection configuration in `config/database.php` with `pgsql` selected in `.env`.
- Database queue driver and the `jobs`, `job_batches`, and `failed_jobs` migrations. This satisfies "queue email and notifications" for local and testing. Production queue backing is an operations decision.
- Database session and cache drivers and their migrations.
- Password hashing (`hashed` cast) and `password_reset_tokens`. ADR-0005 requires Laravel's hashing. Present.
- `email_verified_at` on `users`. Laravel's `MustVerifyEmail` contract, signed verification URLs, and `verified` middleware are framework features, not packages. They can carry Client-INV-02 once the `User` model opts in.
- Private storage disk for documents.
- `/up` health route. It is unauthenticated and returns only a status. That matches "health output must not disclose sensitive information" as long as it stays minimal.
- PHPUnit 11 and Pint.
- Vite plus Tailwind 4 build pipeline.

**Reusable after a decision**

- SQLite `:memory:` test database. Works for identity and workflow tests. Cannot run PostGIS or `check`-constraint-specific tests. See decision D-3.

---

## 3. Missing infrastructure

Nothing below is installed. Package names are listed so the developer can approve them. Nothing was installed during this audit.

| Need | Source of the requirement | Options seen | Status |
| --- | --- | --- | --- |
| Roles and permissions | ADR-0006, `30-security.mdc`, implementation decision 8 name Spatie | `spatie/laravel-permission` | Named in the ADR. Still needs the developer to approve installation. |
| JWT for `/api/v1` | ADR-0005: an established maintained library, no custom crypto | `firebase/php-jwt` (used by the prototype) or a Laravel guard package | Library not chosen by any ADR. **Decision D-4.** |
| Audit store | ADR-0009: append-oriented, before/after, request ID, source | `spatie/laravel-activitylog`, or a first-party Audit context | ADR-0009 does not name a package. **Decision D-5.** |
| UUID route identifiers | ADR-0003: bigint `id` PK plus a `uuid` for routes; no `id` exposed publicly | Framework `Str::uuid()` with a `uuid` column and `getRouteKeyName()`, or a package | Achievable without a package. Convention needs stating once. |
| Media/document metadata | ADR-0008: metadata, private storage, checksum, access class; "images and files use media library" | `spatie/laravel-medialibrary` | ADR-0008 says "media library". Needs installation approval when Slice 2 documents arrive. Not needed for Slice 1. |
| PostGIS extension | ADR-0003, ADR-0007 | `CREATE EXTENSION postgis` in a migration or by a DBA | Extension is available on the server and not enabled. Enabling it needs a privileged role. **Decision D-6.** |
| API routing | ADR-0004 | `routes/api.php`, `api` middleware group, `/api/v1` prefix | Framework-native. No package. |
| OpenAPI | ADR-0004 | Hand-maintained `openapi.yaml` served with a bundled Swagger UI, or an annotation generator | Approach not decided. Can be deferred until the first API slice, but must exist before any API endpoint is called done. |
| Security headers and CSP | NFR-03, `30-security.mdc` | First-party middleware | No package required. |
| Request correlation ID | ADR-0009 | First-party middleware | No package required. |
| Phone verification | Client-INV-03, ADR-0005 says the mechanism is an implementation concern | Interface plus a null/log implementation now; SMS provider later | Provider is not chosen. Abstraction is enough for Slice 1. |
| Frontend libraries | Frontend baseline: Alpine.js, ECharts, Chart.js, `@tanstack/table-core`, Day.js, Lucide, MapLibre GL, QRCode, SweetAlert2 | npm packages | None installed. Only Alpine.js and Lucide are needed for Slice 1 screens. |
| Docker | NFR-07, `90-operations.mdc` | `Dockerfile` for PHP-FPM plus Nginx, compose with PostgreSQL/PostGIS and a queue worker | Not present. Not a blocker for writing Slice 1 code. Blocker for staging. |
| CI | `95-development-workflow.mdc` after-coding checks | Tests, Pint, static analysis | None. Static analysis tool not chosen. |

---

## 4. Confirmed features ready to implement

These have a client decision or an accepted ADR, and the production skeleton does not stand in their way beyond the decisions in section 12.

| Feature | Confirmed by | Ready when |
| --- | --- | --- |
| Investor self-registration | Client-INV-01, ADR-0005, ADR-0011 | D-1 module layout and D-2 minimum registration fields are answered. |
| Email verification as a separate fact | Client-INV-02, ADR-0005 | Immediately after D-1. Framework feature. |
| Phone verification as a separate fact, behind an interface | Client-INV-03, ADR-0005 | Immediately after D-1. Provider stays abstract. Failure policy is AMB-02 and is not implemented. |
| Verified investor = email verified AND phone verified | Client-INV-04, `50-workflows.mdc`, ADR-0011 | Immediately after D-1. This is a pure rule with tests. |
| Registration and verification audit events | ADR-0009, ADR-0011 | After D-5 (audit store). |
| Staff cannot self-register; staff join by invitation | ADR-0005 | The boundary (no public staff registration) is ready. Issuing invitations needs D-7. |
| Opportunity lifecycle states and named transitions: submit, approve, activate, complete, cancel | ADR-0010, current requirements section 15 | After D-1. Rejection destination (AMB-09), activation preconditions (AMB-32), and cancel authority (AMB-11) are not implemented; the transitions exist as authorized, audited operations with their business precondition left as an explicit unresolved hook. |
| Reusable access rule: verified investor AND approved AND active | Client-INV-04, ADR-0006, ADR-0011 | After Slice 1 and Slice 2 models exist. |
| Investor dashboard, paginated list, detail, allowlisted filters and sort | Client-INV-04, ADR-0004, ADR-0007 | After the access rule. Investor-visible field list is AMB-31, so the first contract exposes only the fields already agreed as identification and location without contacts or documents, and that field set must be approved (D-9). |
| Interest and inquiry as two separate records | Client-INV-05, ADR-0012 | After the access rule. Their lifecycle is AMB-06/AMB-07; only "submitted" capture is confirmed. See D-8. |
| Management review permission and queue | Client-INV-06, ADR-0006, ADR-0012 | After interest and inquiry records exist and D-7 names the permission set. |
| Region and district registry, GeoJSON import, district workflow | FR-01, FR-02, ADR-0003, ADR-0007, ADR-0010 | After D-6 (PostGIS enabled) and D-3 (spatial tests). District rejection destination is AMB-09-equivalent and stays open. |
| Security headers, CSP, request ID middleware | NFR-03, ADR-0009 | Immediately. Framework-native. |
| `/api/v1` skeleton, error envelope, pagination conventions | ADR-0004 | After D-4 (JWT library). |

---

## 5. Features blocked by unresolved requirements

| Feature | Blocking item | What is safe to build meanwhile |
| --- | --- | --- |
| Verification retry, resend limits, expiry, lockout | AMB-02 | Verification itself. Configuration keys may exist with no policy attached. |
| Extra GIPA approval of investors | AMB-03 | Nothing. Do not add an "approved investor" state. |
| Investor profile beyond account and contact channels | AMB-04 | Registration with the minimum fields in D-2. |
| Matching | AMB-05 | Nothing that filters the catalog. |
| Interest and inquiry lifecycles after submission | AMB-06, AMB-07 | Capture and a management queue of submitted items. No accept, reject, matched, or closed states. |
| Opportunity reject destination | AMB-09 | A reject transition that records the decision and stops. It must not silently move the record. This is the exact boundary where Slice 2 stops. |
| Completion and cancellation authority and preconditions | AMB-10, AMB-11 | Transitions guarded by explicit permissions; business preconditions left as reported gaps. |
| Activation preconditions | AMB-32 | An explicit activate transition guarded by permission. No auto-activate on approve. |
| SLA calendar and pause | AMB-12, AMB-13 | SLA due-date storage per transition using configurable durations, no calendar logic, no pause logic. |
| District and region role matrix | AMB-14, AMB-15, AMB-16 | Server-side scope check infrastructure that accepts a set of district IDs. The seeded matrix waits for the client. |
| Financial metric rules, exchange rates | AMB-17, AMB-18 | Precise numeric fields with explicit currency, captured as entered. |
| Document rules, investor document visibility | AMB-19, AMB-31 | Private storage with metadata. No investor download. |
| Public opportunity summary | AMB-01 | Nothing public. The public portal shows landing and public informational content only. |
| Certificates | AMB-21, AMB-22 | Nothing. The lifecycle shape is decided; the business trigger is not. |
| Aftercare, matchmaking | AMB-05, AMB-28 | Nothing. |
| External integrations | AMB-20 | Adapter interface boundaries only, if a slice needs them. |
| AI assistant | AMB-29 | Nothing. |
| Spatial defaults | AMB-30 | Bounded viewport query with an explicit configurable limit and a documented SRID (D-6 asks for the SRID). No nearby radius default. |
| Retention and privacy basis | AMB-26, AMB-27 | Configurable audit retention key with no automatic deletion job. |
| Report formulas | AMB-25 | Counts by lifecycle state and by district, since those need no formula. Nothing for job creation or infrastructure quality. |

---

## 6. ADRs applicable to each ready feature

| Ready feature | ADRs |
| --- | --- |
| Investor registration and verification | ADR-0001, ADR-0002 (Investor Onboarding context), ADR-0005, ADR-0009, ADR-0010 (onboarding workflow), ADR-0011, ADR-0013 |
| Staff invitation boundary | ADR-0005, ADR-0006 |
| Opportunity lifecycle | ADR-0002 (Opportunity Management), ADR-0003, ADR-0006 (workflow authorization), ADR-0009, ADR-0010 |
| Access rule | ADR-0004, ADR-0006, ADR-0007 (map respects the same rule), ADR-0011 |
| Investor dashboard and discovery | ADR-0004, ADR-0006, ADR-0007, ADR-0011, ADR-0012 |
| Interest and inquiry capture | ADR-0002 (Opportunity Engagement), ADR-0009, ADR-0010, ADR-0012 |
| Management review | ADR-0006, ADR-0009, ADR-0012 |
| Geography | ADR-0002, ADR-0003, ADR-0007, ADR-0010 |
| Security middleware | ADR-0009 (request ID), NFR-03 |
| `/api/v1` skeleton | ADR-0004, ADR-0005 |

---

## 7. Security boundaries

| Boundary | Rule for `gipa_prod` | Current state |
| --- | --- | --- |
| Public versus investor | Protected opportunity list, detail, search, map, GeoJSON, exports, downloads, and inquiry require a verified investor. A verified investor has both `email_verified_at` and the phone-verified fact set. | Not implemented. No model supports it. |
| Investor versus management | Investor JWT or session never reaches management routes. Management review needs an explicit permission. | No permission infrastructure. |
| Staff onboarding | No public staff registration. Invitation only. | Skeleton has no registration at all, so the boundary is currently satisfied by absence. |
| Object-level authorization | Knowing an opportunity UUID is not access. Policies check both the actor's verification and the opportunity's approved AND active state. | Not implemented. |
| District scope | Enforced server-side on UI routes, API, mutations, workflow transitions, exports, reports. | Not implemented. Role matrix is AMB-14. |
| Workflow mutation | No direct status assignment outside the transition mechanism. | No models exist yet. This has to be a rule from the first migration onward. |
| Secrets | `.env` only. `APP_KEY` is empty right now. `.env` uses the `postgres` superuser locally. | `APP_KEY` must be generated before feature tests run. The superuser is a local convenience and must not be the production role. |
| Logging | Never log passwords, JWTs, OTP values. | No logging policy code yet. |
| Headers | CSP, HSTS on TLS, frame and MIME protections. | Not present. |
| Uploads and GeoJSON | Untrusted. Validate type, size, geometry. | No upload code yet. |
| Health | `/up` stays minimal. | Present and minimal. |
| Maintenance | Admin and IP whitelist. | Framework `down` exists; whitelist and API contract for maintenance are FR-12 work, later slice. |

---

## 8. Data boundaries

Domain implications only. No tables are designed here.

| Context (ADR-0002) | Owns | Must not do |
| --- | --- | --- |
| Identity and Access | Users, staff invitations, roles, permissions, district scope assignments | Hold opportunity or engagement facts. |
| Investor Onboarding | Investor account, email-verified fact, phone-verified fact, onboarding state | Grant access on registration alone. Store a single boolean that replaces the two facts. |
| Opportunity Management | Opportunity, its lifecycle state, documents metadata, finance, contacts | Store engagement status or investor visibility as a second mutable flag. Visibility is derived from approved AND active plus the caller. |
| Opportunity Engagement | Expression of interest, inquiry, assignment, management review state | Write to the opportunity's status. Merge interest and inquiry into one table. |
| Workflow and SLA | Transition history, SLA due timestamps, breach events | Hardcode durations or a calendar. |
| Geography | Regions, districts, geometry, readiness | Hardcode 261. Store GeoJSON as the authoritative geometry. |
| Audit | Append-only records with the ADR-0009 fields | Be editable through ordinary CRUD. Contain passwords, tokens, or OTPs. |
| Notifications | Templates, queued delivery | Be called synchronously from inside a workflow transaction. |

Cross-context reads go through explicit query contracts or read models. Reporting never becomes a second source of amounts or counts.

Identifier convention from ADR-0003: bigint `id` primary key, `uuid` column for route binding and every external representation, `id` never exposed. Every new table follows this from the first migration.

---

## 9. Testing requirements

Per `80-testing.mdc` and ADR-0006. Each slice ships with these before it is called done.

| Slice | Required tests |
| --- | --- |
| 1 Identity and onboarding | Registration validation; duplicate email; email verification sets only the email fact; phone verification sets only the phone fact; neither alone yields verified investor; both yield verified investor; staff cannot self-register; audit record written for registration and each verification; no OTP or password in logs or audit payloads. |
| 2 Opportunity lifecycle | Every valid transition; every invalid transition; unauthorized actor; wrong district; history row per transition; approve does not activate; reject stops at the unresolved boundary and does not move state silently; SLA due timestamp recorded. |
| 3 Access rule | Matrix of visitor, unverified investor (four combinations), verified investor, management; against draft, pending, approved-not-active, active, completed, cancelled. Only verified × approved AND active passes. |
| 4 Discovery | Pagination bounds; disallowed filter and sort keys rejected; detail 404 or 403 for non-eligible records without leaking existence differences that the security review does not accept; map viewport returns only eligible points; GeoJSON contains no contact or document fields; response contract snapshot. |
| 5 Engagement capture | Verified investor can submit; unverified cannot; against ineligible opportunity cannot; interest and inquiry are separate records; audit and queued notification fired. |
| 6 Management review | Permission required; wrong district denied; investor cannot reach management routes; audit written. |
| 7 Geography | Spatial tests need PostgreSQL with PostGIS (D-3); GeoJSON import rejects invalid geometry and oversized payloads; viewport query bounded. |
| API | Authentication, authorization, validation, pagination, error envelope, OpenAPI document validates. |

Negative and security paths are mandatory. Happy-path-only slices are incomplete.

---

## 10. Proposed implementation sequence

Same order as the reviewed reconciliation report, with the readiness gates found in this audit.

| Order | Slice | Gate before starting |
| --- | --- | --- |
| 0 | Skeleton preparation: `APP_KEY`, module layout, test database, approved packages, security and request-ID middleware, `routes/api.php` with `/api/v1`, Docker files | D-1, D-3, D-4, D-5, D-6 answered; package installation approved. |
| 1 | Identity and investor onboarding | D-2, D-7. |
| 2 | Opportunity lifecycle | None beyond slice 0. Stops at AMB-09, AMB-11, AMB-32 boundaries. |
| 3 | Access rule | Slices 1 and 2. |
| 4 | Investor dashboard and discovery | D-9 for the initial field set. Map part waits for slice 7 geometry, but point-only viewport can start once opportunities carry a point. |
| 5 | Interest and inquiry capture | D-8. |
| 6 | Management review | D-7 permission names. |
| 7 | Geography | D-6, D-3. Can run in parallel with 4 to 6 once PostGIS is enabled. |
| 8 | Certificates | AMB-21, AMB-22. |
| 9 | Matchmaking and aftercare | AMB-05, AMB-28. |
| 10 | External integrations | AMB-20. |
| 11 | AI assistant | AMB-29. |

Slice 0 is new relative to the reconciliation report. It exists because the audit found no module layout, no API routing, no test strategy for PostGIS, and no generated application key. Doing those inside Slice 1 would mix infrastructure decisions into a business feature.

---

## 11. Risks

**Architectural risks**

- **No module layout convention.** ADR-0001 and ADR-0002 define bounded contexts and forbid cross-context internal access, but no ADR or rule says where module code lives or how namespaces are named. The first slice would set this by accident. Decision D-1 prevents that.
- **SQLite test database versus PostgreSQL production.** `phpunit.xml` uses SQLite `:memory:`. ADR-0003 relies on PostgreSQL constraints and PostGIS. Tests that pass on SQLite may not exercise `check` constraints, `uuid` types, or spatial behaviour. Decision D-3.
- **Duplicate active rule files.** `*BK.mdc` copies are loaded as live rules. If one is edited and the other is not, the assistant receives contradictory guidance.
- **Three documentation roots.** `docs/`, `documentation/`, `.cursor/docs/`. Traceability links can rot.
- **JWT and audit implemented before their library is chosen.** Choosing during coding would be a silent architecture decision. D-4 and D-5.

**Security risks**

- `APP_KEY` is empty. Any session, encrypted cookie, or signed URL is unusable until it is generated. Signed email verification links depend on it.
- Local `.env` uses the `postgres` superuser. Acceptable locally. Must never be the production role.
- `.env.example` still says SQLite, so a new developer or a container would boot on the wrong engine.
- No security headers or CSP exist, and adding them after Blade views are written forces rework of inline scripts. Add them in slice 0.

**Data risks**

- PostGIS is available but not enabled. Enabling it in a migration requires a database role with `CREATE EXTENSION` rights. If production restricts that, the extension must be enabled by a DBA outside migrations. D-6.
- The framework `users` table is bigint-only. Extending it later is fine; exposing `id` in any URL before the `uuid` column exists would break ADR-0003.

**Delivery risks**

- The prototype used SMS (Twilio) for verification, Firebase JWT, Spatie activity log, Spatie media library, and DomPDF. None of those choices is decided for production. Copying them silently would import prototype decisions as if they were ADRs.
- No Docker files. Staging cannot be reproduced until they exist. Not a blocker for slice 1 code, a blocker for slice 1 acceptance in a staging environment.
- Performance target NFR-01 cannot be evaluated until an agreed workload exists.

---

## 12. Questions requiring human/client decision

Decisions D-1 to D-9 are needed by the developer or the client. Each names the slice it blocks.

| ID | Decision | Why it is not made here | Blocks |
| --- | --- | --- | --- |
| D-1 | Module directory and namespace convention for bounded contexts. Options seen in practice: `app/Modules/{Context}/{Domain,Application,Infrastructure,Presentation}` with PSR-4 under `App\Modules`, or a `src/{Context}` root with its own PSR-4 entry. | ADR-0001 and ADR-0002 define contexts but not a code layout. Creating the first module fixes the pattern for the whole system. This is an architectural decision. | Slice 0, therefore Slice 1. |
| D-2 | Minimum registration fields for an investor. The journey implies at least name, email, phone number, and password. Anything beyond that is AMB-04. | AMB-04 says no mandatory field may be invented. Confirmation that the four implied fields are the Slice 1 minimum is needed. | Slice 1. |
| D-3 | Test database strategy: keep SQLite `:memory:` for non-spatial tests and add a PostgreSQL test database for spatial and constraint tests, or run all tests against PostgreSQL. | ADR-0003 requires PostgreSQL behaviour. A local `gipa_prod_test` database and PostGIS on it need to be created by a human. | Slice 0. Slice 7 hard. |
| D-4 | JWT library for `/api/v1`. | ADR-0005 requires an established library and does not name one. Package installation needs approval. | Slice 0 API skeleton. Not Slice 1 web flow. |
| D-5 | Audit store: `spatie/laravel-activitylog` or a first-party Audit context table. | ADR-0009 lists fields (request ID, source, authentication context) that the package does not carry by default and would need extension. Either route is an architecture choice. | Slice 1 audit events. |
| D-6 | Who enables PostGIS, and which SRID is the documented spatial reference system. | Enabling an extension needs privileges. ADR-0007 requires a documented SRS and AMB-30 leaves it open. WGS 84 / SRID 4326 is common and is not confirmed. | Slice 7. Opportunity point column in Slice 2 if it is spatial from day one. |
| D-7 | Who may issue a staff invitation and the initial permission names for management review and workflow transitions. | ADR-0005 says district management invites staff. AMB-14 leaves the role matrix open. A seed of Super Administrator with `staff.invite`, plus permission names for `opportunity.submit/approve/activate/complete/cancel` and `engagement.review`, is a proposal that needs approval. | Slice 1 invitation issuing; Slice 2 transitions; Slice 6. |
| D-8 | The single holding state for a submitted interest or inquiry. The submission must persist with some state. The smallest option is one state, `submitted`, with management assignment recorded separately. | AMB-06 and AMB-07 forbid inventing the state machine. One holding state still needs approval. | Slice 5. |
| D-9 | Initial investor-visible field set for list and detail. Proposal for approval: title, sector, sub-sector, region, district, descriptive location, project status, overview. Excludes contacts, financial metrics, and documents until AMB-31. | AMB-31 is open. Without an approved minimal set, Slice 4 cannot serialize anything. | Slice 4. |

Developer clarification received in this task and recorded, not yet in the requirements baseline: "District officer identifies an opportunity in the field and creates/enters it in draft, pending reviewer/admin approval and publishing." This was written under the district workflow heading but describes opportunity creation. It should be confirmed as: district officers hold the opportunity create and submit permission within their district scope. It partly informs D-7 and AMB-14. It is not applied until confirmed in `project-requirements.md`.

Developer instruction received: default light mode with a dark-mode toggle. Implementation decision 22 allows light/dark where the product design approves. This instruction is that approval and will be recorded when the first layout is built.

---

## Summary: can Slice 1 begin safely?

Not yet. The confirmed investor onboarding behaviour is fully specified enough to build. The production skeleton is not prepared to receive it.

Blockers that must be resolved before Slice 1 starts:

1. **D-1 module layout.** Without it the first module sets an undocumented convention.
2. **`APP_KEY`.** Empty. Feature tests fail today. Generate it (`php artisan key:generate`) or approve me to do so; it edits `.env`.
3. **D-5 audit store.** Slice 1 must write audit events for registration and verification. The store has to exist first.
4. **D-2 minimum registration fields.** Confirm name, email, phone, password.
5. **Package approval.** At minimum `spatie/laravel-permission` for the staff/investor role boundary, plus whichever audit approach D-5 selects, plus Alpine.js and Lucide on the frontend.

Items that do not block Slice 1 and can follow: D-4 JWT (web session flow first), D-6 PostGIS, D-7 invitation issuer (the "no public staff registration" boundary can be built now; issuing invites waits), Docker files, `.env.example` correction, duplicate `*BK.mdc` cleanup, removal of `TechDoc.*`.

Stopping here. Waiting for review of this report and answers to D-1, D-2, D-5, the `APP_KEY` step, and package approval before beginning Slice 1.
