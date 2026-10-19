0001-modular-monolith.md

# ADR-0001: Modular Monolith Architecture

- Status: Accepted
- Date: 2026-09-28
- Decision Type: Architecture
- Scope: System-wide

## Context

The IOMP Platform is a production-grade government platform for GIPA.

The platform contains several related business capabilities, including:

- investor onboarding and authentication
- investor opportunity discovery and engagement
- region and district management
- investment opportunity/project management
- workflow and SLA management
- geospatial discovery
- investor inquiries and expressions of interest
- certificate and anti-forgery verification
- audit and compliance
- notifications
- dashboards and reporting
- administration and access management
- external system integrations
- PWA capabilities  and offline accessibility
- AI assistant capabilities (chatbot)
- internal automation

The project technology baseline is Laravel 12 with PHP, PostgreSQL/PostGIS, Blade, Alpine.js, Tailwind CSS, Docker and a versioned REST API.

The system must remain maintainable by GIPA or future vendors while avoiding unnecessary operational complexity.

## Decision

IOMP will be implemented as a **modular monolith**.

The application will be deployed as a single Laravel application while maintaining strong internal module and bounded-context boundaries.

The architecture will use the conceptual dependency direction:

Presentation
→ Application
→ Domain

with Infrastructure providing technical implementations behind explicit boundaries.

Modules must not directly manipulate another module's internal entities, repositories, or persistence implementation.

Cross-module communication must use explicit contracts such as:

- application services
- domain events
- integration events
- query/read contracts
- dedicated DTOs where appropriate

## Initial Business Modules

The following modules are expected to emerge from the requirements:

- Identity & Access
- Investor Onboarding
- Geography
- Opportunity Management
- Opportunity Engagement / Investor Relations
- Workflow & SLA
- Geospatial
- Certificates & Verification
- Audit & Compliance
- Notifications
- Reporting & Dashboard
- Administration & Settings
- Messaging
- External Integrations
- PWA / Platform Services & Offline Accessibility
- AI Assistant (ChatBot)

These are logical modules, not separate deployable services.

The final module boundaries may be refined when implementation reveals stronger domain boundaries. Such changes must be documented when they materially affect architecture.

## Dependency Rules

A module must not:

- access another module's private database tables directly for business operations;
- instantiate another module's internal domain objects;
- modify another module's internal state;
- bypass another module's authorization rules;
- depend on presentation-layer implementation details.

A module may communicate with another module through an explicit public contract.

## Database

The system will use one PostgreSQL database with PostGIS.

The use of one database does not remove module boundaries.

Data ownership must remain explicit.

Each module is responsible for its authoritative business data.

Cross-module read requirements should use deliberate query contracts, read models, or other explicit mechanisms rather than uncontrolled table access.

## Transactions

Transactions should normally be scoped to a single business use case.

Where a business operation spans modules, orchestration should occur at the application layer or through domain/integration events.

Distributed transaction patterns are not required for the modular monolith.

## Why Not Microservices

Microservices are not required at this stage.

The project does not currently establish a need for:

- independently deployed services;
- independently scaled services;
- independently operated databases;
- separate deployment lifecycles;
- network-level service isolation.

Introducing microservices prematurely would increase operational complexity.

## Future Extraction

A module may later be extracted into a service if there is a documented architectural reason such as:

- independent scaling requirement;
- independent deployment requirement;
- operational isolation;
- security boundary;
- integration boundary;
- organizational ownership requirement.

Extraction must preserve the domain contract.

## Consequences

### Positive

- simpler deployment;
- simpler local development;
- strong domain boundaries;
- easier transactional consistency;
- lower operational complexity;
- easier testing;
- future service extraction remains possible.

### Negative

- developers must actively maintain module boundaries;
- one deployment contains all modules;
- architectural discipline is required to prevent the monolith becoming tightly coupled.

## Alternatives Considered

### Traditional Laravel application

Rejected because it does not provide sufficient structural protection for the number of business capabilities in IOMP.

### Microservices

Not selected because the current requirements do not justify their operational complexity.

### Separate applications per business capability

Not selected because it would introduce unnecessary deployment and integration complexity.

## Related Requirements

FR-01 through FR-18

NFR-02, NFR-06, NFR-07

## Related ADRs

ADR-0002 Domain Boundaries
ADR-0003 PostgreSQL/PostGIS
ADR-0005 API Authentication
ADR-0006 Authorization Model
ADR-0010 Workflow Engine
