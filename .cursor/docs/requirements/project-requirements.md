## IOMP Project Requirements
1. Purpose
This document is the consolidated working requirements baseline for the Investment Opportunities Mapping Project (IOMP) Platform.

It consolidates:

The original IOMP Terms of Reference (TOR).

Explicit current client decisions.

Confirmed implementation requirements communicated during project discovery.

Approved architectural decisions in docs/architecture/adr/.

Where the original TOR contains implementation wording that conflicts with an explicit current client decision, the current client decision takes precedence.

This document describes what the system must do. Technical implementation decisions belong in the architecture ADRs and requirements/implementation-decision.md.

## 2. Requirement Precedence
Requirements shall be interpreted in the following order:

Explicit current client decision.

Updated client requirements confirmed during implementation.

Approved Architecture Decision Records.

Current approved functional requirements.

Original TOR requirements.

Temporary implementation assumptions.

AI-generated assumptions must never silently override a documented requirement or client decision.

If an implementation discovers a contradiction, the contradiction must be documented and resolved rather than silently changing the requirement.

## 3. Platform Scope
IOMP is a production-grade government platform for GIPA.

The platform provides capabilities for:

Investor onboarding and aftercare.

Investment opportunity and project management.

Investor-to-business/opportunity matchmaking.

Certificates and anti-forgery verification.

Region and district management.

Geospatial investment discovery.

Investor inquiries and expressions of interest.

Workflow and SLA management.

User, role, group and permission management.

External integrations.

Reporting and dashboards.

Notifications.

Audit and compliance.

Administrative messaging and onboarding.

PWA capabilities.

AI assistant/chatbot capabilities where separately implemented and approved.

The ai assistance bot implementation is now part of this release and has been approved.

## 4. Actors and User Groups
The system shall support the following user categories.

4.1 Public Visitor
A public visitor may:

access public platform pages;

view publicly available informational content;

access the landing page;

access approved public information where the applicable publication policy permits;

access public certificate verification where applicable.

Public visitors shall not automatically receive access to protected investment opportunity information reserved for registered investors.

## 4.2 Investor
An investor is a registered platform user.

An investor shall be able to:

register an account;

verify email address;

verify phone number;

authenticate securely;

access an investor dashboard;

maintain permitted profile information;

view investment opportunities made available to investors;

search and filter available opportunities;

review opportunity details;

identify opportunities matching their investment needs;

express interest in an opportunity;

submit an inquiry associated with an opportunity;

track the status of their inquiries/interests where permitted;

receive relevant notifications.

Investor access shall be subject to authentication, account verification and authorization.

## 4.3 GIPA Management Personnel
Authorized GIPA personnel shall be able to review and process investor interests, inquiries and related workflow actions according to their assigned permissions.

Access shall be controlled through roles, permissions and applicable organizational/district scope.

4.4 Content/Opportunity Officers
Authorized personnel may create, maintain, submit and manage investment opportunities according to assigned permissions and workflow responsibilities.

4.5 District Officers
District-scoped personnel may access and manage data within their authorized district scope where applicable.

Developer clarification (2026-09-28): district officers identify investment opportunities in the field and create/enter them in the draft state, pending reviewer/administrator approval and publication, within their district scope. The exact permission matrix is formalised with AMB-14 when authorization is implemented.

District scope must be enforced server-side.

4.6 System Administrators
Authorized system administrators may manage:

users;

roles;

groups;

permissions;

system settings;

workflow configuration;

notifications;

maintenance mode;

audit configuration;

onboarding configuration;

operational settings.

Administrative privileges shall follow least-privilege principles.

## 5. Investor Registration and Access Requirement
The investor access model is a confirmed client requirement. Trace it with these identifiers. Do not reopen them as ambiguities.

| Identifier | Confirmed client decision |
| --- | --- |
| Client-INV-01 | Investor registration is required before protected opportunity access. |
| Client-INV-02 | Email verification is required. |
| Client-INV-03 | Phone verification is required. |
| Client-INV-04 | A verified investor dashboard shows opportunities that are both approved and active. |
| Client-INV-05 | A verified investor may express interest and/or submit an inquiry. |
| Client-INV-06 | Authorized GIPA management personnel review that engagement. |

The investor access model is a confirmed client requirement.

Investment opportunities that have reached the applicable approved and active state shall be available to authenticated and successfully verified investors.

The investor access flow is:

Public visitor
    ↓
Investor registration
    ↓
Email verification
    ↓
Phone verification
    ↓
Investor account activated/verified
    ↓
Investor dashboard
    ↓
Available approved + active opportunities
    ↓
Opportunity review
    ↓
Investor expresses interest and/or submits inquiry
    ↓
GIPA management review
    ↓
Authorized approval/rejection/processing
    ↓
Further workflow / engagement / aftercare

The exact subsequent matchmaking and aftercare states shall follow approved business workflow definitions.

The system must not expose protected investor-only opportunity information merely because an opportunity is approved or active.

## 6. Investment Opportunity Availability
Investment opportunities have lifecycle states governed by the approved workflow architecture.

The baseline lifecycle is:

draft
  ↓
pending approval
  ↓
approved
  ↓
active
  ↓
completed

Cancellation is an applicable terminal path.

Only opportunities satisfying the applicable publication/access rules may appear in investor-facing opportunity discovery.

The current client decision establishes that an opportunity is investor-accessible only when it is both approved and active, and only after successful investor registration, email verification, and phone verification.

Draft, pending approval, completed, and cancelled opportunities are not part of that investor catalog.

An account that has not completed both verifications is not a verified investor and must not receive the catalog, detail, search hits, or map points.

Public availability of opportunity data must therefore be treated separately from authenticated investor availability. Whether any limited public summary exists is AMB-01. That open question does not weaken Client-INV-04.

7. Investor Interest and Inquiry
The platform shall allow a verified investor to engage with an available opportunity.

Supported engagement includes:

reviewing opportunity information;

expressing interest;

submitting an inquiry;

providing relevant investor information;

tracking engagement status where permitted.

Management personnel with the appropriate role and permission shall review investor engagement.

The system shall support a controlled workflow for:

Investor submission
    ↓
Management review
    ↓
Decision / processing
    ↓
Assignment / engagement
    ↓
Response / matchmaking
    ↓
Further investor relationship management

The exact business states, SLA durations and decision rules shall be configured through approved workflow requirements rather than hardcoded in controllers.

8. Region and District Management
The platform shall maintain a central registry of Ghanaian regions and districts.

The initial dataset includes the known 261 districts, but application logic must not hardcode the number 261.

District data may include:

name;

code;

region;

status;

geometry;

District Readiness Score;

economic data;

data year;

population;

area.

The system shall support district geometry import, including GeoJSON.

District workflow:

draft
  ↓
under review
  ↓
published

Approval and rejection actions shall be authorized and audited.

9. Opportunity Data Requirements
Each investment opportunity shall support structured capture of, at minimum:

Administrative and Location
Region.

Metropolitan/Municipal/District.

Descriptive location.

Geographic point.

Identification
Opportunity/project title.

Sector.

Sub-sector.

Enterprise type.

Project status.

Description
Project description/overview.

Main objectives/rationale.

Critical success factors.

Competitive advantages.

Financial and Investment
Investment amount.

Currency.

USD support.

GHS support.

Investment structure.

ROI.

IRR.

NPV.

Payback period.

Projected revenues.

Investment Structure
Supported structures include:

Local Equity.

Foreign Equity.

Local Loan.

Foreign Loan.

Enterprise Type
Supported enterprise types include:

Sole Proprietorship.

Joint Venture/Partnership.

Private Limited Liability.

Public Limited Liability.

Cooperative Society.

Project Status
The source data form identifies:

Not Started.

In Progress.

On Hold.

The relationship between project status and the platform workflow lifecycle must remain explicit. They are not automatically interchangeable.

Supporting Documents
The platform shall support appropriate supporting documentation including:

Business Plan.

Technical Feasibility.

Market Feasibility.

Funding Structure.

Uploaded documents are untrusted input and must follow the security and storage requirements.

Contact Information
Contact name.

Organization/company.

Contact number.

Email address.

10. Search and Filtering
The platform shall support structured search and filtering.

The baseline cascading filter hierarchy is:

Region
  ↓
District
  ↓
Sector
  ↓
Investment Type
  ↓
Status

Variants shall be supported for:

district discovery;

opportunity discovery;

investment map;

investor-facing opportunity discovery.

Additional filters may include:

investment amount;

sorting;

location;

sector/sub-sector;

enterprise type;

project status.

Filters must be implemented through explicit allowlisted query fields.

11. Geospatial Requirements
The platform shall support PostGIS-backed geospatial capabilities.

Required capabilities include:

district boundaries;

opportunity locations;

nearby search;

bounding-box queries;

polygon queries;

spatial filtering;

GeoJSON output;

map viewport queries.

Spatial operations should be executed in PostgreSQL/PostGIS where appropriate.

12. Investment Map
The platform shall provide an interactive investment map.

The map shall support:

Ghana geography;

region/district boundaries where applicable;

opportunity locations;

clustering where appropriate;

cascading filters;

amount filtering;

sorting;

map viewport queries.

Map data shall respect investor/public visibility and authorization rules.

13. Authentication
The platform shall provide secure authentication appropriate to the user type.

The API shall use JWT authentication according to the approved authentication architecture.

The system shall support:

login;

logout/session controls where applicable;

password reset;

account verification;

email verification;

phone verification;

refresh/token lifecycle as specified by the API architecture;

MFA-ready architecture.

Authentication does not grant authorization.

Architecture decision, ADR-0005, reflected here so the requirements baseline matches the accepted ADR. Only an investor may self-register. GIPA personnel are invited by their district management and complete profile setup from that invitation. This is not a new decision of this reconciliation. It is the accepted ADR text, recorded here because the actors section did not previously state it.

14. Authorization
Authorization shall use explicit roles and permissions.

The system shall support:

users;

roles;

groups;

permissions;

district-level restrictions where applicable.

Authorization shall be enforced server-side.

Investor users shall only access information and actions permitted to verified investor accounts.

Management personnel shall only perform investor engagement workflow actions when their assigned permissions permit them.

15. Workflow and SLA
Workflows are explicit business processes.

Opportunity workflow:

draft
→ pending approval
→ approved
→ active
→ completed

Cancellation is an applicable terminal path.

District workflow:

draft
→ under review
→ published

Investor engagement/inquiry workflow shall be separately represented rather than incorrectly reusing the opportunity lifecycle.

Every material transition shall record:

actor;

source state;

target state;

timestamp;

reason/comment where required;

authorization context;

audit information;

SLA implications.

SLA rules shall be configurable.

SLA behavior must account for applicable:

start time;

due time;

business calendar;

assignment/reassignment;

pause/resume;

completion;

breach.

Unconfirmed SLA policy details remain documented in requirements/requirements-ambiguities.md.

16. Audit and Compliance
Material administrative and API actions shall be auditable.

Audit records should capture, where applicable:

actor;

action;

resource;

resource ID;

before state;

after state;

IP address;

user agent;

request ID;

timestamp;

authentication context;

source;

API/UI context.

Audit records shall be protected against ordinary modification.

Audit retention and export shall be configurable according to approved policy.

17. Notifications
The platform shall support configurable notifications.

Events may include:

investor registration;

verification;

opportunity submission;

opportunity approval;

opportunity rejection;

opportunity activation;

investor inquiry;

investor expression of interest;

management review;

SLA breach.

Email delivery may be asynchronous.

Notification configuration must not become tightly coupled to core workflow logic.

18. Reporting and Dashboards
The platform shall support dashboards and report templates including:

regional distribution;

sector investment;

opportunity timeline;

readiness distribution;

job creation;

infrastructure quality.

Administrative dashboards may provide additional operational information according to permissions.

Investor dashboards shall expose only investor-appropriate information.

19. Certificate and Verification Capability
The platform shall support certificate generation and anti-forgery verification as part of the agreed platform scope.

Certificates shall use a unique certificate code.

QR verification shall resolve to a controlled Laravel verification endpoint.

Authoritative certificate data shall include:

certificate code;

verification URL;

QR asset path.

Certificate lifecycle history shall support events such as:

ISSUED;

VERIFIED;

UPDATED;

SUSPENDED;

REVOKED.

The QR must be regenerable from the authoritative certificate code.

20. External Integrations
The broader platform architecture anticipates integration with relevant external systems, including:

ORC;

GIS/geospatial systems;

payment systems;

other approved government or third-party systems.

External integrations shall be implemented through explicit infrastructure boundaries and contracts.

Integration details must not leak external-system concerns into domain logic.

21. API
The platform shall expose core resources through a versioned REST API.

Baseline:

/api/v1/

The API shall support appropriate resources for:

authentication;

users;

groups;

roles/permissions;

regions;

districts;

sectors;

investment types;

opportunities;

investor profiles;

investor inquiries/interests;

workflow;

SLA;

notifications;

audit records;

dashboards/reports;

settings;

messaging;

certificate verification where applicable.

The API shall:

use JWT authentication where required;

enforce authorization;

validate all input;

use bounded pagination;

use allowlisted filtering and sorting;

provide stable error responses;

document contracts using OpenAPI;

preserve backward compatibility within an API version.

22. Admin UI
The administrative interface shall be implemented using:

Laravel;

Blade;

Alpine.js;

Tailwind CSS;

Vanilla JavaScript where appropriate.

The original TOR's reference to Django Admin is superseded and shall not result in Django being introduced.

The admin UI shall support appropriate management of:

users;

roles;

groups;

permissions;

regions;

districts;

sectors;

opportunities;

workflows;

inquiries;

investor engagement;

notifications;

audit;

settings;

maintenance;

reports;

certificates;

messaging.

Administrative actions must be authorized and audited where material.

23. Onboarding and Messaging
The platform shall support:

administrative onboarding tours;

user preferences;

controlled administrative user-to-user messaging.

Messaging must respect authorization and data visibility rules.

24. PWA
The platform shall support PWA capabilities including:

service worker;

installability;

offline fallback;

health checks.

Offline behavior must not bypass server-side authorization or permit unauthorized mutation of authoritative data.

25. Maintenance Mode
The system shall provide configurable maintenance mode.

Requirements include:

configurable public maintenance message;

CLI control;

admin UI control;

administrative access where authorized;

explicitly whitelisted IP access where approved;

appropriate API maintenance response.

Maintenance mode must fail safely.

26. Security Requirements
The platform shall:

use HTTPS in production;

avoid hardcoded secrets;

use environment/managed secret configuration;

apply secure headers;

implement CSP;

validate untrusted input;

protect against OWASP-relevant threats;

secure file uploads;

secure GeoJSON imports;

prevent broken access control;

prevent excessive data exposure;

protect authentication endpoints;

prevent mass assignment;

prevent insecure direct object access;

safely handle errors;

protect sensitive logs;

undergo security review and penetration testing before go-live.

27. Performance and Scalability
The platform shall target:

at least 95% of API requests responding within two seconds under agreed load;

responsive public-facing pages;

bounded API responses;

appropriate database indexes;

efficient geospatial queries;

horizontal scalability of application infrastructure;

architecture that can support growth without fundamental redesign.

Performance compliance must be demonstrated through measurement under an agreed workload.

28. Accessibility
Public and administrative interfaces shall target WCAG 2.1 Level AA where specified.

The UI shall consider:

semantic HTML;

keyboard access;

focus management;

labels;

accessible validation;

contrast;

screen-reader support;

reduced motion;

accessible forms;

alternatives for map-dependent information.

29. Data Integrity
The system shall:

validate data at capture;

preserve referential integrity;

use database constraints where appropriate;

use precise monetary storage;

preserve historical/audit information;

protect authoritative records;

avoid exposing unnecessary PII.

The number of districts must not be hardcoded as a business rule.

30. Deployment and Operations
The system shall be deployable using Docker.

The project shall provide:

documented environment variables;

staging configuration;

production configuration;

deployment procedures;

health checks;

logging;

monitoring;

backup procedures;

restoration procedures;

disaster recovery documentation;

operational runbooks.

Production credentials must never be used in local development.

31. Traceability
Major requirements shall maintain traceability between:

Requirement
    ↓
Module / Bounded Context
    ↓
ADR / Design
    ↓
Implementation
    ↓
Tests

Existing TOR identifiers shall remain usable, including:

FR-01 through FR-18;

NFR-01 through NFR-09.

Additional requirements introduced by confirmed client decisions shall receive explicit project requirement identifiers rather than being hidden as implementation details.

The investor registration, verification and protected opportunity-access flow shall be treated as an explicit current client requirement and traced accordingly.

32. Requirement Change Control
A new client requirement that changes business behavior shall be recorded explicitly.

Changes must not be silently embedded into code.

Where a change affects:

bounded contexts;

data ownership;

authorization;

workflow;

authentication;

API contracts;

persistence;

security boundaries;

deployment architecture;

the relevant ADR and/or requirements documentation shall be updated.
