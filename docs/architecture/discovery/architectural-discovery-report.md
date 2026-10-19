# IOMP Architectural Discovery Report

Status: discovery record, reconciled on 28 September 2026. This document does not approve a schema or an API payload. Where this report disagrees with a later client decision, the reconciliation report and the current requirements baseline govern.

Date: 28 September 2026. Reconciliation overlay: 28 September 2026.

Subject: the production IOMP platform to be built in `gipa_prod`.

Sources, in the precedence recorded by ADR-0013:

1. Explicit current client decision.
2. Updated client requirements in `.cursor/docs/requirements/project-requirements.md`.
3. Accepted ADRs in `.cursor/docs/architecture/adr/`.
4. Current approved functional requirements.
5. Original Terms of Reference, `IOMP_SYSTEM _POC.docx`, sections 1.0 through 3.3.
6. Assumptions. An assumption never overrides a higher source.

See `docs/architecture/discovery/reconciliation-report.md` for the finding-by-finding reconciliation. Statements below that are marked superseded must not be implemented.

## How to read this report

Every statement uses one of these labels.

| Label | Meaning |
| --- | --- |
| Documented requirement | Stated in the original Terms of Reference. |
| Confirmed client decision | An explicit current client decision. It governs where it is more specific than, or different from, the Terms of Reference. |
| Architecture decision | An accepted ADR. It is not a new decision made by this report. |
| Proposed design | A candidate or an engineering constraint that is not an accepted ADR and not a client decision. |
| Assumption | An inference this report refuses to treat as fact. |
| Unresolved question | A decision that no higher-priority source has made. Open items are listed in `.cursor/docs/requirements/requirements-ambiguities.md`. |
| Prototype observation | Something seen in `gipa_demo` while learning what the prototype explored. It does not define `gipa_prod`, and it is not a requirement. |
| Superseded | An earlier discovery conclusion that a later client decision or accepted ADR has replaced. |

`gipa_prod` is the system this discovery is for. The prototype exists so that scope already tried in a demo is not mistaken for the production requirement. This report does not cite prototype documents.

## Sources that govern gipa_prod

| Source | Role |
| --- | --- |
| `gipa_prod/.cursor/docs/requirements/project-requirements.md` | Current approved requirements baseline, including confirmed client decisions. |
| `gipa_prod/IOMP_SYSTEM _POC.docx` | Original Terms of Reference. Still binding where a higher-priority source has not superseded it. |
| `gipa_prod/.cursor/docs/architecture/adr/0001` through `0013` | Accepted architecture decisions. ADR-0011, ADR-0012, and ADR-0013 record investor access, engagement, and precedence. |
| `gipa_prod/.cursor/docs/requirements/implementation-decisions.md` | Stack and implementation constraints, including the investor visibility rule. |
| `gipa_prod/.cursor/docs/requirements/requirements-ambiguities.md` | Open business questions. Confirmed investor access is excluded from that list. |
| `gipa_prod/.cursor/rules/*.mdc` | Engineering constraints. Historical prototype-module paste is subordinate to the confirmed client decision in `05-requirements.mdc`. |
| `gipa_prod/app` | Laravel skeleton. Production domain behaviour is not implemented. |

Architecture decision, ADR-0013. Precedence is explicit client decision, updated client requirements, accepted ADRs, current approved requirements, original Terms of Reference, then assumptions. Empty-ADR language in earlier drafts of this report is superseded.

### Source hygiene

`gipa_prod/TechDoc.txt` and `gipa_prod/TechDoc.docx` describe an unrelated laundry SaaS. They are not IOMP requirements and must not be used for production behaviour, data, or integrations.

Confirmed client decision. Investor registration, email verification, phone verification, the investor dashboard, approved-and-active opportunity access, investor interest and inquiry, and authorized management review are in scope for `gipa_prod`. They are not FR-01 through FR-18, and they are not assumptions. Identifiers Client-INV-01 through Client-INV-06 trace them. Matchmaking and aftercare are named in the current requirements as the continuation of that journey. Their exact states and matching rules remain open in the ambiguities register. Certificates are in the current requirements and in ADR-0003, ADR-0008, and the implementation decisions. The AI assistant is conditional wording ("where separately implemented and approved") and is not part of the confirmed investor decision.

Superseded. The earlier conclusion that investor onboarding, certificates, and matchmaking must not be implemented because they are absent from the Terms of Reference.

### What the prototype is, and what it is not

Prototype observation. `gipa_demo` explores flows that the production requirements now confirm, and also explores behaviour the production requirements still leave open. The prototype does not set SLA durations, field visibility, verification expiry, or the engagement state machine. `gipa_prod` is not a copy of the prototype.

Production stack already recorded for `gipa_prod`, as proposed design in `00-project.mdc` and `implementation-decisions.md`:

- Laravel 12, PHP, Blade, Alpine.js, Tailwind CSS, Vite
- PostgreSQL and PostGIS
- Docker
- Versioned REST and JWT for the API
- OpenAPI documentation
- A modular monolith. Microservices are out of scope unless a later architecture decision requires them.

---

## 1. Functional requirements

Documented requirement. Section 3.3.A defines FR-01 to FR-18. Section 3.1 and the opening data-capture list add field-level and screen-level behaviour. Section 3.3 says the FR list is read together with section 3.1 and section 3.2.

### FR-01 Geography registry

Documented requirement. Maintain a central registry of Ghana's 261 districts and regions, including name, code, status, geometry, District Readiness Score (DRS), economic data, data year, population, and area.

Unresolved question. The Terms of Reference does not say whether DRS, economic data, data year, population, and area apply to regions as well as districts. The score is named District Readiness Score.

Proposed design, from `40-data.mdc`. Do not hardcode 261 in application logic. The initial dataset may contain 261 districts, and the model must allow later administrative change. That is an engineering constraint for `gipa_prod`. It does not change the initial registry size.

### FR-02 District boundaries and district workflow

Documented requirement. Import and load district boundaries. GeoJSON is the example format. District workflow: draft, then under review, then published, with approve and reject.

Unresolved question. The state after reject, whether reject requires a reason, who may submit, approve, or reject, and whether a published district can return to draft are not defined.

### FR-03 Opportunity data capture

Documented requirement. Full lifecycle management of investment opportunities and projects, with mandatory structured capture aligned to the official IOMP Data Form.

Documented requirement. The platform must capture, validate, store, report, and expose at least the following.

| Group | Fields named in the Terms of Reference |
| --- | --- |
| Administration and location | Region name; metropolitan, municipal, or district name; descriptive location; geospatial point |
| Identification | Title; sector and sub-sector; type of enterprise; project status |
| Description | Overview, including rationale and investor attractiveness; main objectives or rationale; critical success factors and competitive advantages |
| Finance | Investment amount required in USD and GHS; investment structure of local equity, foreign equity, local loan, and foreign loan |
| Finance, section 3.1 only | ROI, IRR, NPV, payback period, and projected revenues |
| Documents | Business plan, technical feasibility, market feasibility, funding structure |
| Contact | Name, organisation, phone, email |

Documented requirement. Enterprise types are sole proprietorship, joint venture or partnership, private limited liability, public limited liability, and cooperative society.

Documented requirement. Project status values are Not Started, In Progress, and On Hold.

Documented requirement. These data points are configurable, searchable, filterable, and manageable in the admin UI, and exposed through the versioned REST API. The data model must allow future extension without breaking backward compatibility.

Documented requirement, section 3.1. All listed fields are mandatory unless GIPA explicitly marks a field optional.

Unresolved question. The opening field list omits ROI, IRR, NPV, payback period, and projected revenues. Section 3.1 includes them. Mandatory-ness and units are unstated.

Unresolved question. Investment structure may mean one selected structure, several selected structures, or an amount against each of the four structures.

Unresolved question. Multi-currency support for USD and GHS may mean one amount in one currency, two amounts, or a convertible pair. Exchange-rate ownership is not stated.

Unresolved question. The official IOMP Data Form is referenced and is not attached. Optionality and validation limits may live on that form.

### FR-04 Opportunity workflow and SLA

Documented requirement. Lifecycle: draft, then pending approval, then approved, then active, then completed/cancelled. Actions: submit, approve, reject, and reassign. SLA tracking with breach notifications.

Unresolved question. "Completed/cancelled" may be one terminal label or two terminal states. Reject destination, the meaning of reassign, and the actor for each action are not defined.

Unresolved question. Project status and the lifecycle are both required. How they relate is not stated.

### FR-05 Cascading filters

Documented requirement. Cascading filters region, then district, then sector, then investment type, then status, across districts, opportunities, and the investment map.

Unresolved question. Investment type is not defined in the field list. The field list has enterprise type, sector, sub-sector, and investment structure. The API resource list also names investment types separately from sectors.

### FR-06 and FR-07 Geospatial discovery and map

Documented requirement. Nearby search, bounding-box queries, polygon queries, and GeoJSON for districts and opportunities.

Documented requirement. An interactive investment map of opportunities, filterable by region, district, sector, type, status, and amount, with sorting.

Documented requirement, section 3.0. PostGIS-backed district boundaries and opportunity locations.

### FR-08 Investor inquiries

Documented requirement. The Terms of Reference says public users submit inquiries linked to opportunities, GIPA staff track and respond, and email notifications are sent.

Confirmed client decision, Client-INV-05 and Client-INV-06. A verified investor reviews an approved and active opportunity and may express interest and/or submit an inquiry. Authorized GIPA management personnel review that engagement. Anonymous public submission of a protected-opportunity inquiry is superseded for protected opportunity data.

Architecture decision, ADR-0012. Inquiry and expression of interest are distinct concepts. Their state machines are not yet defined.

Unresolved question, AMB-06, AMB-07, and ADR-0012. Exact states, whether the two concepts share one workflow, assignment, and which parties are notified.

### FR-09 Identity and access

Documented requirement. Users, groups, and permissions. Role-based access. District-level assignment where applicable. JWT authentication for the API.

Documented requirement. Training examples name IT administrators, content managers, and district officers. These are examples, not a closed role catalogue.

### FR-10 Audit

Documented requirement. Log material admin and API actions. Configurable retention. Export.

Documented requirement, NFR-08. The trail is complete and tamper-evident.

### FR-11 Notifications

Documented requirement. Configurable email notifications for workflow and inquiry events, including submission, approval, rejection, and SLA breach. Templates are configurable.

### FR-12 Maintenance mode

Documented requirement. Public site offline with a configurable message. Admin access and an IP whitelist remain available. Controllable from the CLI and the admin UI. The API returns an appropriate status during maintenance.

Unresolved question. The HTTP status and response body are not specified.

### FR-13 Reporting and dashboard

Documented requirement. Dashboard API and report templates for regional distribution, sector investment, timeline, readiness distribution, job creation, and infrastructure quality.

Unresolved question. Job creation and infrastructure quality are report topics. They are not fields in the capture lists.

### FR-14 Admin UI

Documented requirement. Full admin for all core entities and settings. Sections: content management, user and access management, workflow and processes, and system administration. Custom forms, with reassign given as an example. Workflow actions.

Documented requirement, section 3.1 wording. "Django admin".

Proposed design, recorded in `implementation-decisions.md` and `00-project.mdc`. The `gipa_prod` administrative UI is Laravel server-rendered Blade, Alpine.js, and Tailwind CSS. Django is not introduced. This report records that written decision. It does not re-decide it. The Terms of Reference sentence still conflicts until the client confirms the replacement. See section 26.

### FR-15 API

Documented requirement. Versioned REST, example `/api/v1/`, for all core resources. OpenAPI 3.x. Swagger or ReDoc are documentation examples. JWT login and refresh. A documented backward-compatibility policy. Date-based or semantic versioning is given as an example, not as a single choice.

Documented requirement, section 3.1 resource list. Users, groups, regions, districts, sectors, investment types, opportunities, workflow settings, audit logs, dashboard, workflow templates, notifications, and messaging.

### FR-16 Onboarding and messaging

Documented requirement. The Terms of Reference uses onboarding for admin tours, user preferences, and admin user-to-user messaging.

Confirmed client decision. Investor registration, email verification, and phone verification are a separate onboarding flow. They do not replace admin tours and messaging, and admin tours do not satisfy investor verification.

Unresolved question, AMB-04 and AMB-23. Mandatory investor profile fields, and the messaging product rules, are still open. Investor registration itself is not an open question.

### FR-17 PWA

Documented requirement. Service worker, offline fallback page, installability, and health checks.

### FR-18 Settings

Documented requirement. Centralized admin-configurable settings for workflow, notifications, maintenance, audit, and onboarding.

### Public surfaces named beside the FR list

Documented requirement, section 3.0. Landing page, district list and detail, opportunity list and detail, investment map, opportunity compare, search, and investor inquiry submission.

Unresolved question. Search fields, compare rules, and how many opportunities can be compared are not specified.

### Contract delivery that is not runtime behaviour

Documented requirement, sections 2 and 3. The contractor produces a technical and delivery plan, automated tests, architecture and API documentation, deployment and operations guides, user and admin documentation, staging and production artefacts, knowledge transfer, training, UAT corrections, and a support period. Those obligations are restated under operational requirements. They are not domain modules.

---

## 2. Non-functional requirements

Documented requirement. Section 3.3.B, NFR-01 to NFR-09, read with section 3.2.

| ID | Documented requirement |
| --- | --- |
| NFR-01 | At least 95 percent of API requests respond within 2 seconds under an agreed load. Public pages load within acceptable industry-standard thresholds on a typical broadband connection. |
| NFR-02 | Growth in districts, opportunities, users, and concurrent sessions without an architectural redesign. Horizontal scaling of application and database components. |
| NFR-03 | OWASP-aware design. No hardcoded secrets. Environment-based configuration. HTTPS in production. Secure headers and content security policy. Input validation. Security review and penetration testing before go-live. Alignment with ISO/IEC 27001 principles. |
| NFR-04 | An agreed uptime target. 99.5 percent is an example, not a signed figure. Documented backup and disaster-recovery procedures. A graceful, clearly communicated maintenance mode. |
| NFR-05 | WCAG 2.1 Level AA for public and admin UIs where specified. Usable by IT administrators, content managers, and district officers. |
| NFR-06 | Modular, documented codebase. Automated unit, integration, and API tests with agreed coverage targets. Maintainable by GIPA or a later vendor. |
| NFR-07 | Containerized deployment. Free and open-source software where applicable for government use, actively maintained, with public patches. Versioned REST for third-party and government systems. |
| NFR-08 | Validation at capture. Referential integrity. Complete, tamper-evident audit of material admin and API actions. |
| NFR-09 | Responsive public interfaces on current major browsers and common device sizes. Offline fallback through the PWA. |

Unresolved question. "Where specified", "agreed load", "acceptable industry-standard thresholds", "agreed uptime", "agreed coverage", and the browser set are not enumerated.

Proposed design, from `00-project.mdc`. For `gipa_prod`, the engineering priority order is security, correctness, data integrity, architectural integrity, maintainability, testability, performance, scalability, accessibility, then developer productivity. That order is a project rule. The Terms of Reference lists the qualities and does not rank them this way.

---

## 3. Actors and user groups

Documented requirement.

| Actor | What the Terms of Reference says |
| --- | --- |
| GIPA | Client and institutional owner of the authoritative platform. |
| Public visitor | Documented requirement: landing and public information. Confirmed client decision: no automatic access to protected opportunity information. Whether a limited public summary exists is AMB-01, not a licence to publish the investor catalog. |
| GIPA staff | Track and respond to inquiries and perform admin and workflow work. The staff split is not a closed role list. |
| IT administrator | Example training audience and implied technical operator. |
| Content manager | Example training audience and implied content operator. |
| District officer | Example training audience and implied district-scoped operator. |
| Contractor | Designs, builds, tests, deploys, trains, and supports. Not a runtime actor. |
| Future vendor | Operates and extends the system after handover. |
| External system | Consumes the versioned API. No external system is named. |
| Relevant parties | Receive inquiry and workflow notifications. Not identified. |

Confirmed client decision. An investor is a registered user. Access to protected opportunities requires authentication plus successful email verification and phone verification, then the investor dashboard. An unverified account does not receive that access. Management review is a separate authorized actor class.

Architecture decision, ADR-0005. Only an investor registers directly. Staff join by invitation from district management. Password reset, session controls, and an MFA-ready architecture are in the current requirements baseline.

Unresolved question, AMB-14 and AMB-15. Which roles are district-scoped, which are regional or national, and whether assignment is time-bounded.

Superseded. The question of whether an investor may remain only an anonymous inquiry submitter.

---

## 4. Business capabilities

Documented requirement. These are the capabilities `gipa_prod` must provide. They are not a module diagram and they are not a licence to copy prototype modules.

1. Hold the authoritative registry of regions and districts, including readiness and geometry.
2. Import district boundaries.
3. Govern district publication through draft, review, and publish.
4. Capture investment opportunities on the IOMP data form, including finance, documents, and contacts.
5. Govern opportunity progression from draft through approval to an active state and a completed or cancelled end.
6. Track SLA and notify on breach.
7. Let a verified investor discover approved and active opportunities by search, cascading filters, comparison where still required, and map. Public visitors do not receive that catalog. District publication for public informational content remains a separate rule, and any public opportunity summary is AMB-01.
8. Answer geospatial questions for callers who are allowed to see the underlying records: nearby, bounding box, polygon, and GeoJSON.
9. Accept interest and inquiry from a verified investor, then management review, and notify by email.
10. Administer users, groups, permissions, and district-scoped access.
11. Authenticate API clients with JWT, including refresh.
12. Record, retain, and export an audit trail.
13. Send configurable email for workflow and inquiry events.
14. Take the public site offline safely, with an admin and IP exception.
15. Report regional distribution, sector investment, timeline, readiness, job creation, and infrastructure quality.
16. Expose core resources on a versioned, documented REST API.
17. Guide admin users with tours and preferences, and let them message each other.
18. Install and run as a PWA with an offline fallback and health checks.
19. Configure workflow, notification, maintenance, audit, and onboarding settings in one admin place.

Confirmed client decision, now in scope beyond the original FR list: investor onboarding through email and phone verification, the investor dashboard, approved-and-active opportunity access, interest and inquiry, management review, and the continuation into matchmaking and aftercare. Certificate and anti-forgery verification is in the current requirements baseline. Named external systems are anticipated, and their interface contracts remain AMB-20. The AI assistant is not approved for implementation by the investor decision; see AMB-29.

Superseded. The sentence that placed investor onboarding, certificates, and matchmaking out of production scope.

---

## 5. Candidate bounded contexts

Architecture decision, ADR-0001 and ADR-0002. `gipa_prod` is a modular monolith. The accepted initial contexts include Identity and Access, Investor Onboarding, Geography, Opportunity Management, Opportunity Engagement, Workflow and SLA, Geospatial, Certificates and Verification, Audit, Notifications, Reporting, Administration, Messaging, and External Integrations. The table below is the earlier candidate list. Where it conflicts with ADR-0002, ADR-0002 governs. This report does not create a further boundary decision.

| Candidate context | Capabilities it would own | Why it stays a candidate |
| --- | --- | --- |
| Geography | Regions, districts, boundary import, readiness, economic indicators, district publication | District workflow could instead live in a shared workflow context. |
| Opportunity catalogue | Opportunity identity, data-form fields, documents, contacts, project status, publication lifecycle | Finance and documents could be split out. The Terms of Reference does not require that split. |
| Discovery | Public search, filters, compare, and map read models | This can be a read model over Geography and Opportunity rather than an owner of facts. |
| Geospatial query | Nearby, bounding box, polygon, GeoJSON | This may be infrastructure inside Geography and Opportunity, not its own context. |
| Inquiry | Verified-investor interest and inquiry, then management review | Architecture decision, ADR-0002: Opportunity Engagement owns this. It is not only a public form. |
| Identity and access | Users, groups, permissions, district assignment, API authentication | The token mechanism is infrastructure. Roles and district scope are policy. |
| Workflow and SLA | Transition rules, assignment, clocks, breach | Architecture decision, ADR-0010. Opportunity, district, investor onboarding, and investor engagement stay separate workflows. |
| Notification | Templates and email delivery | Delivery is infrastructure. Template policy may be a supporting module. |
| Audit | Append-only record, retention, export | Cross-cutting. A separate owner stops other contexts from rewriting history. |
| Reporting | Dashboard and the named report templates | Reads other contexts. It must not become a second source of opportunity facts. |
| Platform operations | Maintenance mode, health, settings, PWA shell | Some settings, especially SLA, are domain policy rather than technical switches. |
| Admin collaboration | Tours, preferences, admin messaging | Small, and easy to over-model. |

Architecture decision. Investor Onboarding and Certificates and Verification are accepted contexts in ADR-0002. They are not optional prototype extras. Matchmaking and aftercare continue inside engagement and relationship handling. Their state machines are unresolved, not unauthorized. An AI Assistant module is named in ADR-0001 and is not approved to build by AMB-29.

Superseded. The question of whether workflow is only an informal status column. ADR-0010 makes workflow a first-class capability while keeping each business workflow separate.

---

## 6. Entities

Documented requirement. These are business concepts that have identity in the Terms of Reference. The list is not a table design.

| Entity | Identity in the Terms of Reference |
| --- | --- |
| Region | A named administrative region in the registry. |
| District | One of the 261 metropolitan, municipal, or district units, with code, status, and geometry. |
| Sector | A classification used in capture, filters, and reports. |
| Sub-sector | A classification under a sector. The hierarchy is implied and not defined. |
| Investment type | Named in filters and in the API resource list. Not defined. |
| Opportunity | The investment opportunity or project: title, location, narrative, finance, documents, contact, project status, and lifecycle. |
| Supporting document | A business plan, technical feasibility, market feasibility, or funding structure attached to an opportunity. |
| Contact | Name, organisation, phone, and email on an opportunity. Whether many contacts are allowed is unstated. |
| Inquiry | Confirmed client decision: a verified investor's inquiry about an approved and active opportunity. Architecture decision, ADR-0012: distinct from an expression of interest. |
| User | A person who administers or uses protected functions. |
| Group | A named access grouping. The Terms of Reference also requires role-based access and does not define the difference. |
| Permission | An authorization grant. |
| District assignment | The link that restricts a user to a district where that restriction applies. |
| Audit record | A retained record of a material admin or API action. |
| Notification template | Configurable email content for named events. |
| Workflow setting | Admin-configurable workflow configuration. |
| Workflow template | Named as an API resource. State names are also fixed in prose, so the template's freedom is unclear. |
| Setting | Central configuration for workflow, notifications, maintenance, audit, and onboarding. |
| Message | An admin user-to-user message. |
| Report | One of the named dashboard or report outputs. |
| Onboarding preference | An admin user's tour and preference state. |

Unresolved question. Is the opportunity contact the promoter, a district officer, or GIPA?

Unresolved question. Does district status mean workflow status, a separate active flag, or both?

Confirmed client decision. Investor, investor verification state, expression of interest, and inquiry are production concepts. Architecture decision. Certificate and certificate lifecycle events are production concepts. Prototype observation. Assistant documents and an SMS product are not established as production entities by the investor decision. SMS may be used as a notification channel where configured, per ADR-0010, and is not a substitute for the confirmed email and phone verification requirement.

---

## 7. Value objects

Documented requirement. The Terms of Reference requires these concepts to be validated. It does not require a particular programming type. Making them value objects is a proposed design, guided by `20-ddd.mdc`, and only where the type has real validation or behaviour.

| Concept | Why it is more than an unconstrained string |
| --- | --- |
| Money and currency | Amount required, USD and GHS, and reportable totals. NFR-08 requires validation at capture. |
| Investment structure component | Local equity, foreign equity, local loan, foreign loan. |
| Enterprise type | The closed list in the Terms of Reference. |
| Project status | Not Started, In Progress, On Hold. |
| Opportunity lifecycle state | The lifecycle list, with the completed/cancelled ambiguity in section 25. |
| District lifecycle state | Draft, under review, published. |
| Geographic point | Opportunity location as a point, separate from descriptive text. |
| District boundary | Geometry imported and queried in PostGIS. |
| Email address and phone number | Contact and notification targets. |
| Financial metric | ROI, IRR, NPV, payback period, projected revenue. Units are not defined. |
| District Readiness Score | A named score. Scale, formula, and author are not defined. |
| Data year | The year the economic or readiness figures describe. |

Proposed design, from `40-data.mdc`. Monetary values use precise numeric storage. Floating-point types are not used for authoritative money. Currency is explicit.

Assumption this report does not accept. That the readiness score is a 0–100 percentage. The Terms of Reference never states a scale.

---

## 8. Aggregates

Proposed design. The Terms of Reference does not define aggregate boundaries. `20-ddd.mdc` says an aggregate protects an invariant. It is not created for every table. The candidates below are consistency boundaries suggested by the requirements. They are not selected.

| Candidate aggregate | Invariant it would protect | Requirement basis |
| --- | --- | --- |
| District | A district belongs to one region. Code, geometry, and workflow status change under one publication policy. Boundary import replaces geometry only through a validated operation. | FR-01, FR-02 |
| Opportunity | Required data-form fields, project status, lifecycle, location, sector, district, finance, and contact stay consistent. Lifecycle state changes only through an allowed action. | FR-03, FR-04 |
| Opportunity document set | Each required document category is present before a transition that demands it, if the client confirms that gate. | FR-03, and an unresolved gate |
| Inquiry | An inquiry stays linked to one opportunity and keeps its own tracking history. | FR-08 |
| User access | Groups, permissions, and district assignments change as one authorization decision. | FR-09 |
| Audit entry | Once written, an entry is not edited on the ordinary path. | FR-10, NFR-08 |

Unresolved question. Which fields must be complete before submit, before approve, and before public visibility? The Terms of Reference says fields are mandatory unless GIPA marks them optional. It does not name the blocking transition.

Unresolved question. Must an opportunity point lie inside its district boundary? Enforcing that would be a proposed invariant, not a documented one.

---

## 9. Domain events

Proposed design. The Terms of Reference requires notifications, audit, and SLA breach handling when certain things happen. It does not name domain events. `20-ddd.mdc` lists examples and says not to emit an event for every method call. The names below are candidates for `gipa_prod`. They are not an approved catalogue.

| Candidate event | Documented situation |
| --- | --- |
| District submitted for review | A district leaves draft toward under review. The Terms of Reference names approve and reject. It implies an action into review and does not name that action. |
| District published | The approve outcome on the district workflow. |
| District rejected | The reject action. Destination state unresolved. |
| Opportunity submitted | The submit action. |
| Opportunity approved | The approve action. |
| Opportunity rejected | The reject action. |
| Opportunity reassigned | The reassign action. |
| Opportunity activated, completed, or cancelled | The lifecycle names these conditions. A separate action for each is not named. |
| Inquiry submitted | Public submission. |
| Inquiry responded | Staff response. The response itself is unspecified. |
| SLA breached | Breach notification. |

Documented requirement. Email is required for submission, approval, rejection, SLA breach, and inquiry events. Audit is required for material admin and API actions. Those are required reactions. The event mechanism is a proposed design.

Proposed design, from `50-workflows.mdc`. Notifications react to meaningful events. Transition logic is not tied to a mail or SMS provider. Email may be queued. SMS is not a Terms of Reference channel for `gipa_prod`.

---

## 10. Workflows

### District publication

Documented requirement.

```text
draft → under review → published
```

Actions named: approve and reject.

Unresolved question. Reject destination, reasons, resubmission, and unpublish are unspecified. How a district moves from draft into review is implied by the draft state and is not named as an action in FR-02.

### Opportunity lifecycle

Documented requirement.

```text
draft → pending approval → approved → active → completed/cancelled
```

Actions named: submit, approve, reject, and reassign.

Unresolved question. Whether completed and cancelled are separate states. Whether reject returns to draft. Whether reassign changes state. Whether movement from approved to active is automatic or a separate action. Whether cancellation is allowed from every non-terminal state. Whether a completed opportunity can reopen.

Proposed design, from `50-workflows.mdc`. Treat cancellation as a terminal path beside completed, and do not invent the reject destination. That rule is a caution for `gipa_prod`. It is not a client-approved state machine. `50-workflows.mdc` also requires each transition to record source state, target state, actor, authorization, timestamp, reason where required, audit, and SLA effect. That transition record is a proposed design.

### Project status

Documented requirement. A second status on the opportunity: Not Started, In Progress, or On Hold.

Unresolved question. Who may change it, whether it is independent of the lifecycle, and whether On Hold pauses the SLA.

### Inquiry handling

Documented requirement. Submit, store, track, respond, and notify. There is no documented inquiry state machine.

### Admin messaging

Documented requirement. Admin users can message each other. Thread shape, retention, and moderation are not specified.

### Settings and maintenance

Documented requirement. An operator can change workflow, notification, audit, onboarding, and maintenance settings, and can enable maintenance from the CLI or the admin UI.

---

## 11. SLA requirements

Documented requirement.

- Opportunity work has SLA tracking and breach notifications.
- Content and workflow actions are resolved in a timely way, with monitoring and breach alerts.
- Email is sent on SLA breach.
- Workflow settings are admin-configurable.
- The admin experience includes SLA tracking.

Unresolved question. All of the following are absent:

- Duration for each state and each entity.
- Whether the clock starts at submit, at assignment, or on entering a state.
- Business calendar, weekends, and public holidays.
- Pause while On Hold, during reassignment, or during maintenance.
- Warning threshold before breach.
- Who is notified, and whether notification repeats.
- Whether district review has an SLA. The detailed SLA sentence is attached to opportunities. Section 3.2 speaks of workflow actions generally.
- Any consequence of breach other than notification. Automatic escalation and automatic approval are not required.

Proposed design, from `50-workflows.mdc`. SLA rules in `gipa_prod` are configurable and are not hardcoded in controllers. Calculations consider start, due time, calendar, reassignment, pause and resume, completion, and breach. Where those rules are unconfirmed, they stay flagged. This section flags them.

Assumption this report does not accept. Any duration observed in the prototype, including a 72-hour default. Prototype defaults are not production SLA policy.

---

## 12. Authorization boundaries

Documented requirement.

| Boundary | What `gipa_prod` must enforce |
| --- | --- |
| Public visitor | Explicitly public pages only. Protected opportunity list, detail, map points, search hits, and inquiry submission are not public. |
| Role and group | Role-based access with users, groups, and permissions. |
| District scope | Where district assignment applies, a role alone does not grant every district. |
| API | JWT for protected API access, including login and refresh. Confirmed client decision: protected opportunity resources are not anonymous. |
| Maintenance | Admins and whitelisted IPs can pass. Other callers receive the maintenance experience. |
| Workflow actions | Submit, approve, reject, and reassign are controlled actions. The actor for each action is not named. |

Proposed design, from `30-security.mdc` and `60-api.mdc`. Every protected operation is authorized on the server. Hidden UI is not authorization. District scope is checked on the server. The security rule names Spatie as the permissions package, configurable in the admin UI. Package choice is a proposed design. The Terms of Reference does not name a package.

Proposed design, from `70-frontend.mdc`. Public presentation and admin presentation stay separate so administrative actions are not exposed on the public UI.

Confirmed client decision. Protected opportunity information is not public merely because the opportunity is approved or active. Unverified investors do not receive it. Verified investors receive approved and active opportunities.

Unresolved question, AMB-01 and AMB-31. Which fields, if any, a public visitor may see, and which fields on an approved and active opportunity a verified investor may see. Contact phone and email remain personal data.

Unresolved question. May a district officer approve an opportunity in an assigned district, or only prepare it? No segregation-of-duties rule is stated.

Prototype observation. The prototype's permission names and its choice of which catalog routes are anonymous are not the production authorization matrix.

---

## 13. Data ownership

Documented requirement. The platform is the authoritative system for investment opportunities and projects across Ghana's regions and districts. GIPA operates it.

Proposed design. Ownership below is a candidate assignment for a later decision. It is not approved.

| Data | Candidate owner | Notes |
| --- | --- | --- |
| Region and district registry, geometry, readiness, economic figures, data year, population, area | Geography | External boundary files are inputs. They are not a second live authority unless the client says so. |
| Opportunity data-form content, lifecycle, project status | Opportunity catalogue | Field collection is input. After capture, the platform is the system of record. |
| Supporting documents | Opportunity catalogue for the association; file storage for the bytes | ADR-0008 is empty. The storage product is not chosen. |
| Inquiry content | Inquiry | Personal data of the inquirer. |
| Users, groups, permissions, district assignment | Identity and access | |
| Audit trail | Audit | Other contexts emit facts. They do not revise history. |
| Notification templates and delivery outcome | Notification | |
| Report outputs | Reporting | Derived. Not a second master of amounts or counts. |
| Settings | Platform operations | Workflow durations, once configured, are policy consumed by workflow. |

Unresolved question. Who may correct a published opportunity, and does correction create a new public version? Audit of material changes is required. A public version history is not.

Unresolved question. Are readiness, economic data, job-creation figures, and infrastructure quality supplied by districts, by GIPA centrally, or by an external statistical source?

Proposed design, from `40-data.mdc`. Do not duplicate authoritative data without a reason. Collect only personal data required for the documented purpose. Do not place personal data on public endpoints by default.

---

## 14. Geospatial requirements

Documented requirement.

- District geometry is stored with PostGIS. Section 3.0 requires PostGIS-backed district boundaries and opportunity locations. Section 3.1 gives PostGIS as the example for geometry.
- Opportunity location is both descriptive text and a geospatial point.
- Boundary import uses GeoJSON as the example format.
- Nearby search, bounding-box query, and polygon query.
- GeoJSON output for districts and opportunities.
- Cascading filters on districts, opportunities, and the map.
- Map filters for region, district, sector, type, status, and amount, plus sort.
- The map is an interactive feature. Confirmed client decision: opportunity points follow the same visibility rule as lists and detail. A public visitor does not receive protected opportunity points. A verified investor receives points for opportunities that are both approved and active. Architecture decision, ADR-0007: the map must not bypass that rule.

Unresolved question.

- Coordinate reference system. WGS 84 is a common expectation and is not stated.
- Geometry types accepted on import.
- The meaning of nearby: radius, unit, default, and maximum.
- Polygon query input shape.
- Whether queries return only published records.
- Whether the map draws district boundaries, opportunity points, or both. The map sentence says opportunities. Boundary display is implied by district geometry and is not explicit in that sentence.
- Maximum result size for a map request.
- The official boundary file is not attached.
- Whether an opportunity point must fall inside the selected district.

Proposed design, from `40-data.mdc`, `60-api.mdc`, and `30-security.mdc`, for `gipa_prod`. Use PostGIS for spatial predicates, with GiST indexes and GeoJSON serialization in the database. Do not pull large geometries into PHP to do work PostGIS can do. Treat imported GeoJSON as untrusted input: structure, geometry type, coordinate validity, coordinate-reference expectation, size, and polygon validity. Bound result sizes and validate coordinates. `70-frontend.mdc` sketches a viewport query. That sketch is a proposed design, not a selected map library decision. ADR-0007 is empty.

Prototype observation. The prototype is not the production geospatial implementation. `gipa_prod` is required to use PostgreSQL and PostGIS. A prototype map does not satisfy that requirement.

Assumption this report does not accept. That a client-supplied latitude and longitude may be stored without a stated containment rule.

---

## 15. API resources

Documented requirement. REST under a versioned prefix, with `/api/v1/` as the example. JWT login and refresh. OpenAPI 3.x. A written backward-compatibility policy. Date-based or semantic versioning, left as a choice.

Documented requirement. Resources named in section 3.1:

- Users
- Groups
- Regions
- Districts
- Sectors
- Investment types
- Opportunities
- Workflow settings
- Audit logs
- Dashboard
- Workflow templates
- Notifications
- Messaging

Documented requirement, from features rather than that list:

- Cascading filter options
- Geospatial queries: nearby, bounding box, polygon, and GeoJSON
- Investor inquiry and expression of interest, for a verified investor, on an approved and active opportunity

Proposed design, from `60-api.mdc`. For `gipa_prod`, collection responses are paginated, filters and sorts are allowlisted, errors are stable and do not leak internals, and the OpenAPI description stays aligned with the implementation. Those are contract qualities. They are not the payload design.

Confirmed client decision. Opportunity collection, detail, geospatial, and engagement endpoints that expose protected opportunity data require a verified investor or an authorized internal user. A valid login without email and phone verification is not enough.

Unresolved question, AMB-01. Whether any reduced public opportunity representation exists. Staff-only resources, including audit export and settings, are not public.

Unresolved question. Date-based versus semantic versioning is explicitly open.

Prototype observation. Routes present in the prototype are not the production API surface. `gipa_prod` still has to define the resource list above, including users, groups, workflow settings, audit logs, dashboard, workflow templates, notifications, and messaging.

---

## 16. External integrations

Documented requirement.

- Email delivery for workflow and inquiry notifications. No vendor is named.
- GeoJSON boundary import. The file source is not named.
- A versioned REST API so third-party and government systems can integrate. No system is named.
- JWT as the API authentication mechanism.
- Container hosting on free and open-source software where applicable. No cloud vendor is named.
- Penetration testing before go-live. The tester is not named.

Not a documented requirement for `gipa_prod`:

| Item | Where it was seen | Production status |
| --- | --- | --- |
| ORC, payments | Pasted into production rules | Absent from the Terms of Reference |
| A live external GIS product | Pasted into production rules | The requirement is PostGIS inside the platform |
| SMS | Prototype, and a decoupling mention in workflow rules | The required channel is email |
| An assistant model provider | Prototype and rule paste | Absent from the Terms of Reference |
| Certificate signing or a hardware security module | Prototype and rule paste | Absent from the Terms of Reference |

Proposed design. Mail and other slow work can be queued, per `85-performance.mdc`. If the server fetches a URL, `30-security.mdc` treats that fetch as SSRF-sensitive.

Unresolved question. If the client later wants a named registrar, payment, SMS, or assistant integration, each one needs a purpose, a data contract, and an owner. This report does not add them to `gipa_prod`.

---

## 17. Audit requirements

Documented requirement.

- Log material admin actions and material API actions.
- Retention is configurable.
- Export is required.
- The trail is complete and tamper-evident.
- Capture is validated and referential integrity holds.

Unresolved question.

- "Material" is not defined.
- Tamper-evident is not given a mechanism: restricted database permissions, a hash chain, write-once storage, or another control.
- Retention period, export format, and who may export are not set.
- Whether reads are audited, or only changes.
- Confirmed client decision. Investor registration, email verification, phone verification, interest, and inquiry are material and auditable, per ADR-0009 and ADR-0011. They are not anonymous public writes.

Proposed design, from `50-workflows.mdc` and `40-data.mdc`. A workflow audit record may include actor, action, resource, resource identifier, before, after, IP address, user agent, request identifier, timestamp, authentication context, and source channel. Audit rows are append-oriented and protected from ordinary modification. `30-security.mdc` forbids logging passwords, tokens, and unnecessary personal data. This payload is a candidate. It is stronger than the Terms of Reference sentence, and it is not approved here. ADR-0009 is empty.

---

## 18. Notification requirements

Documented requirement.

- The channel is email.
- Templates are configurable.
- Named events include submission, approval, rejection, SLA breach, and inquiry events.
- Notification settings live in the admin UI.
- Inquiry notifications go to relevant parties.

Unresolved question.

- Sender identity, reply address, and language.
- Whether the opportunity contact, the assignee, the submitter, and a GIPA mailbox all receive mail.
- Whether the inquirer receives a receipt.
- Whether a rejection message includes the reason.
- Immediate send versus a digest.

Proposed design. Queue email so a workflow transaction does not depend on the mail provider. Do not bind transition code to a vendor. SMS is not part of the production notification requirement.

---

## 19. Reporting requirements

Documented requirement. A dashboard API and report templates for:

| Report | Captured input in the Terms of Reference |
| --- | --- |
| Regional distribution | Region and district on each opportunity |
| Sector investment | Sector, sub-sector, and investment amount |
| Timeline | No date field is named beyond workflow states |
| Readiness distribution | District Readiness Score |
| Job creation | No job-creation field is named |
| Infrastructure quality | No infrastructure field is named on the data form |

Unresolved question. Audience, refresh, export format, and whether figures are counts, sums, or both.

Unresolved question. The timeline axis is unstated: submission, approval, project start, or data year.

Unresolved question. Job creation and infrastructure quality need a source field, a unit, and an owner before `gipa_prod` can specify those reports.

Proposed design. Reports read an agreed population of records and do not create a second authoritative copy of opportunity amounts. Which statuses enter a report is an unresolved question.

---

## 20. PWA requirements

Documented requirement. FR-17 and NFR-09.

- A service worker.
- An offline fallback page.
- Installability.
- Health checks.
- Public pages remain usable on current major browsers and common device sizes.
- The offline behaviour required is the fallback. The Terms of Reference does not require offline editing.

Unresolved question.

- Which routes may be cached.
- Whether opportunity data, documents, and map tiles are cached.
- Health-check path, payload, and authentication.
- Install name, icon, and display mode.

Proposed design, from `90-operations.mdc`. Health output must not disclose sensitive information. Inquiry submission and workflow actions are not implied to work offline. The Terms of Reference does not say they do.

---

## 21. Operational requirements

Documented requirement.

| Area | Terms of Reference |
| --- | --- |
| Environments | Staging and production artefacts. The contractor provides the hosting environment used for contracted deployment and test. |
| Containers | Docker, with documented environment variables and deployment steps. |
| Configuration | Environment-based. No hardcoded secrets. |
| HTTPS | Enforced in production. |
| Maintenance | CLI and admin controls, a public message, an admin and IP whitelist, and a safe API status. |
| Backup and recovery | Documented backup and disaster-recovery procedures. The 99.5 percent uptime figure is an example. |
| Health | Health checks are required with the PWA capability. |
| SLA operations | Monitoring and breach alerts. |
| Security operations | Review and penetration test before go-live, aligned to ISO/IEC 27001 principles. |
| Handover | Code, repositories, documentation, runbooks, and knowledge transfer. |
| Support | A warranty period to be agreed. Twelve months is the example. Bug fixes, patches, and minor enhancements. Procedures so GIPA can maintain the system after warranty. |
| Training | A plan agreed with the client, for IT administrators, content managers, and district officers, on-site or remote, with user guides. |
| Acceptance | User acceptance testing, then corrections from that testing and from training. |
| Data load | Import and configuration of data as agreed during deployment and test. |
| Method | Rational Unified Process, Agile such as Scrum, or a combination, described in the technical proposal. |
| Plans | Implementation schedule, change management, communication, stakeholder engagement, and risk management. |
| Open-source hosting | Free and open-source hosting software where applicable, actively maintained, with public patches. |

Unresolved question. Backup frequency, retention, encryption, restore-test interval, recovery time objective, and recovery point objective are not numeric. Who operates production after handover is not named.

Proposed design, from `90-operations.mdc`, for `gipa_prod`. Separate local, test, staging, and production. Logs are useful and free of secrets. Monitor errors, latency, queues, database, storage, authentication failures, and security events. Define backup and restore tests before calling a backup reliable. Document recovery order. Maintenance fails safe. Production deployment and database changes need human approval. These practices do not fill the missing numeric targets.

---

## 22. Security requirements

Documented requirement.

- No hardcoded secrets. Environment configuration.
- HTTPS in production.
- Content security policy and secure headers.
- Input validation.
- OWASP-aware design.
- Security review and penetration testing before go-live, with remediation.
- ISO/IEC 27001 principles. The Terms of Reference also says testing is done so that there are no security vulnerabilities. The acceptance bar for residual findings is not defined.
- JWT for the API, including refresh.
- Role-based access and district scope where applicable.
- Tamper-evident audit.
- Maintained open-source components where open-source software is used.
- Compliance with relevant legislation. The legislation is not named.

Proposed design, from `30-security.mdc`, for `gipa_prod`. Treat HTTP input, uploads, GeoJSON, external responses, and job payloads as untrusted. Use an established JWT library. Do not write custom cryptography. Assign request fields explicitly. Check upload type, size, name, storage, and access, and do not execute uploads. Keep secrets and unnecessary personal data out of logs. Use CSRF protection, safe cookies, and escaped output on the web UI. Production errors do not expose stack traces, SQL, filesystem paths, or secrets. Control server-side URL fetch against SSRF. These rules align with NFR-03. They are not a completed threat model. ADR-0005 and ADR-0006 are empty.

Unresolved question. Which data-protection law applies to contact and inquirer data, what retention applies, and whether the inquiry form needs consent.

Unresolved question. The browser session design for the admin UI. The Terms of Reference specifies JWT for the API and does not specify the admin session.

Unresolved question. Whether opportunity documents are public, staff-only, or commercially sensitive. They must be stored and are also API-exposed in general terms. Visibility is not stated.

---

## 23. Performance requirements

Documented requirement. NFR-01 and NFR-02.

- 95 percent of API requests respond within 2 seconds under an agreed load.
- Public pages meet an unspecified industry-standard load time on a typical broadband connection.
- Growth in districts, opportunities, users, and sessions must not require an architectural redesign.
- Application and database components must be able to scale horizontally.

Unresolved question. The load profile is not agreed in the Terms of Reference. Concurrent users, opportunity volume, map request rate, and the page-load budget are missing. Without them, NFR-01 cannot be tested.

Proposed design, from `85-performance.mdc`, for `gipa_prod`. Paginate large collections. Allowlist filters and sorts. Index for real access paths, including spatial indexes. Avoid unbounded queries and N+1 query patterns. Queue mail, imports, large documents, and heavy reports. Do not keep shared business state only in process memory if more than one application instance must serve it. Do not claim the 2-second target without a measured workload.

---

## 24. Accessibility requirements

Documented requirement. NFR-05, section 3.2, and section 2.

- WCAG 2.1 Level AA for public and admin interfaces where specified.
- Usable by IT administrators, content managers, and district officers.
- Responsive layout on common device sizes.
- "Where specified" does not attach a page-by-page exception list. No exception list is in the Terms of Reference.

Unresolved question. Whether the map must have a non-map equivalent that exposes the same opportunity data. The Terms of Reference requires both the map and Level AA, and it does not exempt the map.

Proposed design, from `70-frontend.mdc`. Semantic HTML, keyboard access, focus management, labels, accessible errors, contrast, screen-reader behaviour, reduced motion, and accessible forms. Server-side validation remains the authority. A non-map alternative for map content should be in the accessibility test plan. These are implementation criteria consistent with NFR-05, not extra product features.

---

## 25. Ambiguous requirements

Each item is a documented requirement whose meaning is incomplete. This report does not resolve any of them.

1. Completed/cancelled may be one lifecycle state or two terminal states.
2. Project status and the lifecycle are both required, and their interaction is unstated.
3. Investment type is used in filters and the API list, and it is not defined against enterprise type, sector, or investment structure.
4. Investment structure may be one choice, many choices, or an amount per component.
5. USD and GHS may be alternative currencies or a pair, and the exchange rate has no owner.
6. ROI, IRR, NPV, payback period, and projected revenues appear in section 3.1 and not in the opening field list.
7. Listed fields are mandatory unless GIPA marks them optional, and the transition that enforces completeness is unstated.
8. FR-01 attaches one attribute list to districts and regions. A district readiness score on a region is unclear.
9. District reject has no destination state.
10. Opportunity reject and reassign have no destination and no SLA effect.
11. District review is required, and the action that leaves draft is not named in FR-02.
12. Superseded for investor access. Verified investors see opportunities that are both approved and active. Completed, cancelled, draft, and pending approval are not investor-visible under that decision. Whether any public summary exists remains AMB-01.
13. Groups and roles are both required, and the difference is unstated.
14. Resolved as two different uses of the word. FR-16 remains admin tours, preferences, and messaging. Investor registration and verification are Client-INV-01 through Client-INV-03, not a misreading of FR-16.
15. Workflow templates are an API resource, while state names are fixed in prose.
16. Material audit and tamper-evident are required without a mechanism or an event list.
17. Notification recipients are "relevant parties".
18. Job creation and infrastructure quality are reports without captured fields.
19. The timeline report has no time axis.
20. Nearby, polygon, and map-layer contracts are unspecified.
21. API versioning is date-based or semantic.
22. Django admin is a product name in section 3.1. Production rules treat it as superseded. See section 26.
23. WCAG applies "where specified", and no exception list exists.
24. Load, page-load time, and uptime are examples or items still to be agreed.
25. Coverage targets are to be agreed, and they are not agreed.
26. Relevant legislation is unnamed.
27. The registry contains 261 districts. Whether the software must reject a later district is unstated. The data rule proposes that the count not be hardcoded.
28. Compare and search are required without rules.
29. Supporting documents have no public versus staff visibility rule.
30. The health check has no contract.

---

## 26. Contradictory requirements

These are conflicts between texts. This report does not pick a winner, except to record a decision that is already written, and even then it stays a proposed design until the client confirms it where the Terms of Reference still disagrees.

### Django admin versus the Laravel decision

Documented requirement, section 3.1. Django admin for core entities and settings.

Proposed design, already written in `implementation-decisions.md` and `00-project.mdc`. `gipa_prod` uses Laravel 12. The admin UI uses Blade, Alpine.js, and Tailwind CSS. Django is not introduced. The rules say some Terms of Reference implementation wording was superseded by later client communication. The decision file does not attach that communication.

Unresolved question. Confirm in writing that GIPA accepts the Laravel admin UI in place of Django admin. Until that confirmation is on record, the texts conflict.

### Fixed workflows versus configurable workflow templates

Documented requirement. Opportunity and district state names are specified. Documented requirement. Workflow settings and workflow templates are also required.

These can be reconciled if templates configure durations, assignees, and notifications while states stay fixed. They conflict if templates can rename or skip states. The Terms of Reference does not choose.

### Mandatory data form versus configurable fields

Documented requirement. The listed fields are mandatory unless GIPA marks them optional. Documented requirement. The same data points are configurable. Configurable may mean reference data managed by an administrator, or a form that an administrator can reshape. Those readings differ.

### Two status vocabularies on one opportunity

Documented requirement. Project status is Not Started, In Progress, or On Hold. Documented requirement. Lifecycle status runs from draft through completed/cancelled. Nothing says they are the same field. Collapsing them would drop one requirement.

### Public API exposure versus least exposure of personal data

Documented requirement. Contact details belong to the opportunity, and core resources are exposed on the API. Confirmed client decision. Protected opportunity data is not exposed to anonymous visitors. Unresolved question, AMB-31. Which of those contact fields a verified investor may see is still open. Publishing them to anonymous clients would contradict the investor access decision.

### "No security vulnerabilities" versus a penetration test

Documented requirement. Penetration testing is performed so that there are no security vulnerabilities. Documented requirement. A review happens before go-live. A test that finds issues is part of that review. The residual-risk bar is not defined. The absolute sentence and a real test process pull apart.

### Horizontal scaling versus an unspecified host

Documented requirement, NFR-02. Application and database scale horizontally. Documented requirement. The contractor deploys into the hosting environment it provides. These agree if that environment can run more than one application instance and a scalable database. They conflict if the accepted host is a single machine with local disk. The host is not described.

### Pasted scope versus the Terms of Reference

Superseded as a reason to exclude investor access. The Terms of Reference feature list does not contain the later client decision. ADR-0013 says the client decision governs. Historical prototype-module text in the Cursor rules that still says "public opportunity list" and "public inquiry form" is stale relative to that decision and is corrected in those rules. ORC, payments, and the AI assistant are not made mandatory by the investor decision. Their open points remain AMB-20 and AMB-29.

### Audit detail in the rules versus the shorter audit sentence

The workflow rule proposes before-and-after images and request metadata. The Terms of Reference requires a complete tamper-evident log and does not require a full row image. This is an elaboration unless the stored before-and-after image keeps personal data the same rules say to minimise. Resolve that when the audit payload is decided. ADR-0009 does not decide it.

---

## 27. Requirements requiring client clarification

These decisions block a safe implementation plan for `gipa_prod`. This report does not supply suggested answers.

### Scope

1. Superseded. Do not ask the client to remove investor registration, email verification, phone verification, the investor dashboard, approved-and-active access, interest, inquiry, or management review. Those are confirmed. Remaining scope questions are the open items in the ambiguities register, including aftercare states, the matching algorithm, certificate business rules, external interface contracts, and whether the assistant is approved to build.
2. Provide the official IOMP Data Form, including optional fields, units, and validation limits.
3. Confirm whether ROI, IRR, NPV, payback period, and projected revenues are mandatory, and in what units.
4. Provide the authoritative region list, district list, boundary file, and coordinate reference system.
5. Confirm whether the district count may change after go-live.

### Workflow and SLA

6. Define the opportunity state machine, including reject, cancel, complete, and the path from approved to active.
7. Define the district state machine, including reject and whether unpublish exists.
8. Define who may submit, approve, reject, reassign, and move an opportunity through later states, and whether the approver must differ from the submitter.
9. Define how project status relates to lifecycle status.
10. Set SLA durations, clock start, calendar, pause rules, warnings, recipients, and repeat policy for opportunities, and state whether districts have an SLA.
11. Define which states are public and which enter each report.

### Data meaning

12. Define investment type, or retire that term in favour of enterprise type, sector, or investment structure.
13. Define the investment-structure model.
14. Define USD and GHS rules.
15. Define the readiness-score scale, formula, data year, and who updates it.
16. Define the fields behind job creation, infrastructure quality, and the timeline report.
17. Define whether an opportunity point must lie inside its district.
18. Define document visibility.
19. Define inquiry states, assignment, response, and notification recipients.
20. Define admin messaging parties and retention.

### Access, privacy, and API

21. Provide the role and permission matrix, including district assignment.
22. State whether investors need accounts.
23. State which API resources are public.
24. Choose date-based or semantic versioning, and the compatibility period.
25. State the personal-data basis, consent needs, and retention for contacts and inquirers.
26. Confirm that the `gipa_prod` admin UI is the Laravel UI in the implementation decision, and that Django is not required.

### Quality bar

27. Set the performance workload and the public page-load budget.
28. Set the availability target, recovery time objective, and recovery point objective.
29. Set backup frequency, retention, and restore-test expectations.
30. Set the coverage target and the penetration-test acceptance bar.
31. Set any WCAG exception, especially for the map.
32. Name the legislation and security standard the build will be audited against.
33. Name the production host and who operates it after warranty.
34. Set the warranty length. Twelve months is only the example.
35. Set the maintenance-mode HTTP contract.

---

## Decisions this report does not make

- It does not choose the bounded-context map in section 5.
- It does not choose aggregate boundaries, event names, or tables.
- It does not choose an API payload, a versioning scheme, or a public field list.
- It does not set SLA numbers, role matrices, or report formulas.
- It does not copy prototype programmes into `gipa_prod`.
- It does not treat empty ADR files as decisions.
- It does not treat the laundry technical document as an IOMP source.

Accepted ADRs 0001 through 0013 now exist. Do not treat them as empty placeholders. New architecture decisions are still required only for the open items in the ambiguities register, and they must be written as ADRs rather than assumed during implementation. The reconciliation report lists those items.
