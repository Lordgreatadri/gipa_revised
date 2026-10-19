0009-audit-architecture.md

# ADR-0009: Audit and Compliance Architecture

- Status: Accepted
- Date: 2026-09-28
- Decision Type: Security / Compliance Architecture
- Scope: System-wide

## Context

IOMP is a government platform and must preserve an audit trail of material administrative and API actions.

The TOR requires audit information including:

- actor;
- timestamp;
- IP;
- route;
- action;
- entity;
- before/after data where appropriate;
- configurable retention;
- export.

The audit system must support investigations without becoming a source of sensitive-data leakage.

## Decision

IOMP will maintain an append-oriented audit trail for material actions.

Audit records are owned by the Audit & Compliance context.

Ordinary business users must not be able to modify historical audit records.

## Audit Record

An audit record should support:

- audit ID;
- actor ID where authenticated;
- actor type;
- action;
- resource type;
- resource ID;
- before state where appropriate;
- after state where appropriate;
- IP address;
- user agent where appropriate;
- request ID/correlation ID;
- authentication context;
- source (`UI`, `API`, `CLI`, `JOB`, etc.);
- timestamp;
- reason/comment where applicable;
- relevant metadata.

## Material Actions

Examples include:

- login/security events where appropriate;
- investor registration/verification;
- opportunity creation;
- opportunity update;
- opportunity submission;
- opportunity approval;
- opportunity rejection;
- opportunity activation;
- opportunity completion;
- opportunity cancellation;
- reassignment;
- inquiry/engagement state changes;
- permission changes;
- role changes;
- settings changes;
- certificate lifecycle events;
- district publication;
- document access or changes where required.

Not every read operation must automatically create a persistent audit record unless required by policy.

## Before and After State

Before/after information should be captured for material mutations where appropriate.

Sensitive values must be redacted or excluded.

Never record:

- passwords;
- authentication tokens;
- OTP codes;
- private keys;
- secrets;
- unnecessary sensitive personal information.

## Tamper Resistance

Audit records should be append-oriented and protected from ordinary application modification.

The implementation should provide tamper-evident characteristics appropriate to the operational environment.

A normal CRUD interface must not provide arbitrary audit modification.

## Authentication Context

Where available, audit records should capture whether the action originated through:

- web UI;
- API;
- CLI;
- queue/job;
- system process.

## Request Correlation

Request IDs/correlation IDs should be propagated through relevant application operations to allow an action to be traced across:

- HTTP request;
- application service;
- queued notification;
- integration;
- audit record.

## Retention

Audit retention must be configurable according to approved policy.

The application must not silently delete historical audit information merely because storage is growing.

Retention configuration must respect applicable legal and organizational requirements.

## Export

Authorized personnel may export audit records.

Export operations themselves must be audited.

Exports must respect authorization and sensitive-data controls.

## Workflow Audit

Every material workflow transition must create an audit record containing sufficient context to establish:

- actor;
- action;
- resource;
- previous state;
- resulting state;
- timestamp;
- source;
- reason where required.

## Consequences

Audit becomes a first-class platform capability rather than incidental logging.

Audit data requires careful access control and storage lifecycle management.

## Related Requirements

FR-04
FR-09
FR-10
FR-12
FR-18

NFR-03
NFR-08
