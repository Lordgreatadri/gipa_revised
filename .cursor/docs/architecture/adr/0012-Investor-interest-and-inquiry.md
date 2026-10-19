0012 — Investor interest and inquiry

# ADR-0012: Investor Interest and Inquiry Workflow

## Status

Accepted

## Context

The confirmed client requirements introduce two investor actions against an
approved and active opportunity:

- making an inquiry
- showing interest

These actions initiate an internal management process.

The original TOR explicitly defines investor inquiries but does not fully define
the newly confirmed investor-interest process.

## Decision

Investor interactions with opportunities will be modeled explicitly rather
than represented as an unstructured message or boolean field.

At minimum, the domain must distinguish:

- Investor Inquiry > finding more of that opportunity
- Investor Interest > interested in that opptunity, and may be mapped/matched to it.

Both must identify:

- investor
- opportunity
- submission timestamp
- current workflow state
- responsible management party where applicable
- audit information

The application layer must expose explicit use cases for creating and
processing these interactions.

## Authorization

Only a verified and authorized investor may initiate an investor interaction
against an active approved opportunity.

Management users require appropriate permissions to review and process
investor interactions.

District-level or organizational authorization must be enforced where
applicable.

## Workflow

Investor interactions enter an internal management review process.

Management personnel with the appropriate role and permission may perform
approved workflow actions.

The exact downstream states and actions must follow the confirmed business
process and must not be invented by implementation.

## Audit

Material interaction events must produce audit records.

Examples:

- inquiry submitted
- interest expressed
- interaction assigned
- interaction reassigned
- interaction approved
- interaction rejected
- interaction status changed

## Notifications

Relevant parties may receive notifications when material interaction events
occur.

Notifications should be triggered through domain/application events rather
than embedded directly inside controllers.

## Data protection

Investor personal information and communication details are sensitive and
must not be exposed through public opportunity endpoints.

API responses must expose only fields appropriate to the authenticated actor.

## Consequences

Inquiry and interest become first-class business concepts.

They should not be implemented as arbitrary flags on the Opportunity model.

The design supports future extension of the investor-to-business matchmaking
capability without forcing a redesign of Opportunity.

## Unresolved

The following require confirmation:

- complete inquiry lifecycle
- complete interest lifecycle
- whether inquiry and interest share the same workflow
- whether management approval is required for both
- who receives assigned interactions
- whether investors can withdraw interest
- whether management can request additional information
- SLA rules for management review
- investor-visible status values
- duplicate interaction rules



Identity & Access
        │
        ├── Investor Onboarding
        ├── Internal Users
        ├── Roles / Permissions
        └── Verification

Geography
        │
        ├── Region
        ├── District
        └── Spatial Data

Opportunity Management
        │
        ├── Opportunity
        ├── Investment Structure
        ├── Financial Data
        ├── Documents
        └── Opportunity Workflow

Investor Engagement
        │
        ├── Investor Inquiry
        ├── Investor Interest
        ├── Assignment
        └── Management Review

Workflow & SLA
        │
        ├── State transitions
        ├── Assignment
        ├── SLA
        └── Escalation

Notifications
Audit & Compliance
Reporting
Settings
