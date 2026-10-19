0006-authorization-model.md

# ADR-0006: Authorization Model

- Status: Accepted
- Date: 2026-09-28
- Decision Type: Security Architecture
- Scope: System-wide

## Context

IOMP requires:

- roles;
- groups;
- permissions;
- district-level restrictions;
- investor access restrictions;
- management approval;
- administrative capabilities.

The system must prevent unauthorized access even where a user manipulates URLs, API parameters or client-side state.

## Decision

IOMP will use explicit server-side authorization based on:

- roles;
- permissions;
- resource ownership/relationship where applicable;
- organizational/district scope where applicable;
- actor type;
- workflow state.

Spatie authorization tooling should be used as the implementation mechanism, with roles and permissions configurable through the administrative UI.

The package is an implementation mechanism; business authorization remains an application/domain concern.

## Actor Classes

The system must distinguish at minimum between:

### Public Visitor

Can access explicitly public resources.

Cannot access investor-protected opportunity information or administrative functions.

### Investor

Can:

- manage their investor profile subject to policy;
- access their investor dashboard after email and phone verification;
- view opportunities that are both approved and active;
- review opportunity details permitted to verified investors;
- submit inquiries;
- express interest;
- track their own engagement/inquiry state.

Investors cannot:

- approve opportunities;
- modify GIPA opportunity lifecycle;
- access administrative data;
- access another investor's private information;
- bypass management review.

### Management / Authorized GIPA Personnel

Permissions are determined by assigned role and applicable scope.

Examples include:

- opportunity reviewer;
- opportunity approver;
- inquiry/engagement manager;
- district officer;
- content manager;
- auditor;
- compliance officers.
- system administrator.

Exact roles must be represented through configurable permissions rather than hardcoded role-name checks wherever practical.
Use seeders to seed the relevant roles and permissions and subject it to admin modification and additions.

## District Scope

Where a role is district-scoped, authorization must verify the actor's assigned scope server-side.

A user authorized for District A must not automatically gain access to District B.

Scope checks must apply to:

- UI routes;
- API endpoints;
- actions;
- mutations;
- workflow transitions;
- exports;
- reporting where applicable.

## Opportunity Visibility

The confirmed business rule is:

- opportunities are managed by GIPA;
- an opportunity is available to a verified investor only when its lifecycle state is both approved and active;
- draft, pending approval, completed, and cancelled opportunities are not in that investor catalog;
- the investor must have registered and successfully verified both email and phone;
- an unverified investor must not receive list, detail, search, or map access to protected opportunity information;
- investor access does not imply administrative ownership.

The implementation must distinguish:

1. internal opportunity lifecycle state;
2. investor visibility/eligibility;
3. investor engagement state.

These concepts must not be collapsed into one generic boolean.

## Investor Engagement Authorization

An investor may create an inquiry/interest for an opportunity only when:

- the investor is authenticated;
- required verification is complete;
- the opportunity is currently eligible for investor engagement;
- the investor is permitted to engage with that opportunity;
- no business rule prevents duplicate or conflicting engagement.

Management personnel with the appropriate permission review the engagement.

The exact approval/rejection/reassignment flow must follow the confirmed business workflow.

## Workflow Authorization

Workflow transitions require explicit permission.

For example:

- submit;
- approve;
- reject;
- reassign;
- activate;
- complete;
- cancel

must not be executable merely because a user can edit the underlying record.

## API Authorization

API authorization must be enforced independently of UI visibility.

Never assume that hiding a button prevents unauthorized API access.

## Consequences

Authorization becomes explicit and testable.

The system requires authorization tests covering:

- correct role;
- incorrect role;
- unauthenticated user;
- incorrect district;
- investor vs internal user;
- unauthorized opportunity state;
- unauthorized workflow transition.

## Related Requirements

FR-08
FR-09
FR-14
FR-15
FR-16

NFR-03
NFR-08
