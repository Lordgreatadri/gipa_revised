0004-api-versioning.md


# ADR-0004: Versioned REST API

- Status: Accepted
- Date: 2026-09-28
- Decision Type: API Architecture
- Scope: Public Integration Boundary

## Context

IOMP must expose core resources to external consumers and government integrations.

The API is a long-lived integration boundary and must support backward compatibility.

## Decision

The IOMP API will use explicit URL versioning.

The initial API version is:

/api/v1/

## API Contract

The API must expose deliberate resource contracts rather than database tables.

API resources may include:

- authentication;
- users;
- groups;
- permissions;
- investors;
- regions;
- districts;
- sectors;
- investment types;
- opportunities;
- opportunity engagements;
- inquiries;
- workflow;
- SLA;
- audit;
- dashboard/reporting;
- notifications;
- messaging;
- certificates;
- verification;
- settings where appropriate.

Not every internal domain object must automatically become a public API resource.

## Compatibility

Existing clients using `/api/v1/` must not be broken by ordinary changes.

Backward-compatible changes may include:

- adding optional response fields;
- adding optional request fields;
- adding new endpoints;
- adding non-breaking filter capabilities.

Breaking changes require a new API version according to the approved versioning policy.

## Authentication

API authentication uses the approved JWT architecture.

Protected resources must enforce authorization in addition to authentication.

## Authorization

Every protected endpoint must enforce server-side authorization.

Object-level authorization must be applied where required.

An authenticated investor must not gain access to administrative resources merely because the investor possesses a valid JWT.

## Pagination

Collection endpoints must be bounded and paginated.

No API endpoint should return an unbounded collection by default.

## Filtering

Filtering fields must be explicitly allowlisted.

Client input must never be directly converted into SQL expressions.

## Sorting

Sorting fields must be allowlisted.

## Geospatial API

Geospatial endpoints must validate:

- coordinates;
- geometry;
- query bounds;
- result limits.

Expected capabilities include:

- nearby;
- bounding box;
- polygon;
- GeoJSON.

## Errors

API errors must be:

- consistent;
- documented;
- safe;
- useful to clients.

Internal exceptions, SQL statements, filesystem paths and secrets must never be exposed.

## OpenAPI

OpenAPI 3.x documentation is part of the API contract.

API changes must update the OpenAPI documentation.

Swagger/ReDoc-compatible presentation may be used.

## Consequences

API consumers have a stable integration boundary.

API implementation requires deliberate contract management and compatibility testing.

## Related Requirements

FR-03
FR-05
FR-06
FR-08
FR-09
FR-10
FR-13
FR-15
FR-16
FR-18

NFR-07
