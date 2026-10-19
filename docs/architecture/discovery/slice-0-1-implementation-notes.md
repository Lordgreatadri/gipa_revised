# Slice 0 and Slice 1 Implementation Notes

Date: 28 September 2026
Scope: `gipa_prod` only. No Git operations were performed; the developer reviews the working tree and commits.

Approved decisions applied (see the readiness report for the original questions): D-1 module layout, D-2 registration fields, D-5 audit store, `APP_KEY` present, packages `spatie/laravel-permission`, `spatie/laravel-activitylog`, `alpinejs`, `@alpinejs/focus`, `lucide`. Docker deferred. JWT and PostGIS left out of Slice 1. Light default with dark-mode toggle. District-officer create/submit clarification recorded (see section 6).

Architectural conventions introduced here are recorded in ADR-0014.

---

## 1. Slice 0 — skeleton preparation

| Item | Where | Notes |
| --- | --- | --- |
| Module structure | `app/Modules/{Shared,Audit,Identity,InvestorOnboarding}` | Layout per ADR-0014. Providers listed in `bootstrap/providers.php`. |
| Permissions foundation | `config/permission.php`, migration `create_permission_tables`, `HasRoles` on `User` | No roles or permission names seeded (D-7 open). |
| Audit foundation | `App\Modules\Audit\*`, `config/audit.php`, `config/activitylog.php`, migration `extend_audit_records_for_iomp` | Append-only trigger, redaction, correlation columns. |
| Request ID | `Shared\Presentation\Http\Middleware\AssignRequestId` (prepended globally) | UUID only; malformed client ids are replaced. Echoed as `X-Request-Id`; added to log context; propagates to jobs via `Context`. |
| Security headers + CSP | `Shared\Presentation\Http\Middleware\SecurityHeaders` (appended globally), `config/security.php` | Nonce-based `script-src`/`style-src`; HSTS only on HTTPS. See trade-off T-1. |
| API routing | `routes/api.php` (`/api/v1`), `bootstrap/app.php` exception rendering | JSON envelope `{error:{code,message,details?},meta:{request_id}}`; guests on `/api/*` receive 401 JSON instead of a login redirect. Only `GET /api/v1/status` exists. No JWT yet. |
| Test configuration | `phpunit.xml` → PostgreSQL `gipa_prod_test` | Database created on the local server during this task. |
| Environment | `.env.example` corrected to PostgreSQL and IOMP keys; `.env` gained `PHONE_VERIFICATION_DRIVER=mail` | `.env` secrets were not read or printed. |
| Frontend | `resources/css/app.css` (`@custom-variant dark`), `resources/js/{app,theme,icons}.js`, `resources/views/components/{layouts,ui}/*`, `resources/views/public/home.blade.php` | `npm run build` succeeds. Theme persisted in `localStorage['iomp-theme']`; light unless the user opted into dark. |
| Reference data | `Shared\Domain\Models\Country`, migration `create_countries_table`, `CountryTableSeeder` wired into `DatabaseSeeder` | Seeder import changed from `App\Models\Country` to the module namespace; data untouched. Seeder runs `updateOrCreate` by name (idempotent). |

Removed: `app/Models/User.php` (moved to `Identity\Domain\Models\User`), `resources/views/welcome.blade.php`, default `tests/Unit/ExampleTest.php`, default seeded "Test User".

## 2. Slice 1 — Identity & Access and Investor Onboarding

### Identity (`App\Modules\Identity`)

- `User`: `uuid`, `name`, `email`, `email_verified_at`, `phone` (E.164, unique, DB check constraint), `phone_verified_at`, `user_type` (`investor|staff`, DB check constraint, **not** mass-assignable), `country_id` (FK, nullable at DB; required for investor registration), password hashed cast, `HasRoles`, `MustVerifyEmail`.
- Login / logout (`/login`, `/logout`), brute-force throttle from `config/security.php`, session regeneration, audit of `auth.login_succeeded`, `auth.login_failed` (identifier only, never the password), `auth.login_locked_out`, `auth.logout`.
- E-mail verification: queued `VerifyEmailAddress` notification with a **uuid-based** signed URL (`/email/verify/{uuid}/{hash}`); `VerifyEmailRequest` checks uuid + e-mail hash + `signed` + logged-in account. Audit `account.email_verified`, `account.email_verification_resent`.
- `PostLoginDestination`: investors → investor area (gate redirects to the next onboarding step); staff → public home (no staff area yet).

### Investor Onboarding (`App\Modules\InvestorOnboarding`)

- `RegisterInvestor` use case: transaction, forces `user_type = investor`, associates country, audits `investor.registered`, dispatches `InvestorRegistered` after commit → listener sends the verification e-mail. Registration auto-logs in and lands on the e-mail notice. Registration grants no access.
- `VerifiedInvestorRule::satisfiedBy(User)`: investor AND e-mail verified AND phone verified. Single source of truth; used by `EnsureInvestorIsVerified` middleware (`investor.verified` alias). `EnsureUserIsInvestor` (`investor` alias) returns 403 for staff.
- `OnboardingStep::nextFor(User)`: derived (not stored) step order e-mail → phone (ADR-0010 "independently tracked verification conditions").
- Phone verification abstraction: `PhoneVerificationSender` contract; drivers `null` (production placeholder), `array` (tests), `mail` (non-production only, refuses in production). `RequestPhoneVerification` stores only a bcrypt hash of a random numeric code with a configurable TTL, invalidates earlier codes, audits `investor.phone_verification_requested`. `ConfirmPhoneVerification` validates the latest active code, consumes it, sets `phone_verified_at` only, audits `investor.phone_verified` (with before/after), dispatches `InvestorPhoneVerified`; wrong codes increment `failed_attempts` and audit `investor.phone_verification_failed`.
- Named rate limiters per flow (`registration`, `phone-verification-send`, `phone-verification-confirm`, `email-verification-resend`, `email-verification-verify`) so one flow cannot consume another's attempts. Values in `config/onboarding.php`.
- Investor dashboard placeholder at `/investor/dashboard` behind the gate; Slice 4 replaces its content.

### Tests (96 passing, PostgreSQL)

`tests/Feature/Modules/{Identity,InvestorOnboarding,Audit,Shared}`, `tests/Feature/Api/V1`, `tests/Unit/Modules/*`. Coverage includes: validation and duplicate rejection; staff cannot be created through registration (posted `user_type`/`role` ignored) and no staff registration route exists; each verification sets only its own fact; e-mail links reject unsigned, wrong-hash, expired, other-account and guest use; phone codes reject wrong, expired, consumed, other-user codes; throttles return 429; the OTP never appears in audit payloads or the application log; the access-gate matrix (guest, registered-only, e-mail-only, phone-only, verified, staff, JSON client); audit correlation fields, redaction, model-level and database-level immutability, `source` check constraint; security headers, CSP nonce presence and use, HSTS-on-HTTPS-only, request-id handling; API envelope for 404/405 and JSON-without-Accept; country seeder idempotency; users table check constraints.

Run: `php artisan test`. Format: `vendor/bin/pint`. Build: `npm run build`.

## 3. Trade-offs recorded for security review

| ID | Trade-off | Why | Follow-up |
| --- | --- | --- | --- |
| T-1 | CSP `script-src` includes `'unsafe-eval'`. | The standard Alpine.js build evaluates expressions with the `Function` constructor. | Evaluate `@alpinejs/csp` once the UI surface is larger; it restricts expressions to method references. Everything else is nonce-based; no `'unsafe-inline'`. |
| T-2 | Registration reveals whether an e-mail or phone is already registered (validation message). | Standard Laravel behaviour; usability for legitimate re-registrations. Login errors are uniform. | If the security review requires anti-enumeration on registration, switch to "if this address is unregistered you will receive an e-mail" flow. |
| T-3 | Phone verification uses a numeric one-time code as the default mechanism. | ADR-0005 makes the mechanism an implementation concern; a code is the only mechanism the abstraction can carry without a provider. Retry/expiry/lockout business policy remains AMB-02; only technical defaults (TTL, throttles) exist. | Confirm the mechanism and provider when the SMS decision is taken. |
| T-4 | `mail` phone driver delivers the code to the investor's e-mail in non-production. | Lets the full flow be exercised locally without an SMS provider; the class refuses to run in production. With `MAIL_MAILER=log` the code lands in the mail log locally. | Never enable in production. |
| T-5 | Failed-login audit stores the attempted identifier (e-mail). | ADR-0009 login/security events; needed to detect abuse against an account. | Covered by audit access control and retention policy (AMB-26/27). |
| T-6 | Local `.env` uses the PostgreSQL superuser. | Pre-existing local convenience. | Use a dedicated role in UAT/production. |

## 4. Known gaps left deliberately (not silently)

- Password reset is not implemented (not in the Slice 1 list). Framework `password_reset_tokens` table exists.
- Staff invitation issuing (D-7) and any role/permission seed: not implemented. The prohibition on staff self-registration is implemented and tested.
- Investor profile fields beyond D-2: AMB-04.
- Verification retry/lockout business policy: AMB-02 (technical throttles only).
- JWT guard for `/api/v1`: deferred by the developer; no protected API resources exist yet.
- OpenAPI document: not started because no contract endpoint beyond `status` exists. Required before the first real API resource (ADR-0004).
- Docker files: deferred by the developer.
- `composer audit` reports advisories in transitive dependencies `guzzlehttp/guzzle` and `league/commonmark` (framework dependencies, pre-existing). Updating them is a dependency change for the developer to approve.
- Hygiene items from the readiness report (duplicate `*BK.mdc` rules, `TechDoc.*`) untouched.

## 5. Frontend instruction applied

Light mode is the default; dark mode is a user toggle (`x-ui.theme-toggle`, `$store.theme`). This implements the developer's instruction and the product-design approval anticipated by implementation decision 22. No system-preference auto-detection, so first visit is always light.

## 6. Requirements baseline updates made for consistency

- `project-requirements.md` 4.5: district officers identify opportunities in the field and create/enter them in draft for reviewer/administrator approval and publication, within district scope; permission matrix to be formalised with AMB-14.
- `requirements-ambiguities.md`: AMB-04 partly decided (minimum registration fields = name, e-mail, phone, password, country); decisions table extended with D-2, D-1/D-3/D-5 (ADR-0014), the district-officer clarification, and the phone-verification technical default.
- `implementation-readiness-report.md`: status banner pointing here.

## 7. Next slice readiness

Slice 2 (opportunity lifecycle) can start on this skeleton. It needs D-7 permission names for transition authorization and stops at the AMB-09/AMB-11/AMB-32 boundaries as described in the readiness report. Geography (Slice 7) still needs D-6 (PostGIS enablement and SRID).
