IOMP Requirements Ambiguities and Open Decisions
Purpose

This document records requirements that are incomplete, potentially conflicting, or require an explicit business decision.

It must not be used to downgrade confirmed client requirements.

A requirement confirmed by the client is not an ambiguity merely because the original TOR did not describe it.

1. Confirmed Decisions — Not Ambiguities

The following were previously unclear from the original TOR but are now confirmed requirements.

1.1 Investor Registration

Investors must register before accessing protected investment opportunities.

1.2 Investor Verification

Investor access requires successful verification of:

email;

phone.

1.3 Investor Dashboard

Verified investors receive an investor dashboard.

1.4 Investor Opportunity Access

Approved and active opportunities are available to verified investors through the investor experience.

1.5 Investor Engagement

Verified investors can review opportunities and express interest and/or submit inquiries.

1.6 Management Review

Authorized GIPA management personnel review investor expressions of interest/inquiries and continue the appropriate workflow.

1.7 Approved and active means both states

Investor list, detail, search, filter, and map access require the opportunity to be both approved and active. Draft, pending approval, completed, and cancelled opportunities are outside that catalog. An approved opportunity that is not yet active is not investor-visible.

1.8 Unverified investors

Registration alone does not grant opportunity access. Email verification and phone verification are both required. This is not AMB-03. AMB-03 asks only whether a further GIPA approval step exists after those two verifications.

These are confirmed requirements and must not be treated as unresolved questions.

Reconciliation note, 28 September 2026. AMB-08 is decided for the visibility gate and is removed from the open list below. The historical question is kept under section 5 so the change is visible.

2. Genuine Open Requirements
AMB-01 — Public Opportunity Visibility
Question

Should any investment opportunity information remain publicly visible to unauthenticated visitors?

Current position

The confirmed investor access requirement establishes that protected opportunity information is available to verified investors.

It does not, by itself, establish whether a limited public summary, title, sector or location may remain publicly discoverable.

Required decision

GIPA should explicitly define:

whether opportunities are completely investor-only;

whether limited public summaries are allowed;

which fields, if any, are public.

Impact

Affects:

public portal;

API serialization;

search;

map;

SEO;

authorization;

data exposure.

AMB-02 — Investor Verification Failure
Question

What happens when email or phone verification fails, expires or is abandoned?

Required decision

Define:

verification expiry;

resend behavior;

maximum attempts;

account lockout;

support/admin intervention;

whether partially registered accounts can log in.

AMB-03 — Investor Account Approval
Question

Is successful email and phone verification sufficient to activate an investor account, or must GIPA approve the investor account?

Current position

Email and phone verification are confirmed.

Whether a separate institutional approval is required has not yet been established.

Impact

Affects:

onboarding;

authentication;

authorization;

investor access;

audit;

notifications.

AMB-04 — Investor Profile Requirements
Status: PARTLY DECIDED (2026-09-28). The minimum registration fields are decided (decision D-2): name, e-mail, phone, password, country. Everything below remains open.

Question

What information must an investor provide during registration beyond the decided minimum?

Potential categories include:

individual/company name;

country;

investment profile;

preferred sectors;

investment range;

enterprise type;

geographic preferences;

contact information;

supporting documents.

No additional mandatory field should be invented without client approval.

AMB-05 — Investor Matching Rules
Question

How should the system determine whether an opportunity fits an investor's needs?

Possible approaches include:

investor-selected filters;

explicit matching criteria;

rule-based matching;

weighted matching;

manual management review;

future AI-assisted matching.

Current position

The platform must enable investors to identify opportunities matching their needs.

The exact automated matching algorithm has not yet been confirmed.

AMB-06 — Expression of Interest Workflow
Question

What exact states should an investor expression of interest use?

For example:

submitted
→ under review
→ accepted
→ rejected
→ engagement
→ closed


The above is illustrative only and is not an approved workflow.

Required decision

GIPA must define the authoritative state machine, including:

valid transitions;

actors;

rejection behavior;

reassignment;

SLA;

notifications;

closure behavior.

AMB-07 — Inquiry and interest workflows
Status

PARTLY DECIDED on 28 September 2026 by ADR-0012. Do not reopen the question of whether they are one concept.

Decided

They are two concepts.

An inquiry is the investor asking to know more about that opportunity.

An expression of interest is the investor stating interest in that opportunity, and it may later be used for matchmaking.

Still open

Whether they share one state machine, whether management approval applies to both in the same way, and whether one automatically creates the other. ADR-0012 leaves those lifecycle questions unresolved. Do not invent the shared-or-separate state machine while closing this item.

AMB-08 — Removed from the open list on 28 September 2026

Not an ambiguity. Investor visibility requires both approved and active. See section 1.7 and section 5. Do not reclassify Client-INV-04 as open. Activation preconditions, which are a different question, are AMB-32.

AMB-09 — Opportunity Rejection Destination
Question

Where does an opportunity go after rejection?

Potential possibilities include returning to:

draft;

correction/revision;

previous workflow state.

No destination should be assumed.

AMB-10 — Opportunity Completion
Question

What business conditions allow an opportunity to move from active to completed?

Required clarification may include:

investment secured;

project commenced;

project completed;

management declaration;

investor confirmation;

external system confirmation.

AMB-11 — Opportunity Cancellation
Question

Who may cancel an opportunity, at which states, and for what reasons?

Required:

authorized roles;

valid source states;

reason requirement;

whether cancellation is reversible;

notification behavior.

AMB-12 — SLA Business Calendar
Question

Do SLAs operate using:

calendar time;

business days;

GIPA working hours;

public holidays;

configurable calendars?

AMB-13 — SLA Pause and Resume
Question

Which workflow states or actions pause an SLA?

This must be defined before hardcoding SLA calculations.

AMB-14 — District-Level Authorization
Question

Which roles are district-scoped and which roles have regional or national scope?

The system supports district-level authorization, but the exact role-to-scope matrix requires confirmation.

AMB-15 — Region-Level Authorization
Question

Should some users be assigned to regions rather than individual districts?

If yes, authorization must explicitly support the hierarchy.

AMB-16 — Administrative Opportunity Visibility
Question

Can all authorized GIPA personnel view all opportunities, or should opportunity access be restricted by region/district/organizational role?

AMB-17 — Financial Metrics
Question

Are ROI, IRR, NPV, payback period and projected revenue:

entered manually;

calculated by the platform;

imported from source data;

optionally provided?

If calculated, GIPA must provide authoritative calculation rules.

AMB-18 — Multi-Currency
Question

The TOR specifies USD and GHS.

Required clarification:

Are additional currencies expected?

Are exchange rates required?

If conversion is required, what is the authoritative exchange-rate source?

Should historical exchange rates be preserved?

AMB-19 — Supporting Document Rules

Required clarification:

maximum file size;

allowed file types;

maximum number of documents;

document versioning;

replacement rules;

document expiry;

download permissions;

retention.

AMB-20 — External Integration Specifications

The platform anticipates ORC, GIS, payment and other integrations.

The following require separate interface specifications:

authentication;

endpoints;

payloads;

ownership;

synchronization frequency;

failure handling;

retry rules;

source-of-truth rules;

reconciliation.

AMB-21 — Certificate Business Rules

The certificate capability requires confirmation of:

certificate types;

issuing authorities;

issuance triggers;

certificate numbering rules;

validity periods;

revocation authority;

amendment rules;

archival rules.

AMB-22 — Certificate Verification Visibility
Question

Is certificate verification completely public without authentication?

If so, define the minimum information returned by verification to avoid unnecessary disclosure.

AMB-23 — Messaging

The requirements mention administrative user-to-user messaging.

Clarify:

whether messaging is persistent;

whether attachments are supported;

whether read receipts are required;

whether messages are auditable;

retention;

deletion rules;

notification behavior.

AMB-24 — PWA Offline Scope

The PWA requirement confirms offline fallback and installability.

It does not establish whether authenticated users may perform offline data-entry or mutation.

Until confirmed, offline mode must not be assumed to permit authoritative offline writes.

AMB-25 — Reporting Definitions

Reports such as:

job creation;

infrastructure quality;

sector investment;

readiness distribution;

require authoritative metric definitions and source fields.

The system must not invent formulas.

AMB-26 — Data Retention

The system requires configurable audit retention, but broader retention periods for:

investors;

inquiries;

opportunities;

documents;

certificates;

messages;

require policy confirmation.

AMB-27 — Data Protection and Legal Retention

GIPA should confirm applicable retention, privacy and data-protection requirements for investor and contact information.

AMB-28 — Aftercare lifecycle
Status

OPEN

Question

Matchmaking and aftercare are in the confirmed investor journey as the continuation after management processing. The states, actors, and exit conditions of aftercare are not defined.

Required decision

Define the aftercare and further-engagement state machine, or explicitly defer it to a later release without allowing implementation to invent the states.

AMB-29 — AI assistant approval
Status

APPROVED


AMB-30 — Geospatial query defaults
Status

OPEN

Question

Nearby radius, maximum result size, and the documented spatial reference system are not decided. The sample SQL in ADR-0007, including a 5 kilometre illustration and an Amazon RDS sketch, is not a decision.

Required decision

Set the coordinate reference system, the default and maximum nearby radius, and the map result bound.

AMB-31 — Investor-visible opportunity fields
Status

OPEN

Question

Verified investors can open approved and active opportunities. The field set they may see, including promoter contact details and supporting documents, is not defined. ADR-0008 already says an attached document is not automatically visible.

Required decision

List investor-visible fields and document categories. Do not treat the full internal record as the investor contract.

AMB-32 — Activation preconditions
Status

OPEN

Question

Investor visibility requires both approved and active. The business meaning of the transition from approved to active is not defined: who may activate, and what must be true before activation.

Required decision

Define the activate action, the actor, and the preconditions. Do not infer that approval automatically activates the opportunity.

3. Rules for Handling Ambiguity

Until a business decision is confirmed:

Do not invent behavior.

Do not encode irreversible business policy.

Keep the implementation configurable where practical.

Document temporary assumptions explicitly.

Add tests only for confirmed behavior.

Update this document when a decision is made.

Update affected ADRs when the decision changes architecture.

Update the traceability matrix.

4. Resolution Status

Each ambiguity should eventually be marked:

OPEN

DECIDED

SUPERSEDED

When a decision is made, record:

decision;

date;

source;

affected requirements;

affected ADRs;

implementation impact.

5. Decisions recorded on 28 September 2026

These lines are the visible change log for this reconciliation. They are not open questions.

| Item | Status | Decision | Source | Affected requirements | Affected ADRs |
| --- | --- | --- | --- | --- | --- |
| Investor registration | DECIDED | Required before protected opportunity access. | Explicit client decision, Client-INV-01 | project-requirements sections 4.2 and 5 | ADR-0011, ADR-0005 |
| Email verification | DECIDED | Required. | Client-INV-02 | project-requirements section 5 | ADR-0011, ADR-0005 |
| Phone verification | DECIDED | Required. Mechanism and failure policy remain AMB-02. | Client-INV-03 | project-requirements section 5 | ADR-0011, ADR-0005 |
| Investor dashboard | DECIDED | Verified investors receive it. | Client-INV-04 | project-requirements sections 4.2 and 18 | ADR-0011 |
| Approved and active access | DECIDED | Both states are required. This closes former AMB-08 as a visibility question. | Client-INV-04, implementation decision section 10 | project-requirements section 6 | ADR-0011, ADR-0006, ADR-0010 |
| Interest and inquiry | DECIDED as capabilities | Both exist and are distinct concepts. Lifecycles remain open under AMB-06 and AMB-07. | Client-INV-05, ADR-0012 | project-requirements section 7 | ADR-0012 |
| Management review | DECIDED | Authorized GIPA management personnel review engagement. | Client-INV-06 | project-requirements sections 4.3 and 7 | ADR-0011, ADR-0012, ADR-0006 |
| Anonymous opportunity catalog | SUPERSEDED | The TOR public opportunity browser does not override investor-only protected data. | ADR-0013 precedence | TOR sections 3.0 and 3.1, read with Client-INV-04 | ADR-0011, ADR-0007 |
| Minimum investor registration fields | DECIDED (D-2) | name, e-mail, phone, password, country. AMB-04 stays open for anything further. | Developer approval of readiness report D-2 | project-requirements section 5; AMB-04 | ADR-0011 |
| Module layout, audit store, test database | DECIDED (D-1, D-3, D-5) | `app/Modules/{Context}/{Domain,Application,Infrastructure,Presentation}`; spatie/laravel-activitylog extended; tests on PostgreSQL. | Developer approval of readiness report | none | ADR-0014 |
| District officer opportunity entry | CLARIFIED | District officers create opportunities in draft, within district scope, for reviewer/admin approval and publication. Permission matrix still AMB-14. | Developer clarification | project-requirements section 4.5 | ADR-0006, ADR-0010 |
| Phone verification mechanism | TECHNICAL DEFAULT | Numeric one-time code behind the `PhoneVerificationSender` abstraction; provider not chosen. Business retry/expiry/lockout policy remains AMB-02. | ADR-0005 "implementation concern"; Slice 1 notes T-3 | AMB-02 | ADR-0005, ADR-0011 |