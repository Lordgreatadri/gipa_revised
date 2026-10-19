0010-workflow-engine.md


# ADR-0010: Workflow and SLA Architecture

- Status: Accepted
- Date: 2026-09-28
- Decision Type: Architecture
- Scope: Workflow and SLA

## Context

IOMP contains multiple business workflows.

The TOR specifies lifecycle workflows for:

### Opportunities

draft
→ pending approval
→ approved
→ active
→ completed

with cancellation as an applicable terminal path.

### Districts

draft
→ under review
→ published

The system also requires:

- submit;
- approve;
- reject;
- reassign;
- SLA tracking;
- breach notifications.

Client-confirmed requirements additionally introduce an investor engagement flow:

1. investor registers;
2. investor verifies email;
3. investor verifies phone;
4. investor accesses investor dashboard;
5. investor reviews opportunities that are both approved and active;
6. investor chooses an opportunity;
7. investor makes an inquiry or expresses interest;
8. authorized management personnel review the engagement;
9. management approves/rejects/reassigns according to the applicable process;
10. the engagement continues through its configured lifecycle.

This engagement process must not be confused with the underlying opportunity lifecycle.

## Decision

Workflow is treated as a first-class business capability rather than as a simple status column.

State transitions must be explicit, authorized and auditable.

## Opportunity Workflow

The baseline opportunity lifecycle is:

draft
→ pending approval
→ approved
→ active
→ completed

Cancellation is an applicable terminal path.

The implementation must distinguish:

- current workflow state;
- available transitions;
- actor authorization;
- transition history;
- SLA state.

## District Workflow

The district lifecycle is:

draft
→ under review
→ published

Approval/rejection behavior must follow the approved business rule.

If rejection returns an item to a particular previous state, that must be explicitly configured rather than assumed.

## Investor Onboarding Workflow

Investor onboarding is a separate business workflow.

The baseline flow is:

registered
→ email verified
→ phone verified
→ onboarding eligible/active

The exact representation may use states or independently tracked verification conditions, but access must not be granted merely because registration occurred.

## Investor Engagement Workflow

Investor engagement is distinct from opportunity lifecycle.

Baseline business flow:

eligible investor
→ engagement/inquiry submitted
→ management review
→ approved/rejected/reassigned as applicable
→ continued engagement lifecycle

The exact terminal states and transitions must follow confirmed business requirements.

Do not collapse investor engagement status into the opportunity's status.

## Transition Model

Every transition should define:

- source state;
- target state;
- actor;
- required permission;
- timestamp;
- reason/comment where required;
- audit context;
- SLA implications;
- notification implications.

## Authorization

A transition must be explicitly authorized.

For example, a user having update permission on an opportunity does not automatically gain approval permission.

Workflow permissions must be enforced server-side.

## Rejection

The system must not invent generic rejection behavior.

Where the business has not defined the destination state, implementation must identify the unresolved decision.

Once confirmed, rejection behavior must be represented explicitly in the workflow configuration.

## Reassignment

Reassignment must:

- verify authorization;
- identify the new responsible party;
- preserve previous responsibility;
- create an audit event;
- correctly update SLA ownership/timing according to the approved rules.

## SLA

SLA behavior must be configurable.

Do not hardcode SLA durations inside controllers or models.

An SLA rule should be capable of defining:

- start event;
- due duration;
- due timestamp;
- business calendar if applicable;
- pause behavior;
- resume behavior;
- reassignment behavior;
- completion behavior;
- breach behavior.

If the client has not yet specified business-calendar, pause/resume or reassignment timing semantics, these remain explicit requirements to confirm.

## SLA Breach

SLA breach must result in a meaningful domain/application event.

Notifications should react to that event rather than being hardwired into the workflow transition itself.

## Workflow Configuration

Workflow definitions should be configurable where the business requirement calls for configurable workflow settings.

Configuration must not permit administrators to bypass fundamental security or data-integrity invariants.

## Workflow History

State transitions must be historically traceable.

A current status field alone is insufficient for audit-sensitive workflows.

History should record at minimum:

- previous state;
- new state;
- actor;
- timestamp;
- reason/comment where applicable;
- relevant assignment;
- SLA context.

## Domain Events

Meaningful workflow events may include:

- OpportunitySubmitted
- OpportunityApproved
- OpportunityRejected
- OpportunityActivated
- OpportunityCompleted
- OpportunityCancelled
- DistrictSubmittedForReview
- DistrictPublished
- InvestorRegistered
- InvestorEmailVerified
- InvestorPhoneVerified
- InvestorInterestSubmitted
- InquirySubmitted
- EngagementApproved
- EngagementRejected
- EngagementReassigned
- SlaBreached

Events must represent meaningful facts.

## Notifications

Workflow events may trigger:

- email;
- SMS where configured;
- dashboard notifications;
- administrative alerts.

Delivery should normally be asynchronous.

Notification failure must not silently corrupt the underlying workflow state.

## Idempotency

Workflow transitions and asynchronous processing must be designed to prevent duplicate effects where retries occur.

For example, a notification job may be retried without causing multiple unintended business transitions.

## Consequences

Workflow becomes explicit, testable and auditable.

The system can support multiple workflows without scattering transition logic across controllers.

Additional client-confirmed workflows can be introduced without changing the fundamental architecture.

## Related Requirements

FR-02
FR-03
FR-04
FR-08
FR-11
FR-14
FR-18

NFR-08
