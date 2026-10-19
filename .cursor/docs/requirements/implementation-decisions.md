## Stacks

Laravel 12
PHP
Blade
Alpine.js
Vanilla JavaScript where appropriate
Tailwind CSS
Domain-Driven Design
Modular monolith
PostgreSQL
PostGIS
Docker
Versioned REST API
JWT authentication


## Admin UI Technology

The original TOR contains a reference to "Django admin".

This reference does not apply to the agreed implementation.

The IOMP Platform is being implemented using Laravel 12.

The administrative interface will therefore be implemented using Laravel-compatible
server-rendered administrative UI patterns using Blade, Alpine.js and Tailwind CSS.

The Django reference must not be interpreted as a requirement to introduce Django.


## IOMP Implementation Decisions

This document records confirmed implementation decisions that constrain how the IOMP requirements are implemented.

It does not replace the Terms of Reference or the project's Architecture Decision Records.

Business requirements remain authoritative for business behavior.

1. Technology Stack

The agreed implementation stack is:

Laravel 12

PHP

Blade

Alpine.js

Vanilla JavaScript where appropriate

Tailwind CSS

Vite

Domain-Driven Design

Modular monolith

PostgreSQL

PostGIS

Docker

Versioned REST API

JWT authentication

OpenAPI documentation

No alternative application framework should be introduced without an explicit architectural decision.

2. Architectural Style

IOMP shall be implemented as a modular monolith.

The application shall maintain strong bounded-context boundaries while being deployed as a single application.

The architecture follows:

Presentation
     ↓
Application
     ↓
Domain

Infrastructure supports the appropriate boundaries.


The system shall not be decomposed into microservices unless a future architectural decision establishes a concrete requirement for independent deployment, scaling or ownership.

3. Domain-Driven Design

DDD shall be applied pragmatically.

The system should use explicit business concepts such as:

Investor;

Opportunity;

District;

Region;

Inquiry;

Expression of Interest;

Workflow;

SLA;

Investment Structure;

Sector;

Certificate.

DDD patterns must be used where they protect meaningful business behavior and boundaries.

The project must not introduce abstractions merely for ceremony.

4. Admin UI Technology

The original TOR contains a reference to "Django admin".

This reference does not apply to the agreed implementation.

The IOMP Platform is implemented using Laravel 12.

The administrative interface shall therefore use Laravel-compatible server-rendered administrative UI patterns using:

Blade;

Alpine.js;

Tailwind CSS;

Vanilla JavaScript where appropriate.

The Django reference must not be interpreted as a requirement to introduce Django.

React, Vue, Inertia, Livewire or another frontend application framework must not be introduced without an explicit architecture decision.

5. Frontend Technology

The agreed frontend baseline includes:

Blade;

Alpine.js;

Tailwind CSS;

Vanilla JavaScript;

ECharts;

Chart.js where appropriate;

@tanstack/table-core;

Day.js;

Axios;

Lucide;

MapLibre GL;

Leaflet where appropriate;

QR code rendering support;

SweetAlert2 where appropriate.

Libraries should only be used where they solve an actual UI requirement.

Do not introduce overlapping frontend frameworks.

6. Geospatial Architecture

PostgreSQL with PostGIS is the authoritative geospatial persistence technology.

The system shall support:

region/district geometry;

opportunity points;

nearby queries;

bounding-box queries;

polygon queries;

GeoJSON;

spatial filtering;

map viewport queries.

Spatial operations should be executed in PostGIS rather than loading large geographic datasets into PHP.

Map viewport queries should use bounded coordinates and appropriate spatial indexes.

7. Authentication

API authentication shall use JWT.

JWT handling must use an established maintained library.

The project shall not implement cryptographic token handling manually.

Authentication and authorization are separate concerns.

8. Authorization

Authorization shall use explicit roles and permissions.

The agreed approach supports configurable role and permission administration.

Spatie's permission tooling may be used where compatible with the architecture and security requirements.

District-level authorization must be enforced server-side.

Frontend visibility controls are not security controls.

9. Investor Access Model

The investor access model is a confirmed business and implementation requirement.

The system shall support:

Investor registration
        ↓
Email verification
        ↓
Phone verification
        ↓
Verified investor account
        ↓
Investor dashboard
        ↓
Approved + active opportunities
        ↓
Opportunity review
        ↓
Interest / inquiry
        ↓
Authorized GIPA management review
        ↓
Further engagement / matchmaking / aftercare


The exact engagement workflow remains a business workflow and must not be inferred beyond confirmed requirements.

Protected investor opportunity data shall not be exposed through public endpoints merely because an opportunity has reached an approved state.

10. Opportunity Visibility

Opportunity lifecycle and visibility are separate concerns.

The workflow is:

draft
→ pending approval
→ approved
→ active
→ completed


with cancellation as an applicable terminal path.

The current client decision establishes that an opportunity becomes available to verified investors only when it is both:

approved; and

active.

This must be enforced through the application/domain authorization boundary and reflected consistently in:

Blade UI;

API resources;

search;

filters;

map;

investor dashboard;

caching/read models where used.

11. Investor Engagement

Investor engagement must not be implemented as an uncontrolled boolean such as:

interested = true


The business process requires a proper domain concept capable of supporting:

investor;

opportunity;

submission;

status;

actor;

timestamps;

review;

authorization;

audit;

notifications;

future workflow expansion.

The final workflow states remain subject to the corresponding business decision.

12. Certificate Architecture

Certificates are represented as authoritative records with lifecycle history.

The certificate design should support:

certificate
    │
    └── certificate_events
           ├── ISSUED
           ├── VERIFIED
           ├── UPDATED
           ├── SUSPENDED
           └── REVOKED


A certificate should not rely on a single valid = true/false field as its complete lifecycle representation.

Authoritative certificate data includes:

certificate code;

verification URL;

QR path.

The QR must be regenerable from the certificate code.

For official certificates that are stored, printed, downloaded and archived, QR generation should occur server-side.

13. QR Verification

The verification flow is:

QR scanner
    ↓
/verify/{certificate-code}
    ↓
Laravel
    ↓
Certificate lookup
    ↓
Certificate validation
    ↓
Verification response


Certificate verification should expose only information appropriate to the verification use case.

14. API

The API baseline is:

/api/v1/


The API is a long-lived integration boundary.

Breaking changes must not be introduced into an existing version without an approved compatibility exception.

Breaking API changes require a new API version according to the project's versioning policy.

OpenAPI documentation is part of the API contract.

15. Data Architecture

PostgreSQL is the primary relational database.

PostGIS provides geospatial capabilities.

Data ownership follows bounded-context boundaries.

The system shall use:

foreign keys;

unique constraints;

check constraints where appropriate;

appropriate nullability;

indexes based on access patterns;

UUIDs for external/route-facing identifiers where appropriate;

database-level integrity constraints.

Application validation does not replace database integrity.

16. Monetary Data

Authoritative monetary values shall use precise numeric storage.

Floating-point arithmetic must not be used for authoritative monetary calculations.

Currency must always be explicit.

17. File Storage

Uploaded files are untrusted input.

The implementation shall validate:

file type;

file size;

filename;

storage location;

authorization;

content where appropriate.

Uploaded content must never be executed.

Supporting documents shall not automatically become publicly accessible.

18. Audit Architecture

Material state-changing actions shall produce audit records.

Audit information should include:

actor;

action;

resource;

resource ID;

before;

after;

IP;

user agent;

request ID;

timestamp;

authentication context;

source;

UI/API context.

Audit records should be append-oriented and protected against ordinary modification.

Audit behavior should be centralized enough to provide consistent guarantees without placing audit-specific code throughout controllers.

19. Workflow Architecture

Workflow transitions are business operations.

Code must not arbitrarily update workflow status fields.

Transitions must be:

explicit;

authorized;

auditable;

testable.

Workflow behavior should support configurable SLA rules and meaningful domain/application events.

Notifications must react to workflow/application events rather than being hardwired into every transition implementation.

20. Queue Architecture

Queues should be used for work that should not block the request lifecycle, including where appropriate:

email delivery;

SMS delivery;

large imports;

document processing;

large reports;

asynchronous notifications.

Queued operations must be designed for correctness and idempotency.

21. PWA

The PWA implementation shall support:

service worker;

installability;

offline fallback;

health checks.

Offline capability must not bypass authorization or permit unauthorized mutation of authoritative server data.

22. Dashboard UI

Dashboard layouts may use the referenced dashboard templates for visual/design inspiration.

They are reference material rather than application framework requirements.

The final implementation remains based on:

Blade;

Alpine.js;

Tailwind CSS;

approved JavaScript libraries.

The UI should support light/dark mode where approved by the product design.

Template source code must not be copied into the project without appropriate licensing review.

23. Production Safety

The AI/developer workflow must not:

create Git commits;

push;

merge;

rebase;

reset;

alter Git history;

execute destructive production operations;

expose production secrets.

Production deployment and destructive production changes require human authorization.

24. Requirement Change Handling

When a client decision changes an existing requirement:

Record the decision in the requirements documentation.

Identify affected bounded contexts.

Identify affected ADRs.

Update traceability.

Update tests.

Update implementation.

Review security and data implications.

No client requirement change should be hidden inside implementation code.