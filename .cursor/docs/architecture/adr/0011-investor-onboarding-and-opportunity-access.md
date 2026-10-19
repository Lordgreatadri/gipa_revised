0011 — Investor onboarding and opportunity access

# ADR-0011: Investor Onboarding and Opportunity Access

## Status

Accepted

## Date

2026-09-28

## Context

The original TOR describes public opportunity discovery and investor inquiry
functionality.

Subsequent client decisions have confirmed that investment opportunities are
not to be treated as unrestricted public content.

The agreed business behavior is that investors must register and successfully
verify both their email address and phone number before receiving authenticated
investor access.

Only opportunities that have reached the approved and active states are
eligible for investor access.

Authenticated investors access eligible opportunities through an investor
dashboard and may review opportunities and initiate an approved investor
interaction such as making an inquiry or expressing interest.

Those interactions are subsequently reviewed by authorized management
personnel.

## Decision

IOMP will implement an explicit Investor onboarding and access capability.

An investor must:

1. Register an investor account.
2. Verify their email address.
3. Verify their phone number.
4. Complete the required account activation requirements.
5. Authenticate to the investor dashboard.

Only authenticated and sufficiently verified investors may access
investor-restricted opportunity information.

An opportunity is eligible for investor access only when its lifecycle state
is both:

- approved
- active

The application must enforce this rule server-side.

Frontend visibility alone is not an authorization mechanism.

Investor access must therefore be treated as an authorization boundary rather
than merely a UI visibility rule.

## Opportunity visibility

The following distinction applies:

### Public

Public users may access explicitly public platform content and functionality
approved for unauthenticated users.

### Investor

Authenticated and verified investors may access approved and active investment
opportunities through the investor experience.

### Internal management

Authorized GIPA personnel may access opportunities according to their roles,
permissions, and applicable organizational/district scope.

## Security

Investor verification status must be persisted and evaluated server-side.

Email verification and phone verification are separate trust signals.

Unverified investors must not receive investor-level opportunity access.

Authorization must be evaluated for every protected operation.

An investor must not be able to access an opportunity merely by guessing or
obtaining its identifier.

Object-level authorization is required.

## Audit

Material investor onboarding and access events must be auditable.

Examples include:

- investor registration
- email verification
- phone verification
- account activation
- investor access to protected resources where audit requirements apply
- investor inquiry
- investor interest submission

## Notifications

Verification and material investor workflow events may generate notifications
through the notification architecture.

Notification delivery must not be tightly coupled to the domain transaction.

## Consequences

The public portal, investor portal, authorization layer, opportunity module,
inquiry/interest capability, notifications, and audit architecture must
recognize investor access as a separate security and business boundary.

The original TOR wording describing public opportunity discovery must therefore
be interpreted together with the later confirmed client decision.

## Unresolved

The following business rules remain to be confirmed before implementation:

- exact investor registration fields
- exact phone verification mechanism
- whether phone verification uses OTP
- investor profile approval requirements, if any
- whether investor organizations require separate verification
- exact post-interest workflow states

Resolved by the confirmed client decision and not left open here: verified investors may access the catalog of opportunities that are both approved and active. A matching or eligibility filter must not hide that catalog until GIPA defines one. The matching algorithm itself remains AMB-05.
- exact management approval/rejection semantics
- whether an investor can submit multiple interests/inquiries for the same
  opportunity
- visibility of internal opportunity fields to investors
