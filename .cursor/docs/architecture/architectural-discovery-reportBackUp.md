# IOMP Architectural Discovery Report

Status: discovery only. This document does not approve an architecture, a schema, an API contract, or a workflow policy.

Date: 28 September 2026

Primary source: `gipa_prod/IOMP_SYSTEM _POC.docx` (GIPA Terms of Reference for the Investment Opportunities Mapping Project platform). The extracted text ends at section 3.3, non-functional requirement NFR-09. No later TOR section was present in the document body.

## How to read this report

Every statement below uses one of these labels.

| Label | Meaning |
| --- | --- |
| Documented requirement | Stated in the TOR. |
| Proposed design | A design already written in `gipa_prod/.cursor` rules or `implementation-decisions.md`, or a candidate model offered here for later decision. It is not a requirement and it is not approved by this report. |
| Demo observation | Behaviour or structure found in `gipa_demo/`. It shows what a prototype did. It is not a requirement. |
| Assumption | An inference this report refuses to treat as fact. |
| Unresolved question | A decision the TOR does not make and that the client needs to confirm. |

This report does not select among candidate bounded contexts, aggregates, or API shapes. Where a candidate is listed, it is a proposed design offered for review.

## Sources reviewed

| Source | Role in this report |
| --- | --- |
| `gipa_prod/IOMP_SYSTEM _POC.docx` | Authoritative business requirements. |
| `gipa_demo/` and `gipa_demo/system-component.md` | Prototype evidence. The demo is a Laravel 12 application. Its own status file says checked items are implemented and tested. |
| `gipa_prod/.cursor/rules/*.mdc` | Engineering constraints and some scope text that is not in the TOR. |
| `gipa_prod/.cursor/docs/requirements/implementation-decisions.md` | Records a stack decision and the supersession of the TOR's Django admin wording. |
| `gipa_prod/.cursor/docs/architecture/adr/0001` through `0010` | Placeholder files. Each file contains only its own filename. They are not approved decision records. |
| `gipa_prod/.cursor/docs/requirements/requirements-traceability.md` | A stub covering FR-01 to FR-03 only. |
| `gipa_prod/app` | Laravel skeleton. Domain models are not implemented. Only the framework `User` model is present. |

### Source hygiene

`gipa_prod/TechDoc.txt` and `gipa_prod/TechDoc.docx` describe a multi-tenant laundry SaaS (TheLeads Laundry Management System), including tenants, orders, loyalty, MySQL, and SMS credits. That material is not an IOMP requirement. It must not be used as a source for IOMP behaviour, data, or integrations.

Several Cursor rule files paste a block titled "Main Components" and "Prototype Modules". Parts of that block match the TOR. Other parts name investor onboarding and aftercare, certificates and anti-forgery, matchmaking, ORC, GIS and payment integrations, and an AI assistant. Those parts are not in the TOR. This report treats them as proposed design or demo scope, not as documented requirements.

Rule precedence in `05-requirements.mdc` says: explicit current client decision, then updated client requirements, then an approved architecture decision record, then current approved functional requirements, then original TOR implementation wording, then AI assumptions. The ADR files are empty, so they do not yet outrank the TOR. The one written implementation decision is the Laravel stack and the rejection of Django admin.

## What the demo shows, without treating it as the requirement

The demo covers much of the TOR public portal, district and opportunity workflows, JWT catalog API, audit viewing, SLA indicators, and a PWA shell. It also implements programmes the TOR does not specify.

Documented requirement coverage that the demo status file marks incomplete:

- Opportunity comparison.
- Approved district-boundary GeoJSON import and geometry validation. A Leaflet point map exists.
- Maintenance mode controls, IP whitelist, and central settings UI.
- Report templates and exports named in the TOR. A staff dashboard exists.
- Notification template management.
- Staff inquiry assignment and response workspace.
- Admin onboarding tours and user-to-user messaging.
- User, role, and permission management screens.
- PostGIS. The demo README describes a MySQL-compatible schema. The TOR requires PostGIS-backed boundaries and locations.

Demo observation, outside the TOR:

- Investor registration, profiles, KYC documents, and an onboarding review workflow.
- Cryptographically signed certificates, QR verification, suspend, reinstate, and revoke.
- Investor-to-opportunity matchmaking.
- A retrieval-augmented assistant with an offline driver and an optional OpenAI driver.
- SMS providers and delivery webhooks.
- Separate investor and staff login, password reset, and email verification.
- Sixteen regions seeded with 261 districts.
- Default SLA durations of 72 hours, taken from environment variables.
- Public opportunity visibility limited to records with `published_at` set, workflow status approved, active, completed, or cancelled, and a published district.
- Project status default `proposed`, which is not one of the TOR values Not Started, In Progress, and On Hold.
- District rejection returns the district to draft. The TOR does not state the rejection destination.
- Opportunity and district coordinates stored as latitude and longitude decimals, with boundary and centroid fields, without a confirmed PostGIS deployment.

---

## 1. Functional requirements

Documented requirement. Section 3.3.A defines FR-01 to FR-18. Section 3.1 and the opening data-capture list add field-level and screen-level behaviour that the FR list summarises. Section 3.3 says the FR list is to be read together with section 3.1.

### FR-01 Geography registry

Documented requirement. Maintain a central registry of Ghana's 261 districts and regions, including name, code, status, geometry, District Readiness Score (DRS), economic data, data year, population, and area.

Unresolved question. The TOR does not say whether DRS, economic data, data year, population, and area apply to regions as well as districts. DRS is named as a district score.

Proposed design, from `40-data.mdc`. Do not hardcode 261 in application logic. The initial dataset may contain 261 districts, and the model must allow later administrative change. That is an engineering constraint, not a change to the initial registry size.

Demo observation. The demo seeds 16 regions and 261 districts, stores a readiness score, population, area, an infrastructure quality score, and JSON economic data on the district, and stores code, name, capital, and boundary on the region. A data-year field is not in the district fillable list reviewed.

### FR-02 District boundaries and district workflow

Documented requirement. Import and load district boundaries, with GeoJSON given as an example. District workflow: draft, then under review, then published, with approve and reject.

Documented requirement, section 3.0. The same workflow is restated as draft, under review, published.

Unresolved question. The TOR does not define the state after reject, whether reject requires a reason, who may submit, approve, or reject, or whether a published district can return to draft.

Demo observation. Submit moves draft to under review. Reject moves under review back to draft. Publish moves under review to published. Reassign stays in under review. These transitions are demo behaviour.

### FR-03 Opportunity data capture

Documented requirement. Full lifecycle management of investment opportunities and projects, with mandatory structured capture aligned to the official IOMP Data Form: administrative, financial, documentation, and contact data.

Documented requirement, opening field list and section 3.1. The platform must capture, validate, store, report, and expose at least:

| Group | Fields named in the TOR |
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

Documented requirement, section 3.1. All of the listed fields are mandatory unless GIPA explicitly marks a field optional.

Unresolved question. The opening field list omits ROI, IRR, NPV, payback period, and projected revenues. Section 3.1 includes them. It is not stated whether those metrics are mandatory.

Unresolved question. "Investment structure" may mean one selected structure, several selected structures, or an amount against each of the four structures. The TOR does not say.

Unresolved question. "Multi-currency support: USD and GHS" may mean one amount in one currency, two amounts, or a convertible pair. Exchange-rate ownership is not stated.

Unresolved question. The official IOMP Data Form is referenced and not attached to this TOR. Field optionality, validation ranges, and units may live on that form.

### FR-04 Opportunity workflow and SLA

Documented requirement. Lifecycle: draft, then pending approval, then approved, then active, then completed/cancelled. Actions: submit, approve, reject, and reassign. SLA tracking with breach notifications.

Unresolved question. "Completed/cancelled" may be one terminal label or two terminal states.

Unresolved question. The destination of reject, the meaning of reassign, the actor allowed to perform each action, and whether approved and active are both public states are not defined.

Unresolved question. Project status (Not Started, In Progress, On Hold) and the lifecycle workflow are both required. The TOR does not say how they relate.

### FR-05 Cascading filters

Documented requirement. Cascading filters region, then district, then sector, then investment type, then status, across districts, opportunities, and the investment map.

Unresolved question. "Investment type" is not defined in the field list. The field list has enterprise type, sector, sub-sector, and investment structure. The API resource list also names "investment types" separately from sectors.

### FR-06 and FR-07 Geospatial discovery and map

Documented requirement. Nearby search, bounding-box queries, polygon queries, and GeoJSON for districts and opportunities.

Documented requirement. An interactive investment map of opportunities, filterable by region, district, sector, type, status, and amount, with sorting.

Documented requirement, section 3.0. PostGIS-backed district boundaries and opportunity locations.

### FR-08 Investor inquiries

Documented requirement. Public users submit inquiries linked to opportunities. GIPA staff track and respond. Email notifications are sent.

Documented requirement, section 3.2. Submission and tracking are transparent, with notifications to relevant parties.

Unresolved question. Inquiry statuses, assignment, response content, and which parties are notified are not defined. "Relevant parties" is not a role list.

### FR-09 Identity and access

Documented requirement. Users, groups, and permissions. Role-based access. District-level assignment where applicable. JWT authentication for the API.

Documented requirement, training examples. User groups include IT administrators, content managers, and district officers. These are examples, not a closed role catalogue.

### FR-10 Audit

Documented requirement. Log material admin and API actions. Configurable retention. Export.

Documented requirement, NFR-08. The trail is complete and tamper-evident.

### FR-11 Notifications

Documented requirement. Configurable email notifications for workflow and inquiry events, including submission, approval, rejection, and SLA breach. Templates are configurable.

### FR-12 Maintenance mode

Documented requirement. Public site offline with a configurable message. Admin access and IP whitelist remain available. Controllable from CLI and admin UI. The API returns an appropriate status during maintenance.

Unresolved question. The HTTP status and the response body are not specified.

### FR-13 Reporting and dashboard

Documented requirement. Dashboard API and report templates for regional distribution, sector investment, timeline, readiness distribution, job creation, and infrastructure quality.

Unresolved question. Job creation and infrastructure quality are report topics. They are not fields in the opportunity or district capture lists.

### FR-14 Admin UI

Documented requirement. Full admin for all core entities and settings. Sections: content management, user and access management, workflow and processes, system administration. Custom forms, with reassign given as an example. Workflow actions.

Documented requirement, section 3.1 wording. "Django admin".

Proposed design, recorded in `implementation-decisions.md` and `00-project.mdc`. The administrative UI is Laravel server-rendered Blade, Alpine.js, and Tailwind CSS. Django is not to be introduced. This report records that decision as already written. It does not re-decide it. It remains a conflict with the TOR wording until the client confirms it. See section 26.

### FR-15 API

Documented requirement. Versioned REST API, example `/api/v1/`, for all core resources. OpenAPI 3.x, with Swagger or ReDoc named as examples. JWT login and refresh. A documented backward-compatibility policy. Date-based or semantic versioning is given as an example, not as a single choice.

Documented requirement, section 3.1 resource list. Users, groups, regions, districts, sectors, investment types, opportunities, workflow settings, audit logs, dashboard, workflow templates, notifications, and messaging.

### FR-16 Onboarding and messaging

Documented requirement. Onboarding tours, user preferences, and user-to-user messaging for admin users. APIs and admin UI.

Unresolved question. The TOR uses "onboarding" for tours and preferences. It does not describe investor registration, KYC, or aftercare. See section 26.

### FR-17 PWA

Documented requirement. Service worker, offline fallback page, installability, and health checks.

### FR-18 Settings

Documented requirement. Centralized admin-configurable settings for workflow, notifications, maintenance, audit, and onboarding.

### Public surfaces named in addition to the FR list

Documented requirement, section 3.0. Landing page, district list and detail, opportunity list and detail, investment map, opportunity compare, search, and investor inquiry submission.

Unresolved question. Search fields, compare rules, and how many opportunities can be compared are not specified.

### Delivery obligations that are functional only in the sense of the contract

Documented requirement, sections 2 and 3. The contractor produces a technical and delivery plan, automated tests, architecture and API documentation, deployment and operations guides, user and admin documentation, staging and production artefacts, knowledge transfer, training, UAT corrections, and a support period. These are delivery requirements. They are restated under operational requirements.

---

## 2. Non-functional requirements

Documented requirement. Section 3.3.B, NFR-01 to NFR-09, read with section 3.2.

| ID | Documented requirement |
| --- | --- |
| NFR-01 | At least 95 percent of API requests respond within 2 seconds under an agreed load. Public pages load within acceptable industry-standard thresholds on a typical broadband connection. |
| NFR-02 | Growth in districts, opportunities, users, and concurrent sessions without an architectural redesign. Horizontal scaling of application and database components. |
| NFR-03 | OWASP-aware design. No hardcoded secrets. Environment-based configuration. HTTPS in production. Secure headers and CSP. Input validation. Security review and penetration testing before go-live. Alignment with ISO/IEC 27001 principles. |
| NFR-04 | An agreed uptime target, with 99.5 percent given as an example. Documented backup and disaster-recovery procedures. A graceful, clearly communicated maintenance mode. |
| NFR-05 | WCAG 2.1 Level AA for public and admin UIs where specified. Usable by IT administrators, content managers, and district officers. |
| NFR-06 | Modular, documented codebase. Automated unit, integration, and API tests with agreed coverage targets. Maintainable by GIPA or a later vendor. |
| NFR-07 | Containerized deployment. FOSS where applicable for government use, with active maintenance and public patches. Versioned REST API for third-party and government systems. |
| NFR-08 | Validation at capture. Referential integrity. Complete, tamper-evident audit of material admin and API actions. |
| NFR-09 | Responsive public interfaces on current major browsers and common device sizes. Offline fallback through the PWA. |

Unresolved question. "Where specified" on WCAG, "agreed load", "acceptable industry-standard thresholds", "agreed uptime", "agreed coverage", and the compatibility window for browsers are not numeric or enumerated in the TOR.

Proposed design, from `00-project.mdc`. Engineering priority order is security, correctness, data integrity, architectural integrity, maintainability, testability, performance, scalability, accessibility, then developer productivity. That order is a project rule, not a TOR ranking. The TOR lists those qualities without this order.

---

## 3. Actors and user groups

Documented requirement.

| Actor | What the TOR says they do |
| --- | --- |
| GIPA | Client and institutional owner. Operates the authoritative platform. |
| Public user | Uses landing, lists, detail, map, compare, search, and submits an investor inquiry. |
| GIPA staff | Track and respond to inquiries. Perform admin and workflow work. The TOR does not split staff into a closed set of roles. |
| IT administrator | Example training audience. Implied operator of technical administration. |
| Content manager | Example training audience. Implied operator of content. |
| District officer | Example training audience. Implied operator with a district relationship. |
| Contractor | Designs, builds, tests, deploys, trains, and supports. Not a runtime actor. |
| Future vendor | Operates and extends the system after handover. |
| External system | Consumes the versioned API. No named external system is required. |
| Relevant parties | Receive inquiry and workflow notifications. Not identified. |

Proposed design, pasted into Cursor rules as prototype module B. Login, password reset, session controls, MFA-ready architecture, roles, groups, permissions, and district-level restrictions. Password reset and MFA are not TOR requirements.

Demo observation. Seeded roles are Super Administrator, Content / Data Manager, District Officer, Field Agent, Reviewer / Approver, and Investor. Investors have a separate login and a portal. Field Agent is a least-privilege submitter. These roles are prototype choices.

Unresolved question. Is an investor an authenticated user, or only an anonymous inquiry submitter? The TOR's public inquiry flow does not require an account. The demo created accounts.

Unresolved question. Is district assignment one district, many districts, or a time-bounded assignment? The TOR says "where applicable" and stops.

---

## 4. Business capabilities

Documented requirement. These are the capabilities the TOR requires the platform to provide. They are not a module diagram.

1. Hold the authoritative registry of regions and districts, including readiness and geometry.
2. Import district boundaries.
3. Govern district publication through draft, review, and publish.
4. Capture investment opportunities on the IOMP data form, including finance, documents, and contacts.
5. Govern opportunity progression from draft through approval to active and a completed or cancelled end.
6. Track SLA and notify on breach.
7. Let the public discover districts and opportunities by search, cascading filters, comparison, and map.
8. Answer geospatial questions: nearby, bounding box, polygon, and GeoJSON.
9. Accept, store, track, and answer investor inquiries, and notify by email.
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

Proposed design, not capabilities of the TOR. Investor onboarding and aftercare, certificate issue and anti-forgery verification, investor-to-opportunity matchmaking, named external integrations, and an AI assistant appear in Cursor rules and in the demo. They are out of TOR scope until the client adds them.

---

## 5. Candidate bounded contexts

Proposed design. The TOR does not name bounded contexts. `10-architecture.mdc` requires a modular monolith and forbids one context from writing another context's internal state. ADR-0002 is empty, so no boundary set is approved. The candidates below are offered for a later decision. They follow TOR capabilities, not the demo's extra programmes.

| Candidate context | TOR capabilities it would own | Why it is only a candidate |
| --- | --- | --- |
| Geography | Regions, districts, boundary import, DRS, economic indicators, district publication workflow | District workflow could instead live in a shared workflow context. |
| Opportunity catalogue | Opportunity identity, data-form fields, documents, contacts, project status, publication lifecycle | Finance and documents could be split out. That split is not required. |
| Discovery | Public search, filters, compare, map read models | Discovery can be a read model over Geography and Opportunity rather than a context that owns data. |
| Geospatial query | Nearby, bounding box, polygon, GeoJSON | This may be infrastructure inside Geography and Opportunity, not a context. |
| Inquiry | Public inquiry submission, staff tracking, response | Small enough that it could be a supporting module of Opportunity. |
| Identity and access | Users, groups, permissions, district assignment, API authentication | Authentication mechanism is infrastructure. Roles and district scope are domain policy. |
| Workflow and SLA | Transition rules, assignment, clocks, breach | A generic engine versus workflow inside each context is an open design choice. ADR-0010 is empty. |
| Notification | Templates and delivery of email | Delivery is infrastructure. Template policy may be a supporting module. |
| Audit | Append-only record of material actions, retention, export | Cross-cutting. Owning it as a context avoids other contexts rewriting history. |
| Reporting | Dashboard and the named report templates | Reads other contexts. Should not become a second source of opportunity facts. |
| Platform operations | Maintenance mode, health, settings, PWA shell | Settings are configuration. Some settings change domain policy, especially SLA and workflow. |
| Admin collaboration | Tours, preferences, admin messaging | Small. Easy to over-model. |

Proposed design that this report does not adopt. A certificate context, an investor-onboarding context, a matchmaking context, and an assistant context match the demo. They do not match the TOR. If the client later confirms that scope, those contexts need their own decision.

Unresolved question. Should workflow be one shared capability or a policy inside Geography and Opportunity separately? The TOR describes two different state machines, which argues against one shared status enum, and it also asks for workflow settings and workflow templates, which argues for shared configuration.

---

## 6. Entities

Documented requirement, as business concepts that have identity in the TOR. The list is the concept, not a table design.

| Entity | Identity in the TOR |
| --- | --- |
| Region | Named administrative region in the registry. |
| District | One of the 261 metropolitan, municipal, or district units, with code, status, and geometry. |
| Sector | Classification used in capture, filters, and reports. |
| Sub-sector | Classification under a sector. The TOR implies a hierarchy and does not define the tree. |
| Investment type | Named in filters and in the API resource list. Not defined. |
| Opportunity | The investment opportunity or project, with title, location, narrative, finance, documents, contact, project status, and lifecycle. |
| Supporting document | Business plan, technical feasibility, market feasibility, or funding structure attached to an opportunity. |
| Contact | Name, organisation, phone, and email on an opportunity. The TOR does not say whether many contacts are allowed. |
| Inquiry | A public submission linked to an opportunity. |
| User | A person who administers or uses protected functions. |
| Group | A named access grouping. The TOR says groups and also role-based access. It does not define the difference. |
| Permission | An authorization grant. |
| District assignment | The link that restricts a user to a district where that restriction applies. |
| Audit record | A retained record of a material admin or API action. |
| Notification template | Configurable email content for named events. |
| Workflow setting | Admin-configurable workflow configuration. |
| Workflow template | Named as an API resource. The TOR also fixes the state names, so the template's freedom is unclear. |
| Setting | Central configuration for workflow, notifications, maintenance, audit, and onboarding. |
| Message | An admin user-to-user message. |
| Report | One of the named dashboard or report outputs. |
| Onboarding preference | A user's tour and preference state. |

Proposed design, not entities required by the TOR. Investor profile, KYC document, onboarding case, certificate, certificate event, match preference, assistant document, and SMS message exist in the demo.

Unresolved question. Is the opportunity contact the promoter, a district officer, or GIPA? The TOR only says contact details.

Unresolved question. Does "status" on a district mean workflow status, an administrative active flag, or both?

---

## 7. Value objects

Documented requirement. The TOR requires these concepts to be validated. It does not require them to be implemented as value-object types. The type choice is a proposed design.

| Concept | Why it is more than a bare string |
| --- | --- |
| Money and currency | Amount required, USD and GHS, and reportable investment totals. NFR-08 requires validation at capture. |
| Investment structure component | Local equity, foreign equity, local loan, foreign loan. |
| Enterprise type | Closed list in the TOR. |
| Project status | Closed list: Not Started, In Progress, On Hold. |
| Opportunity lifecycle state | Closed list, with the completed/cancelled ambiguity in section 25. |
| District lifecycle state | Draft, under review, published. |
| Geographic point | Opportunity location as a point, separate from descriptive text. |
| District boundary | Geometry imported from GeoJSON and queried in PostGIS. |
| Email address and phone number | Contact and inquiry notification targets. |
| Financial metric | ROI, IRR, NPV, payback period, projected revenue. Units are not defined. |
| District Readiness Score | Named score. Scale, formula, and who calculates it are not defined. |
| Data year | The year the economic or readiness figures describe. |

Proposed design, from `20-ddd.mdc`. Money, investment amount, geographic coordinate, email address, phone number, percentage, and investment metric are examples of value objects to consider. The rule also says not to wrap values that have no behaviour.

Assumption this report does not accept. That DRS is a 0–100 percentage. The TOR never states a scale.

Demo observation. The demo stores ROI and IRR as percentages, NPV and projected revenue as decimals, payback as months, and readiness as a decimal. Those units are prototype choices.

---

## 8. Aggregates

Proposed design. The TOR does not define aggregate boundaries. `20-ddd.mdc` says an aggregate exists to protect an invariant, not to mirror a table. The candidates below are consistency boundaries suggested by the TOR. They are not selected.

| Candidate aggregate | Invariant it would protect | TOR basis |
| --- | --- | --- |
| District | A district belongs to one region. Geometry, code, and workflow status change together. Publication happens only from the allowed prior state. Boundary import replaces geometry only through a validated operation. | FR-01, FR-02 |
| Opportunity | Required data-form fields, project status, lifecycle, location, sector, district, finance, and contact stay consistent. A lifecycle action is the only way to change workflow state. | FR-03, FR-04 |
| Opportunity document set | Each required document category is present before a transition that demands it, if the client confirms that gate. | FR-03, and an unresolved gate |
| Inquiry | An inquiry is always linked to an opportunity and keeps its own tracking history. | FR-08 |
| User access | A user's groups, permissions, and district assignments change as one authorization decision. | FR-09 |
| Audit entry | Once written, an audit entry is not edited in the ordinary path. | FR-10, NFR-08 |

Unresolved question. Which fields must be complete before submit, before approve, and before the opportunity becomes publicly visible? The TOR says the fields are mandatory unless GIPA marks them optional. It does not say at which transition incompleteness blocks progress.

Unresolved question. Must an opportunity's point lie inside its district boundary? The TOR does not say. Enforcing that would be a proposed invariant, not a documented one.

Demo observation. The demo keeps workflow events immutable and separate from the opportunity and district rows, and uses a version column. That is a prototype concurrency and history choice, not a TOR aggregate.

---

## 9. Domain events

Proposed design. The TOR requires notifications, audit, and SLA breach handling when certain things happen. It does not name domain events. `20-ddd.mdc` lists examples and says not to emit an event for every method call. The names below are candidates, not an approved event catalogue.

| Candidate event | Documented trigger |
| --- | --- |
| District submitted for review | District leaves draft toward under review. The TOR says approve and reject. It does not name "submit", though a draft state implies an action into review. |
| District approved or published | Approve action on the district workflow. |
| District rejected | Reject action. Destination state unresolved. |
| Opportunity submitted | Submit action. |
| Opportunity approved | Approve action. |
| Opportunity rejected | Reject action. |
| Opportunity reassigned | Reassign action. |
| Opportunity activated, completed, or cancelled | The lifecycle names these states. The TOR does not name a separate action for each. |
| Inquiry submitted | Public submission. |
| Inquiry responded | Staff response. The response event is implied by "respond" and is otherwise unspecified. |
| SLA breached | Breach notification. |
| Maintenance mode enabled or disabled | Operational state change. Audit-worthy. Not named as a domain event in the TOR. |

Documented requirement. Email is required for submission, approval, rejection, and SLA breach, and for inquiry events. Audit is required for material admin and API actions. Those are reactions. The event mechanism is a proposed design.

Proposed design, from `50-workflows.mdc`. Notifications react to events and are not hard-wired inside the transition. Email may be queued. SMS is mentioned in that rule and is not a TOR channel.

Demo observation. The demo queues mail and database notifications, writes workflow events, and runs an hourly SLA escalation command that emails the assigned reviewer once. Escalation recipient and the hourly schedule are prototype choices.

---

## 10. Workflows

### District publication

Documented requirement.

```text
draft → under review → published
```

Actions named: approve, reject.

Unresolved question. See FR-02. Reject destination, reasons, resubmission, and unpublish are unspecified.

Demo observation, not a requirement.

```text
draft --submit--> under review --publish--> published
under review --reject--> draft
under review --reassign--> under review
```

### Opportunity lifecycle

Documented requirement.

```text
draft → pending approval → approved → active → completed/cancelled
```

Actions named: submit, approve, reject, reassign.

Unresolved question. Whether "completed/cancelled" is one state or two. Whether reject returns to draft. Whether reassign changes the state. Whether active is automatic after approval or a separate action. Whether cancellation is allowed from every non-terminal state. Whether a completed opportunity can reopen.

Proposed design, from `50-workflows.mdc`. Cancellation is an applicable terminal path beside completed. The rule also says not to invent rejection behaviour, and to flag unconfirmed SLA rules. This report follows that caution.

Demo observation. Separate actions exist for submit, approve, reject, reassign, activate, complete, and cancel. Workflow status is stored separately from project status. Public visibility includes approved, active, completed, and cancelled once `published_at` is set.

### Project status

Documented requirement. A second status on the opportunity: Not Started, In Progress, On Hold.

Unresolved question. Who may change it, whether it is independent of the lifecycle, and whether On Hold pauses the SLA.

### Inquiry handling

Documented requirement. Submit, store, track, respond, notify.

Unresolved question. There is no documented inquiry state machine.

Proposed design, from the prototype-module text in the Cursor rules. Assignment and response status. That text is not the TOR.

### Admin messaging

Documented requirement. User-to-user messaging for admin users exists. No thread, retention, or moderation workflow is specified.

### Settings and maintenance

Documented requirement. An operator can change workflow, notification, audit, onboarding, and maintenance settings, and can enable maintenance from the CLI or the admin UI.

---

## 11. SLA requirements

Documented requirement.

- Opportunity approval work has SLA tracking and breach notifications (FR-04, section 3.1).
- Content and workflow actions are resolved in a timely way, with monitoring and breach alerts (section 3.2).
- Email is sent on SLA breach (FR-11).
- Workflow settings are admin-configurable (FR-18).
- The admin UI includes SLA tracking (section 3.0).

Unresolved question. All of the following are absent from the TOR:

- Duration for each state and each entity.
- Whether the clock starts at submit, at assignment, or at entering a state.
- Business calendar, weekends, and public holidays.
- Pause on On Hold, on reassignment, or on maintenance mode.
- Warning threshold before breach.
- Who is notified, and whether notification repeats.
- Whether district review has an SLA. The detailed SLA sentence is attached to opportunities. Section 3.2 speaks of workflow actions generally.
- Consequence of breach other than notification. The TOR does not say the record auto-escalates or auto-approves.

Assumption this report does not accept. The demo default of 72 hours for district review, opportunity review, and investor onboarding review. Those numbers are environment defaults in the prototype, not TOR durations.

Proposed design, from `50-workflows.mdc`. SLA rules are configurable, not hardcoded in controllers, and calculations consider start, due time, calendar, reassignment, pause and resume, completion, and breach. Where those rules are unconfirmed, they stay flagged. This report flags them.

---

## 12. Authorization boundaries

Documented requirement.

| Boundary | What must be enforced |
| --- | --- |
| Public versus staff | Public pages and inquiry submission are available without the admin capability. Admin CRUD, workflow actions, settings, audit export, and messaging are not public. |
| Role and group | RBAC with users, groups, and permissions. |
| District scope | Where district assignment applies, a user does not gain other districts by holding a role alone. The TOR does not define the permission matrix. |
| API | JWT for protected API access, with login and refresh. The TOR does not say which resources are anonymous. |
| Maintenance | Admins and whitelisted IPs can pass. Everyone else sees the maintenance experience. |
| Workflow actions | Submit, approve, reject, and reassign are controlled actions. The actor for each action is not named. |
| Audit export and settings | Admin capabilities. The TOR does not name the permission key. |

Proposed design, from `30-security.mdc` and `60-api.mdc`. Server-side checks on every protected operation. Hidden UI is not authorization. District scope is checked on the server. Spatie is named in the security rule as the package, with admin-configurable roles. Package choice is a proposed design. The TOR does not name Spatie.

Proposed design, from `70-frontend.mdc`. Public views stay separate from admin views so administrative actions are not exposed on the public UI.

Demo observation. Permissions include district submit, review, and reassign, and opportunity submit, review, reassign, and lifecycle. Audit view and export, and SLA monitor view, are separate permissions. Public catalog GET routes are unauthenticated. Investor match routes require JWT. Certificate verification is public and rate-limited. That matrix is prototype policy.

Unresolved question. Which opportunity fields are public? Contact phone and email are mandatory capture fields and are also personal data. Publishing them on the public detail page is not stated.

Unresolved question. May a district officer approve an opportunity in their district, or only submit it? The TOR gives no segregation-of-duties rule.

---

## 13. Data ownership

Documented requirement. The platform is the authoritative system for investment opportunities and projects across Ghana's regions and districts. GIPA operates it.

Proposed design, offered for a later ownership decision. This is not approved.

| Data | Candidate owner | Notes |
| --- | --- | --- |
| Region and district registry, geometry, DRS, economic figures, data year, population, area | Geography | Authoritative inside the platform. External boundary files are inputs, not a second live authority, unless the client says otherwise. |
| Opportunity data-form content, lifecycle, project status | Opportunity catalogue | Field collection is a source of input. After capture, the platform is the system of record. |
| Supporting documents | Opportunity catalogue for the association; file storage for the bytes | ADR-0008 is an empty placeholder. Storage product is not chosen. |
| Inquiry content | Inquiry | Personal data of the inquirer. |
| Users, groups, permissions, district assignment | Identity and access | |
| Audit trail | Audit | Other contexts emit facts. They do not revise history. |
| Notification templates and delivery logs | Notification | |
| Report outputs | Reporting | Derived. Not a second master of amounts or counts. |
| Settings | Platform operations | Workflow durations, if configurable, are policy consumed by workflow. |

Unresolved question. Who is allowed to correct a published opportunity, and does correction create a new version visible to the public? The TOR requires audit of material changes and backward-compatible extension. It does not require a public version history.

Unresolved question. Are economic data, DRS, job-creation figures, and infrastructure quality supplied by districts, by GIPA centrally, or by an external statistical source?

Demo observation. The demo stores infrastructure quality and economic JSON on the district and treats published workflow state as the public gate. It does not document an external system as the owner of those figures.

Proposed design, from `40-data.mdc`. Do not duplicate authoritative data without a reason. Collect only personal data required for the documented purpose. Do not expose personal data on public endpoints by default.

---

## 14. Geospatial requirements

Documented requirement.

- District geometry stored with PostGIS named as the example and, in section 3.0, as the backing store.
- Opportunity location is both descriptive text and a geospatial point.
- Boundary import, with GeoJSON as the example format.
- Nearby search, bounding-box query, and polygon query.
- GeoJSON output for districts and opportunities.
- Cascading filters on the map.
- Map filters for region, district, sector, type, status, and amount, plus sort.
- The map is an interactive public feature.

Unresolved question.

- Coordinate reference system. WGS 84 is a common expectation and is not stated.
- Geometry types accepted on import: polygon, multipolygon, or both.
- What "nearby" means: radius, unit, default radius, and maximum radius.
- Polygon query input: GeoJSON body, or a list of coordinates.
- Whether queries are limited to published records.
- Whether the map shows district boundaries, opportunity points, or both. Section 3.1's map sentence says opportunities. Boundary display is implied by district geometry and by the prototype-module text, and is not explicit in the map sentence.
- Maximum result size for a map request.
- Accuracy and source of the official boundary file. The TOR says import is in scope. It does not attach the file.
- Whether opportunity points are required to fall inside the selected district.

Proposed design, from `40-data.mdc`, `60-api.mdc`, and `30-security.mdc`. Use PostGIS for spatial predicates, GiST indexes, and GeoJSON serialization. Do not pull large geometries into PHP to do work PostGIS can do. Treat imported GeoJSON as untrusted: structure, geometry type, coordinate validity, CRS expectation, size, and polygon validity. Bound result sizes. Validate coordinates. `70-frontend.mdc` sketches a MapLibre viewport query using `ST_MakeEnvelope`. That sketch is a proposed design. The demo uses Leaflet.

Demo observation. Boundary import and geometry validation are still an open production gate in `system-component.md`. The map that exists is an opportunity point map.

Assumption this report does not accept. That a client-supplied latitude and longitude may be stored without checking it against the district polygon.

---

## 15. API resources

Documented requirement. REST, version example `/api/v1/`, JWT login and refresh, OpenAPI 3.x. Backward compatibility is a documented policy. Versioning may be date-based or semantic. The TOR does not choose one.

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

Documented requirement, from features rather than the resource list:

- Filter options for the cascading lists
- Geospatial queries: nearby, bounding box, polygon, GeoJSON
- Investor inquiry submission
- Authentication refresh

Proposed design, not a contract. A later API decision would need to define, for each resource, anonymous versus authenticated access, pagination, filter allowlists, sort allowlists, and error shape. `60-api.mdc` already requires those engineering properties. It does not define the payloads.

Demo observation. Implemented public routes under `/api/v1` include region, district, sector, and sub-sector lists, opportunity list and detail, and inquiry creation. JWT routes include login, refresh, logout, investor profile, preferences, and matches. Certificate verification is also exposed. Missing relative to the TOR resource list: users, groups, investment types as a named resource, workflow settings, audit logs, dashboard, workflow templates, notifications, and messaging. Sub-sector appears in the demo and is implied by the TOR field list.

Unresolved question. Which of the named resources are public, which are staff-only, and which mutations exist? "REST API for all core resources" does not by itself authorize public write access.

Unresolved question. Date-based versus semantic versioning is explicitly unresolved by the TOR's "or".

---

## 16. External integrations

Documented requirement.

- Email delivery for workflow and inquiry notifications. No vendor is named.
- GeoJSON boundary import. The file source is not named.
- A versioned REST API so that third-party and government systems can integrate. No system is named.
- JWT as the API authentication mechanism.
- Container hosting on FOSS where applicable. No cloud vendor is named.
- Penetration testing before go-live. The tester is not named.

Not a documented requirement. The following appear only in Cursor rule paste or in the demo:

| Item | Where it appears | TOR status |
| --- | --- | --- |
| ORC | Rule paste, "external system integrations" | Absent |
| GIS as a live external service | Rule paste | The TOR requires PostGIS inside the platform, not a named external GIS product |
| Payments | Rule paste | Absent |
| SMS gateways, including Frog and Twilio | Demo | Absent. TOR channel is email |
| OpenAI or another assistant model | Demo | Absent |
| Malware scanning of uploads | Demo status, still open | Not named. File validation is a security expectation in the project rules |
| Certificate signing keys or an HSM | Demo | Absent from the TOR |

Proposed design, from `85-performance.mdc` and `10-architecture.mdc`. Mail and other slow work can be queued. External URL fetch is SSRF-sensitive under `30-security.mdc`.

Unresolved question. If the client does want ORC, payments, SMS, or an assistant, each one needs a purpose, data contract, and owner. This report does not add them.

---

## 17. Audit requirements

Documented requirement.

- Log material admin actions and material API actions (FR-10, section 3.2).
- Retention is configurable (FR-10, FR-18).
- Export is required (FR-10).
- The trail is complete and tamper-evident (NFR-08).
- Data changes are validated and referential integrity holds (NFR-08).

Unresolved question.

- The TOR does not define "material".
- The TOR does not define tamper-evident: database permissions, hash chain, write-once storage, or another control.
- Retention period, export format, and who may export are not set.
- Whether read access is audited, or only changes.
- Whether public inquiry submission is an audited API action. It is a material public write.

Proposed design, from `50-workflows.mdc` and the prototype-module text. A workflow audit record may include actor, action, resource, resource id, before, after, IP, user agent, request id, timestamp, authentication context, and source channel. `40-data.mdc` says audit records are append-oriented and protected from ordinary modification. `30-security.mdc` forbids logging passwords, tokens, and unnecessary personal data. Those fields are a proposed design that is stronger than the TOR sentence, and they are reasonable candidates. They are not approved here.

Demo observation. The demo uses an activity log with before and after values, a permission-gated viewer, CSV and PDF export, and a scheduled retention clean. PDF export and the activity-log package are prototype choices.

---

## 18. Notification requirements

Documented requirement.

- Channel: email.
- Templates are configurable.
- Events named: submission, approval, rejection, SLA breach, and inquiry events.
- Settings for notifications live in the admin UI.
- Inquiry notifications go to relevant parties. Workflow notifications go with those events.

Unresolved question.

- Sender identity, reply-to, and language.
- Whether the opportunity contact, the assignee, the submitter, and a GIPA mailbox all receive mail.
- Whether the inquirer receives a receipt.
- Whether rejection mail includes the reason.
- Digest versus immediate send.
- SMS is not required.

Proposed design. Queue email delivery so the workflow transaction does not depend on the mail provider. Do not couple transition code to a specific mail vendor. This is an engineering proposal from the workflow and performance rules.

Demo observation. Queued mail and in-app database notifications exist. SMS infrastructure exists and is outside the TOR. Template management UI is still open in the demo status file.

---

## 19. Reporting requirements

Documented requirement. A dashboard API and report templates for:

| Report | TOR name | Captured input in the TOR |
| --- | --- | --- |
| Regional distribution | Yes | Region and district on each opportunity |
| Sector investment | Yes | Sector, sub-sector, and investment amount |
| Timeline | Yes | No date field is named beyond workflow states |
| Readiness distribution | Yes | District Readiness Score |
| Job creation | Yes | No job-creation field is named |
| Infrastructure quality | Yes | No infrastructure field is named on the data form |

Unresolved question. Audience, refresh, export format, filters, and whether figures are counts, sums of investment amount, or both.

Unresolved question. Timeline of what: submission, approval, project start, or data year?

Unresolved question. Job creation and infrastructure quality need a source field, a unit, and an owner before a report can be specified. The demo stores an infrastructure quality score on the district. That does not make the field a TOR requirement, but it shows one possible home for it.

Proposed design. Reports read published or otherwise agreed records and do not create a second authoritative copy of opportunity amounts. Which statuses enter a report is an unresolved question, not a decision.

Demo observation. A staff dashboard shows workflow, district, inquiry, and SLA indicators. The TOR report pack and its exports are still open in the demo status file.

---

## 20. PWA requirements

Documented requirement, FR-17 and NFR-09.

- Service worker.
- Offline fallback page.
- Installability.
- Health checks.
- Public pages remain usable as a responsive web application on current major browsers and common device sizes.
- Offline behaviour required by the TOR is the fallback, not a full offline editing client.

Unresolved question.

- Which routes the service worker may cache.
- Whether opportunity data, documents, and map tiles are cached.
- Health-check path, payload, and authentication.
- Install icon, name, and display mode are unspecified.

Proposed design, from `90-operations.mdc`. Health output must not disclose sensitive information. From the demo status notes, certificate or other authenticity checks must not be served from a stale cache. That note matters only if certificate scope is later accepted. For the TOR, inquiry submission and workflow actions should not be implied to work offline. The TOR does not say they do.

Demo observation. Manifest, service worker, and offline fallback are marked done. Map tiles and live data behaviour offline are not fully specified in the status file.

---

## 21. Operational requirements

Documented requirement.

| Area | TOR statement |
| --- | --- |
| Environments | Staging and production deployable artefacts. Hosting environment is provided by the contractor for the contracted deployment and test. |
| Containers | Docker, with documented environment variables and deployment steps for staging and production. |
| Configuration | Environment-based. No hardcoded secrets. |
| HTTPS | Enforced in production. |
| Maintenance | CLI and admin controls, public message, admin and IP whitelist, safe API status. |
| Backup and recovery | Documented backup and disaster-recovery procedures. Uptime example 99.5 percent, still to be agreed. |
| Health | Health checks as part of the PWA feature set. |
| Observability of SLA | Monitoring and breach alerts. |
| Security operations | Security review and penetration test before go-live. ISO/IEC 27001 principles. |
| Handover | Code, repositories, documentation, runbooks, knowledge transfer. |
| Support | Warranty and maintenance for a period to be agreed, with 12 months given as an example. Bug fixes, patches, minor enhancements. Tools and procedures so GIPA can maintain the system after warranty. |
| Training | Plan agreed with the client. Audiences include IT administrators, content managers, and district officers. On-site or remote. User guides and instructional material. |
| Acceptance | UAT, then corrections from UAT and training. |
| Data load | Import and configuration of data as agreed during deployment and test. |
| Method | RUP, Agile such as Scrum, or a combination. The contractor describes it in the technical proposal. |
| Plans | Implementation schedule, change management, communication, stakeholder engagement, and risk management. |
| FOSS | Hosting software based on FOSS where applicable for government use, actively maintained, patches public. |

Unresolved question. Backup frequency, retention, encryption, restore test interval, recovery time objective, and recovery point objective are not numeric.

Unresolved question. Who provides production hosting after the contractor's delivery environment: GIPA, a government host, or the contractor?

Proposed design, from `90-operations.mdc`. Separate local, test, staging, and production. Structured logs without secrets. Monitor errors, latency, queues, database, storage, authentication failures, and security events. Define backup frequency, retention, encryption, restoration, and restore tests. Document recovery order. Maintenance must fail safe. Production changes need human approval. These operational practices are project rules. They do not fill the missing numeric targets.

Demo observation. The demo has a backup controller and backup-run model. That is prototype operations UI, not the TOR runbook.

---

## 22. Security requirements

Documented requirement.

- No hardcoded secrets. Environment configuration.
- HTTPS in production.
- CSP and secure headers.
- Input validation.
- OWASP-aware design.
- Security review and penetration testing before go-live, with remediation.
- ISO/IEC 27001 principles in design, development, and deployment. The TOR says "e.g. ISO/IEC 27001" and also says the contractor performs tests so that there are no security vulnerabilities. "No vulnerabilities" is an acceptance aspiration. A real test report will contain findings and a remediation bar that the client still needs to set.
- JWT for the API, including refresh.
- RBAC and district scope where applicable.
- Tamper-evident audit.
- FOSS components that still receive public patches.
- Compliance with relevant legislation. The legislation is not named.

Proposed design, from `30-security.mdc`. Defense in depth at the boundaries listed in that rule: HTTP, uploads, GeoJSON, external responses, and job payloads. Established JWT libraries, no custom cryptography. Explicit mass assignment. File type, size, name, storage, and access checks. No execution of uploads. No secrets in logs. CSRF, cookie flags, and output escaping on the web UI. No stack traces, SQL, or paths in production errors. SSRF control if the server fetches URLs. Rate abuse and CORS called out for the API. These are engineering rules aligned to NFR-03. They are not a completed threat model.

Unresolved question. Data-protection legislation applicable to contact and inquirer personal data, retention of that data, and whether consent is required on the inquiry form. The demo collects consent. The TOR does not mention consent.

Unresolved question. Session security for the admin UI. The TOR specifies JWT for the API and does not specify the browser session design.

Unresolved question. Classification of opportunity documents. Some feasibility documents may be commercially sensitive. The TOR requires them to be stored and exposed through the API, and it does not say they are public.

Demo observation. The demo records a security-header and CSP nonce pass, private upload storage, and throttling on inquiry and login. It also implements certificate signing, which is outside the TOR. MFA is listed as not done.

---

## 23. Performance requirements

Documented requirement, NFR-01 and NFR-02.

- 95 percent of API requests respond within 2 seconds under an agreed load.
- Public pages meet an unspecified industry-standard load time on a typical broadband connection.
- The design must absorb growth in districts, opportunities, users, and sessions without a redesign, and must be able to scale application and database horizontally.

Unresolved question. The load is "agreed" and not agreed in the TOR. Concurrent users, opportunity volume, map-request rate, and the page-load budget are missing. Without those numbers, NFR-01 cannot be tested.

Proposed design, from `85-performance.mdc`. Paginate collections. Allowlist filters and sorts. Index for real access paths, including GiST for geography. Avoid N+1 queries and unbounded responses. Queue mail, imports, large documents, and heavy reports. Do not keep shared business state only in local process memory if horizontal scaling is required. Do not claim the 2-second target without a measured workload. These are engineering constraints. They do not invent the missing load profile.

Demo observation. The demo bounds some public queries and matchmaking candidate sets. It runs on a MySQL-compatible schema in the README, so it is not evidence that the PostGIS design meets NFR-01.

---

## 24. Accessibility requirements

Documented requirement, NFR-05, section 3.2, and section 2.

- WCAG 2.1 Level AA for public and admin UIs where specified.
- Usable by IT administrators, content managers, and district officers.
- Responsive layout on common device sizes (NFR-09).
- The phrase "where specified" means the TOR does not attach a page-by-page exception list. No exception list exists in the document.

Unresolved question. Whether the map must have a non-map equivalent that exposes the same opportunity data. WCAG 2.1 treatment of maps is a known difficulty. The TOR requires both the map and Level AA where specified, and it does not say the map is exempt.

Proposed design, from `70-frontend.mdc`. Semantic HTML, keyboard access, focus management, labels, accessible errors, contrast, screen-reader behaviour, reduced motion, accessible forms, and a non-map alternative for map content. Server-side validation remains the authority. These are implementation criteria consistent with NFR-05. They should be confirmed in the accessibility test plan rather than treated as extra product scope.

Demo observation. The assistant widget notes a reduced-motion fallback. That widget is outside the TOR. The public portal is described as responsive with light and dark themes. Theme choice is not a TOR requirement.

---

## 25. Ambiguous requirements

Each item is a documented requirement whose meaning is incomplete. None of these ambiguities is resolved by this report.

1. **Completed/cancelled.** One lifecycle state or two terminal states.
2. **Project status versus lifecycle.** Not Started, In Progress, and On Hold sit beside draft, pending approval, approved, active, and completed/cancelled. Both are mandatory concepts. Their interaction is unstated.
3. **Investment type.** Used in filters and the API list. Not defined against enterprise type, sector, or investment structure.
4. **Investment structure shape.** One choice, many choices, or an amount per equity and loan component.
5. **Currency.** One currency per amount, or USD and GHS together, and who owns the rate.
6. **Financial metrics.** Present in section 3.1 and absent from the opening field list. Units and mandatory-ness are unstated.
7. **Mandatory fields.** All listed fields are mandatory unless GIPA marks them optional. GIPA has not marked any in this TOR. The gate at which missing data blocks a transition is unstated.
8. **District attribute list.** FR-01 attaches one "including" list to districts and regions. DRS on a region is hard to reconcile with the name District Readiness Score.
9. **District reject.** Approve and reject are required. The next state after reject is not.
10. **Opportunity reject and reassign.** Same gap. Reassign's effect on the SLA clock is not defined.
11. **Submit.** The opportunity actions include submit. The district actions named in FR-02 are approve and reject, while the states include draft. How a district enters review is implied, not specified.
12. **Public visibility.** Which lifecycle states appear on the public list, detail, compare, search, and map.
13. **Groups versus roles.** The TOR requires both groups and role-based access and does not define them.
14. **Onboarding.** FR-16 is tours, preferences, and admin messaging. The word is easy to read as investor onboarding. The TOR text does not support that reading.
15. **Workflow templates.** Named as an API resource, while the state names are fixed in prose. How much of the machine is configurable is unstated.
16. **Material audit and tamper-evident.** The qualities are required. The mechanism and the event set are not.
17. **Relevant parties.** Notification recipients are not named.
18. **Job creation and infrastructure quality.** Required as reports, not as captured fields.
19. **Timeline report.** The time axis is not named.
20. **Nearby, polygon, and map layers.** Query parameters and geometry contracts are not specified.
21. **API versioning scheme.** Date-based or semantic, left open.
22. **Django admin.** A product name in section 3.1, later treated by project rules as superseded. See section 26.
23. **WCAG "where specified".** No specification of exceptions is included.
24. **Performance and uptime examples.** "Agreed load", "industry-standard thresholds", and "e.g. 99.5 percent" are placeholders.
25. **Coverage targets.** Required to be agreed. Not agreed in the TOR.
26. **Legislation.** "Relevant legislation" is unnamed.
27. **261 districts.** A current fact of the registry. Whether the software must reject a 262nd district is not stated. The data rule proposes that the count must not be hardcoded.
28. **Compare and search.** Both are required features without rules.
29. **Document exposure.** Required supporting documents have no public versus staff visibility rule.
30. **Health check.** Required, with no contract.

---

## 26. Contradictory requirements

These are conflicts between texts. This report does not pick a winner except where a project file already records a decision, and even then it labels that as a recorded proposed design awaiting client confirmation where the TOR still disagrees.

### Django admin versus the Laravel decision

Documented requirement, section 3.1. "Django admin" for all core entities and settings.

Proposed design, already written in `implementation-decisions.md` and `00-project.mdc`. Laravel 12, Blade, Alpine.js, and Tailwind CSS. Django must not be introduced. The rules say the TOR contains implementation suggestions later superseded by client communication. The written decision file does not attach the client communication itself.

Unresolved question. Confirm in writing that GIPA accepts a Laravel admin UI in place of Django admin. Until that confirmation is on record, the texts conflict. This report does not silently delete the TOR sentence.

### Fixed workflows versus configurable workflow templates

Documented requirement. Opportunity and district state names are specified. Documented requirement. Workflow settings and workflow templates are also required API and admin capabilities.

These can be reconciled if templates only configure SLA, assignees, and notifications, while states stay fixed. They conflict if templates can rename or skip states. The TOR does not say which reading is intended.

### Mandatory full data form versus configurable fields

Documented requirement. The listed fields are mandatory unless GIPA marks them optional. Documented requirement. The same data points "shall be configurable". Configurable may mean admin-managed reference data, or it may mean the form itself can be edited. Those readings differ.

### Two status vocabularies on one opportunity

Documented requirement. Project status is Not Started, In Progress, On Hold. Documented requirement. Lifecycle status runs from draft to completed/cancelled. Nothing says they are the same field. Treating them as one field would drop one of the two requirements. The demo kept them separate and then used a project status value, `proposed`, that is in neither list.

### Public API exposure versus minimising personal data

Documented requirement. Contact details are part of the opportunity and core resources are exposed on the API. Documented requirement, NFR-03 and the government-system rule. Security and least exposure. Publishing promoter phone and email to anonymous clients may conflict with that duty. The TOR does not resolve it.

### "No security vulnerabilities" versus a penetration test

Documented requirement. The contractor performs penetration tests so that there are no security vulnerabilities. Documented requirement. A review happens before go-live. A test that finds issues is not a failed architecture. The acceptance bar for residual risk is not defined. The absolute sentence and a real test process pull in different directions.

### Horizontal scaling versus an unspecified single hosting arrangement

Documented requirement, NFR-02. Horizontal scaling of application and database. Documented requirement. The contractor deploys into the hosting environment it provides. Those do not conflict if the environment can run more than one application instance and a scalable database. They conflict if the accepted host is a single virtual machine with local disk. The TOR does not describe the host.

### Demo and rule scope versus the TOR

Not a contradiction inside the TOR. It is a contradiction between sources if someone treats them as equal.

The demo and the pasted "Main Components" block include investor onboarding and aftercare, certificates and anti-forgery, matchmaking, ORC, payments, and an AI assistant. The TOR's feature list, FR catalogue, and objectives do not. `05-requirements.mdc` says the requirements document is authoritative and that an AI assumption must not override it. Building those extra capabilities as if they were FR items would contradict the TOR's scope.

### PostGIS versus the demo database

Documented requirement. PostGIS-backed boundaries and locations. Demo observation. MySQL-compatible schema. The production rules require PostgreSQL and PostGIS. The demo is not evidence that the geospatial requirement is already met.

### Audit "before and after" in the rules versus the TOR's shorter audit sentence

The rules and the demo store before and after images. The TOR requires a complete tamper-evident log of material actions and does not require a full row image. This is an elaboration, not yet a contradiction, unless before-and-after storage copies personal data the TOR also says to minimise. That tension should be resolved when the audit payload is decided.

---

## 27. Requirements requiring client clarification

The following decisions block a safe implementation plan. They are unresolved questions, grouped so a client workshop can answer them. Suggested answers are not included, because an answer here would be an unapproved design.

### Scope

1. Confirm that investor onboarding, KYC, aftercare, certificates, anti-forgery, matchmaking, the AI assistant, SMS, ORC, and payments are out of scope for this build unless a new written requirement adds them.
2. Confirm the official IOMP Data Form, including which fields are optional, their units, and their validation limits.
3. Confirm whether ROI, IRR, NPV, payback period, and projected revenues are mandatory, and in what units.
4. Provide the authoritative region list, district list, and boundary file, and the coordinate reference system.
5. Confirm whether the count of districts is allowed to change after go-live.

### Workflow and SLA

6. Define the opportunity state machine, including reject destination, cancel, complete, and the path from approved to active.
7. Define the district state machine, including reject destination and whether unpublish exists.
8. Define who may submit, approve, reject, reassign, activate, complete, and cancel, and whether the approver must differ from the submitter.
9. Define how project status relates to lifecycle status.
10. Set SLA durations, clock start, calendar, pause rules, warning points, recipients, and repeat policy, for opportunities and, separately, for districts if districts have an SLA.
11. Define which states are visible on the public site and which enter each report.

### Data meaning

12. Define investment type, or remove it in favour of enterprise type, sector, or investment structure.
13. Define the investment-structure model: single choice, multiple choice, or amounts per component.
14. Define currency rules for USD and GHS.
15. Define the DRS scale, formula, data year, and who updates it.
16. Define the fields behind job creation, infrastructure quality, and the timeline report.
17. Define whether an opportunity point must lie inside its district.
18. Define document visibility: public, staff-only, or per document.
19. Define inquiry states, assignment, response, and notification recipients, including whether the inquirer gets email.
20. Define admin messaging: who may message whom, and retention.

### Access, privacy, and API

21. Provide the role and permission matrix, including district assignment rules.
22. State whether investors need accounts or only anonymous inquiries.
23. State which API resources are public.
24. Choose date-based or semantic versioning, and the compatibility period.
25. State the personal-data lawful basis, consent needs, and retention for contacts and inquirers.
26. Confirm that the admin UI is the Laravel UI recorded in the implementation decision, and that Django is not required.

### Quality bar

27. Set the performance workload: volumes, concurrency, and the public page-load budget.
28. Set the availability target, recovery time objective, and recovery point objective.
29. Set backup frequency, retention, and restore-test expectations.
30. Set the test-coverage target and the penetration-test acceptance bar.
31. Set the WCAG exception list, if any, especially for the map.
32. Name the legislation and security standard the build will be audited against.
33. Name the production host and who operates it after warranty.
34. Set the warranty length. Twelve months is only the TOR's example.
35. Set the maintenance-mode HTTP contract for the API.

---

## Decisions this report does not make

- It does not choose the bounded-context map in section 5.
- It does not choose aggregate boundaries, event names, or table designs.
- It does not choose an API payload, a versioning scheme, or a public-field list.
- It does not set SLA numbers, role matrices, or report formulas.
- It does not bring demo programmes into scope.
- It does not treat empty ADR files as decisions.
- It does not treat `TechDoc.txt` as an IOMP source.

The next useful artefact, after the client answers section 27, is a set of architecture decision records that replace the empty ADR placeholders. Those records should cite the TOR identifier they implement and the client answer that closed each ambiguity.
