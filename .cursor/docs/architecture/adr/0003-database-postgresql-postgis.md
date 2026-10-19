0003-database-postgresql-postgis.md

# ADR-0003: PostgreSQL and PostGIS Database Architecture

- Status: Accepted
- Date: 2026-09-28
- Decision Type: Data Architecture
- Scope: System-wide

## Context

IOMP requires relational business data, strong referential integrity and significant geospatial functionality.

The platform must support:

- regions;
- districts;
- district geometries;
- opportunity locations;
- nearby queries;
- bounding-box queries;
- polygon queries;
- GeoJSON;
- filtering;
- reporting;
- audit records;
- investor data;
- workflow state;
- document metadata.

All database table MUST use id field as PK and uuid for all route module bining and sharable no id in public

## Decision

PostgreSQL is the authoritative relational database.

PostGIS is the authoritative geospatial extension.

The Laravel application will interact with PostgreSQL through the approved persistence architecture.

## Data Integrity

Fundamental integrity must be enforced at the database level where appropriate.

Use:

- primary keys;
- foreign keys;
- unique constraints;
- proper relationships
- check constraints;
- appropriate nullability;
- indexes;
- referential integrity.

All database table MUST use id field as PK and uuid for all route module bining and sharable no id in public

Application validation does not replace database constraints.

## Geography

Regions and districts are stored as managed business data.

The initial dataset is expected to contain Ghana's 261 districts, but application logic must not hardcode the number 261.

The model must accommodate future administrative changes.

District geometry must use PostGIS-native geometry types and appropriate spatial reference systems.

## Opportunity Location

Investment opportunities may contain:

- descriptive location text;
- geospatial point.

The geographic point must be validated before persistence.

## Spatial Indexing

Appropriate GiST indexes must be used for spatial query patterns.

Expected query patterns include:

- nearby opportunity search;
- bounding-box search;
- polygon containment/intersection;
- district geometry queries.

Large geographic datasets must not be unnecessarily loaded into PHP for operations that PostGIS can perform efficiently.

## GeoJSON

GeoJSON is an API/output representation.

It must not become the authoritative persistence format.

Imported GeoJSON is untrusted input and must be validated before persistence.

Validation must consider:

- structure;
- geometry type;
- coordinate validity;
- coordinate reference expectations;
- size limits;
- polygon validity where applicable.

## Money

Authoritative monetary values must use precise numeric storage.

Floating-point arithmetic must not be used for authoritative financial calculations.

Currency must always be explicit.

The opportunity model must support the confirmed USD and GHS investment amount requirements.

## Financial Metrics

Where captured, financial metrics such as:

- ROI;
- IRR;
- NPV;
- payback period;
- projected revenues

must use appropriate numeric precision and explicit units/currency where relevant.

## Audit Data

Audit records are append-oriented.

Ordinary application users must not be able to alter historical audit records through normal CRUD operations.

Where tamper evidence is required, the implementation should provide mechanisms sufficient to detect unauthorized modification.

## Certificate History

Certificate status must not be represented solely by a mutable boolean such as `valid`.

The certificate model should preserve lifecycle/history through certificate events such as:

- ISSUED;
- VERIFIED;
- UPDATED;
- SUSPENDED;
- REVOKED.

The current certificate state may be derived or stored as a current-state projection, but history remains authoritative for lifecycle auditing.

## Consequences

PostgreSQL provides strong relational integrity and mature transactional behavior.

PostGIS provides database-native spatial processing.

The application must respect spatial indexing and query design to meet performance targets.

## Related Requirements

FR-01
FR-02
FR-03
FR-05
FR-06
FR-07
FR-10

NFR-01
NFR-02
NFR-08
