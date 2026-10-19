0002-domain-boundaries.md

# ADR-0002: Domain and Bounded Context Boundaries

- Status: Accepted
- Date: 2026-09-28
- Decision Type: Architecture
- Scope: System-wide

## Context

IOMP contains multiple business capabilities with different ownership, rules and security boundaries.

The system must use Domain-Driven Design pragmatically and prevent unrelated functionality from becoming tightly coupled.

## Decision

IOMP will use explicit bounded-context/module boundaries within the modular monolith.

The initial bounded contexts are:

### Identity & Access

Responsible for:

- users;
- authentication;
- sessions;
- roles;
- permissions;
- groups;
- authorization scopes;
- administrator access.

### Investor Onboarding

Responsible for:

- investor registration;
- investor profile;
- email verification;
- phone verification;
- onboarding state;
- investor account activation;
- investor dashboard eligibility.

Investor onboarding is a confirmed business requirement.

### Geography

Responsible for:

- regions;
- districts;
- district metadata;
- district readiness data;
- administrative hierarchy;
- district lifecycle.

### Opportunity Management

Responsible for:

- investment opportunities/projects;
- opportunity data;
- opportunity lifecycle;
- opportunity documents;
- investment structures;
- financial metrics;
- opportunity ownership/responsibility.

### Workflow & SLA

Responsible for:

- workflow definitions;
- state transitions;
- approval/rejection;
- reassignment;
- SLA rules;
- SLA timers;
- breach handling.

Workflow behavior must remain explicit and authorized.

### Opportunity Engagement

Responsible for investor interaction with approved/active opportunities, including:

- investor interest;
- inquiries;
- engagement status;
- management review;
- assignment;
- response tracking.

### Geospatial

Responsible for:

- geographic queries;
- spatial data operations;
- GeoJSON representations;
- nearby queries;
- bounding-box queries;
- polygon queries.

### Certificates & Verification

Responsible for:

- certificates;
- certificate codes;
- certificate lifecycle;
- verification;
- certificate events;
- QR verification metadata.

### Audit & Compliance

Responsible for:

- material action audit;
- before/after state where appropriate;
- actor;
- authentication context;
- request metadata;
- audit retention/export.

### Notifications

Responsible for:

- notification templates;
- email/SMS dispatch;
- notification preferences;
- queued delivery;
- delivery status.

### Reporting & Dashboard

Responsible for:

- dashboards;
- reporting queries;
- report templates;
- aggregated metrics.

Reporting must not become an alternate source of authoritative business data.

### Administration & Settings

Responsible for centralized configuration such as:

- workflow settings;
- notification settings;
- maintenance settings;
- audit settings;
- onboarding settings;
- system administration.

### Messaging

Responsible for controlled administrative user-to-user messaging.

### External Integrations

Responsible for integration adapters and contracts for systems such as:

- ORC;
- GIS/external geographic sources;
- payment systems where confirmed;
- other government/external systems.

External integrations must not leak provider-specific implementation into domain code.

## Cross-Context Rules

Each context owns its business invariants.

Other contexts must interact through explicit contracts.

For example:

Investor Onboarding
→ establishes an authenticated Investor

Opportunity Management
→ owns Opportunity

Opportunity Engagement
→ references an Opportunity through an explicit identifier/contract

Identity & Access
→ determines whether the actor may perform an action

No context may bypass these boundaries.

## Investor and Opportunity Boundary

An investor account does not own the opportunity.

GIPA manages opportunities.

An authenticated and successfully verified investor may discover eligible opportunities and initiate an engagement/inquiry.

Management personnel with the appropriate role and permission review the resulting engagement and continue the approved workflow.

This distinction must be preserved in the domain model.

## Domain Events

Important cross-context events may include:

- InvestorRegistered
- InvestorEmailVerified
- InvestorPhoneVerified
- InvestorActivated
- OpportunitySubmitted
- OpportunityApproved
- OpportunityActivated
- OpportunityCompleted
- OpportunityCancelled
- InvestorInterestSubmitted
- InquirySubmitted
- EngagementApproved
- SlaBreached
- CertificateIssued
- CertificateRevoked

Events must represent meaningful domain facts, not implementation details.

## Consequences

The architecture becomes easier to reason about and test, but developers must explicitly identify the owning context before modifying business behavior.

## Related Requirements

FR-01 through FR-18
NFR-06
NFR-08
