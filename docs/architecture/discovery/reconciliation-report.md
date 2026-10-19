# IOMP Requirements and Architecture Reconciliation

Date: 28 September 2026

Scope: documentation only, for `gipa_prod`. No application code was changed.

Precedence used: ADR-0013. An explicit client decision outranks the original Terms of Reference. The investor journey in this report is that client decision. It is not an assumption and it is not out of scope.

The working requirements file on disk is `.cursor/docs/requirements/implementation-decisions.md`. There is no separate `implementation-decision.md`. This report uses the file that exists.

## 1. Executive Summary

The discovery report treated investor registration, verification, the investor dashboard, matchmaking, aftercare, and certificates as outside production scope because the original Terms of Reference did not fully describe them. That conclusion is superseded.

The client has confirmed a verified-investor journey. Protected opportunity list, detail, search, and map points are available to investors only after registration, email verification, and phone verification, and only when the opportunity is both approved and active. Authorized GIPA management personnel review interest and inquiry.

Accepted ADRs 0001 through 0013 already record the modular monolith, PostGIS, JWT, authorization, workflow, investor onboarding, and engagement. This reconciliation aligns the discovery report, the ambiguities register, traceability, and a few ADR sentences that still read as if approval alone, or an anonymous public catalog, were enough. It does not add a new ADR.

The documentation is ready for implementation planning of the confirmed journey. It is not ready to invent engagement states, aftercare states, public summary fields, or the matching algorithm. Those remain open.

## 2. Confirmed Client Decisions

| Identifier | Decision | Label |
| --- | --- | --- |
| Client-INV-01 | An investor must register before receiving protected opportunity access. | Confirmed client decision |
| Client-INV-02 | Email verification is required. | Confirmed client decision |
| Client-INV-03 | Phone verification is required. | Confirmed client decision |
| Client-INV-04 | The verified investor dashboard shows opportunities that are both approved and active, including list, detail, and map points. | Confirmed client decision |
| Client-INV-05 | The verified investor may express interest and/or submit an inquiry. | Confirmed client decision |
| Client-INV-06 | Authorized GIPA management personnel review that engagement and may approve, reject, or otherwise process it. | Confirmed client decision |
| Continuation | Further engagement, matchmaking, and aftercare follow that review. | Confirmed client decision that the continuation exists. The states are unresolved. |

Unverified investors do not receive the catalog. Registration without both verifications is not access.

These items are also written in `.cursor/docs/requirements/project-requirements.md` sections 4 and 5, `.cursor/docs/requirements/implementation-decisions.md` sections 9 and 10, ADR-0011, ADR-0005, ADR-0006, and ADR-0010.

## 3. Discovery Findings Now Resolved

| Earlier discovery finding | Resolution |
| --- | --- |
| Whether an investor is only an anonymous inquiry submitter | Resolved. The investor is a registered, email-verified, and phone-verified user. |
| Investor onboarding marked out of scope | Resolved. It is Client-INV-01 through Client-INV-03. |
| Investor dashboard marked out of scope | Resolved. It is the verified investor's entry to the catalog. |
| Public opportunity list, detail, and map as the production default | Resolved for protected data. Those surfaces require a verified investor when they show opportunity records. |
| Approved versus active as an open visibility question (former AMB-08) | Resolved. Both states are required. Approved alone does not publish to investors. |
| Completed/cancelled as possibly investor-visible | Resolved for the catalog. They are not in the verified investor catalog. |
| Inquiry as a public form | Resolved. Inquiry and interest are verified-investor actions, then management review. |
| FR-16 "onboarding" confused with investor registration | Resolved as two different meanings. Admin tours remain FR-16. Investor verification is the client decision. |
| ADR files are empty placeholders | Resolved. ADR-0001 through ADR-0013 are accepted documents. |
| Django admin still blocking the stack | Already resolved before this pass by the implementation decision. The discovery conflict note remains only as history. |
| Workflow as an undecided shared status column | Resolved at architecture level by ADR-0010. Opportunity, district, onboarding, and engagement are separate workflows. |

## 4. Discovery Findings Superseded

| Superseded text | Replaced by |
| --- | --- |
| "Out of production scope: investor onboarding, certificates, matchmaking" | Current requirements sections 3, 5, and 19, plus ADR-0011 and ADR-0002. Certificates are in scope. Matchmaking and aftercare are in scope as the continuation, with open state definitions. |
| Terms of Reference public opportunity browser as the implementation rule | Client-INV-04 and ADR-0013. The TOR sentence remains historical. It does not authorize an anonymous catalog. |
| Discovery section 27 item that asked the client to confirm those features are out of scope | Removed from the active question list in the discovery report. |
| Cursor rule prototype modules A and G describing a public opportunity list and a public inquiry form | Updated in `00-project.mdc`, `05-requirements.mdc`, and `20-ddd.mdc` so they no longer contradict the client decision. |
| ADR-0011 unresolved bullet asking whether investors see all active opportunities or only a matched subset | Closed for the catalog. Verified investors see approved and active opportunities. Matching must not hide that catalog until GIPA defines a filter. The algorithm remains AMB-05. |
| ADR-0007 sample of 5 kilometres and Amazon RDS, if read as policy | Marked illustrative. Radius and host are not decided. |

The original TOR text was not deleted. Traceability records the deviation.

## 5. Remaining Ambiguities

Confirmed items in section 2 are not in this list.

| ID | Still open | Why it is still open |
| --- | --- | --- |
| AMB-01 | Whether any limited public opportunity summary exists, and which fields | The client decision forbids protected opportunity data for visitors. It does not say whether a title or sector teaser is public. |
| AMB-02 | Verification expiry, resend, attempts, lockout, and partial login | Phone and email verification are required. Failure behaviour is not specified. |
| AMB-03 | Whether GIPA must approve the investor after email and phone verification | The two verifications are confirmed. An extra institutional approval step is not. |
| AMB-04 | Mandatory investor profile fields | No field list beyond the account and the two verifications was confirmed. |
| AMB-05 | Matching algorithm | Investors must be able to identify suitable opportunities. The scoring rules are not confirmed. |
| AMB-06 | Expression-of-interest states | Illustrative states in the ambiguities file are not approved. |
| AMB-07 | Whether inquiry and interest share one state machine | ADR-0012 decided they are different concepts. The shared workflow question remains open. |
| AMB-09 | Opportunity rejection destination | Unchanged. |
| AMB-10 | Conditions for active to completed | Unchanged. |
| AMB-11 | Who may cancel, from which states | Unchanged. |
| AMB-12 | SLA calendar | Unchanged. |
| AMB-13 | SLA pause and resume | Unchanged. |
| AMB-14 | Which roles are district-scoped | District enforcement is decided. The role matrix is not. |
| AMB-15 | Region-level assignment | Unchanged. |
| AMB-16 | Whether internal users see all opportunities or only a scope | Unchanged. |
| AMB-17 | Whether financial metrics are entered, calculated, or imported | Unchanged. |
| AMB-18 | Exchange rates and extra currencies | USD and GHS are required. Conversion source is not. |
| AMB-19 | Document size, types, versioning, and download rights | Unchanged. |
| AMB-20 | ORC, GIS, and payment interface contracts | Anticipated. Not specified. |
| AMB-21 | Certificate types, issuers, triggers, numbering, validity | The capability and the event names are in scope. The business rules are not. |
| AMB-22 | What a public certificate verification response may return | Public verification is anticipated. The payload is not. |
| AMB-23 | Admin messaging product rules | Unchanged. |
| AMB-24 | Whether offline mode may capture authoritative writes | Default remains no, until confirmed. |
| AMB-25 | Report formulas for jobs, infrastructure, and related metrics | Unchanged. |
| AMB-26 | Retention beyond audit | Unchanged. |
| AMB-27 | Legal privacy basis for investor data | Unchanged. |
| AMB-28 | Aftercare and further-engagement states | The continuation is confirmed. The state machine is not. |
| AMB-29 | Whether the AI assistant is approved for this release | Requirements mention it only where separately approved. The investor decision does not approve it. The rules still list it as a main component. That conflict stays open rather than being deleted. |
| AMB-30 | Nearby radius, result bound, and spatial reference system | ADR-0007 requires a documented SRS and bounded queries. The numbers in the sample are not the decision. |
| AMB-31 | Which fields and documents a verified investor may see | Access to the opportunity is confirmed. The field contract is not. Documents are not automatically included. |
| AMB-32 | Who activates an opportunity and what must be true before activation | Visibility needs both states. The activate transition's business rule is not defined. Do not assume approval auto-activates. |

Former AMB-08 is not an open ambiguity.

## 6. Requirements ↔ ADR Conflicts

| Topic | Conflict found | What this pass did | Still unresolved |
| --- | --- | --- | --- |
| Investor catalog | ADR-0006 said "approved/active", which can be read as either state. The client decision and implementation decision require both. | ADR-0006 now says both approved and active, and excludes draft, pending, completed, and cancelled from the investor catalog. | None for the visibility gate. |
| Matched subset | ADR-0011 left open whether investors see every active opportunity or only matches. That contradicted the confirmed catalog. | The unresolved bullet was closed. Matching stays AMB-05 and must not hide the catalog. | Algorithm only. |
| Inquiry versus interest | AMB-07 asked if they might be the same process. ADR-0012 already defines them as two concepts. | AMB-07 is partly decided. Lifecycle sharing stays open. | State machines. |
| Staff registration | ADR-0005 says only investors self-register and staff are invited. The requirements actors section did not say this. | Reflected into project-requirements section 13 as an existing architecture decision, not as a new one. | Invite expiry and invitation roles are not specified. Not given a new AMB because the ADR already sets the rule. If the client rejects invitations, that needs an explicit decision that supersedes ADR-0005. |
| Geospatial sample | ADR-0007 sample SQL could be read as a 5 kilometre rule and an Amazon RDS host. | Marked illustrative. | AMB-30. |
| Workflow wording | ADR-0010 context said "active/approved". | Aligned to both states. | Rejection destination and activation preconditions. |
| AI assistant | ADR-0001 names an AI Assistant module. Requirements allow it only where separately approved. Rules list it as component 5. | No new ADR. Recorded as AMB-29. | Build approval. |
| Certificate events | Requirements, ADR-0003, and the implementation decision agree on ISSUED, VERIFIED, UPDATED, SUSPENDED, and REVOKED. | No conflict requiring a rewrite. | AMB-21 and AMB-22. |

No new ADR was created. The edits above make accepted decisions consistent. They do not choose verification providers, engagement states, or public teaser fields.

## 7. TOR ↔ Current Requirements Differences

| TOR | Current requirement | Classification |
| --- | --- | --- |
| Public opportunity list, detail, compare, search, and map | Protected opportunity data is for verified investors. Compare remains a TOR feature and must follow the same visibility rule if implemented. | TOR wording superseded for protected data. AMB-01 remains for a possible public summary. |
| Public inquiry submission | Verified investor interest and/or inquiry, then management review | Superseded for protected opportunities. |
| No investor account, email verification, or phone verification | All three are required | Confirmed client decision beyond the TOR. |
| No investor dashboard | Required | Confirmed client decision. |
| Opportunity states written as "completed/cancelled" in one label | Completed, with cancellation as a separate terminal path | Architecture and requirements baseline. The slash is not one state. |
| Django admin | Laravel, Blade, Alpine.js, Tailwind CSS | Implementation decision. Already recorded. |
| "Investment type" in filters, undefined in the field list | Still undefined | Remains ambiguous. Not resolved by the investor decision. |
| Job creation and infrastructure quality reports without source fields | Still undefined | AMB-25. |
| Email notifications | Still required, and now also cover registration, verification, interest, and management review | Expanded, not removed. |
| 261 districts | Initial data set, not a hardcoded rule | Unchanged. |
| PostGIS, JWT, `/api/v1/`, audit, PWA, maintenance, WCAG | Still required | Unchanged. |
| Certificates, matchmaking, ORC, payments, assistant | Not in the TOR feature list. Present in the current baseline at different strengths | Certificates and the matchmaking/aftercare continuation are in scope. Interface contracts and the assistant are not specified enough to build. |

## 8. Investor Journey

Confirmed client decision. This sequence is authoritative.

```text
Public visitor
    ↓
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
Approved and active investment opportunities
    ↓
Investor reviews opportunities
    ↓
Investor expresses interest and/or submits an inquiry
    ↓
Authorized GIPA management personnel review
    ↓
Management approval, rejection, or other processing
    ↓
Further engagement, matchmaking, and aftercare
```

Rules that sit on this sequence:

- The last step exists. Its states are AMB-28 and must not be invented.
- Interest and inquiry are both allowed. They are distinct concepts under ADR-0012. Their state machines are AMB-06 and AMB-07.
- Management processing is required. It is not the opportunity lifecycle.
- An opportunity being approved and active does not mean an investor's interest has been approved.

## 9. Opportunity Visibility Rules

| Actor | Opportunity list, detail, search, filters, and map points | Other notes |
| --- | --- | --- |
| Public visitor | No protected opportunity data. | Landing and explicitly public information remain. A public summary is AMB-01, not a default catalog. |
| Unverified investor | No protected opportunity data. | Registration, or only one of the two verifications, is not enough. |
| Verified investor | Opportunities that are both approved and active. | Draft, pending approval, completed, and cancelled are excluded. Field-level detail is AMB-31. |
| GIPA management personnel | Records allowed by role, permission, and district or organizational scope. | Includes workflow states the investor cannot see, where the role allows. Exact matrix is AMB-14 and AMB-16. |
| System administrator | Administrative access under least privilege. | Not a bypass of audit. Not a reason to expose investor-only data on public endpoints. |

District publication and opportunity visibility are different gates. A published district does not make its opportunities public.

## 10. Authorization Implications

Architecture decision, ADR-0006, aligned to the client decision.

- Enforce the rule on the server for pages, APIs, exports, and workflow actions.
- A valid JWT is not authorization.
- An investor JWT does not grant management actions.
- An investor may submit interest or inquiry only when verified, and only against an opportunity that is both approved and active.
- Management review requires an explicit permission. Ability to edit an opportunity does not include ability to decide an engagement.
- District scope stays server-side. The role-to-district matrix is still AMB-14.
- Object-level checks apply. Knowing an opportunity identifier is not access.
- Staff invitation versus investor self-registration follows ADR-0005.

## 11. API Implications

Architecture decision, ADR-0004, plus Client-INV-04.

- `/api/v1/` remains the version.
- Opportunity collection, detail, nearby, bounding box, polygon, and GeoJSON responses that contain protected opportunity data require a verified investor or an authorized internal user.
- Anonymous calls must not return that data.
- Interest and inquiry creation are authenticated, verified-investor commands.
- Management processing endpoints are a different permission.
- Investor profile resources are in the current API resource list.
- Pagination, allowlisted filters, and allowlisted sorts still apply inside the authorized set.
- Caching or a read model must not keep a public copy of protected opportunities.
- Date-based versus semantic versioning is still an open TOR example, not decided by this reconciliation.

## 12. Geospatial/Map Visibility Implications

Architecture decision, ADR-0007.

- Map points use the same actor table as section 9.
- A viewport query must not return opportunities the caller cannot open as detail.
- District boundaries may still be shown where the district publication policy allows. That is not opportunity access.
- PostGIS remains the query engine. The browser must not download the full catalog to filter it.
- The 5 kilometre sample in ADR-0007 is not the nearby rule.
- MapLibre remains the primary map choice already recorded in ADR-0007. This reconciliation does not change that choice.

## 13. Audit and Compliance Implications

Architecture decision, ADR-0009 and ADR-0011.

Material events include investor registration, email verification, phone verification, account activation, access to protected opportunity data where policy requires it, interest submitted, inquiry submitted, assignment, management decision, and later engagement transitions.

Audit records stay append-oriented. Passwords, tokens, and phone verification secrets are not logged.

Public anonymous inquiry is not the audited event. The audited event is the verified investor's submission.

Retention periods beyond the configurable audit setting remain AMB-26 and AMB-27.

## 14. Data Model Implications

No tables are designed here.

Domain implications:

- An investor account has separate email-verified and phone-verified facts. A single "registered" flag is not the access rule.
- Opportunity lifecycle state stays on the opportunity. Investor visibility is derived from "approved and active" plus the caller's verification. It is not a second public/private boolean that can drift from the lifecycle.
- Expression of interest and inquiry are their own histories, each pointing at an investor and an opportunity. They are not columns on the opportunity.
- Management review state belongs to the engagement, not to the opportunity status.
- Matchmaking preferences may exist later. They must not become a hidden eligibility gate before AMB-05 is decided.
- Aftercare is a later relationship workflow. It needs its own state once AMB-28 is decided.
- Certificate lifecycle remains a history of events, not a single valid flag. That was already decided and is unchanged.
- Supporting documents stay private unless a later field policy includes a specific document.
- Monetary values stay precise and currency-explicit. USD and GHS remain the confirmed currencies.

## 15. Workflow Implications

Four workflows stay separate, per ADR-0010:

| Workflow | Confirmed shape | Still open |
| --- | --- | --- |
| Opportunity | Draft, pending approval, approved, active, completed. Cancellation is a terminal path. | Reject destination, cancel authority, activation preconditions. |
| District | Draft, under review, published. | Reject destination. |
| Investor onboarding | Register, verify email, verify phone, then verified access. | Failure and expiry. Extra GIPA approval is not assumed. |
| Investor engagement | Verified investor submits interest and/or inquiry, then management review, then further engagement. | The state names after submission. |

Do not store engagement approval in the opportunity status. Do not grant investor access when the onboarding workflow has only completed registration.

## 16. Traceability Changes

`.cursor/docs/requirements/requirements-traceability.md` was replaced on 28 September 2026.

The previous file had a three-row stub and a partial table that stopped at Client-INV-06 without FR-07, FR-12, FR-13, FR-16, FR-17, FR-18, the NFR row, certificates, or matchmaking.

The new matrix:

- keeps FR-01 through FR-18 and NFR-01 through NFR-09;
- adds Client-INV-01 through Client-INV-06 with ADR links;
- marks FR-08 as superseded in part, not deleted;
- marks FR-07 and FR-15 visibility as decided;
- records certificates as in scope with open business rules;
- records matchmaking and aftercare as in scope with open rules;
- records the AI assistant as not approved to build;
- leaves implementation and test cells empty.

`project-requirements.md` section 5 now lists the Client-INV identifiers next to the journey.

## 17. Remaining Client Decisions

Needed before the related slice is built. Not needed to accept the journey in section 8.

1. AMB-01 public summary, if any.
2. AMB-02 verification failure policy.
3. AMB-03 extra investor approval, if any.
4. AMB-04 profile fields.
5. AMB-05 matching rules.
6. AMB-06 and AMB-07 engagement state machines.
7. AMB-09, AMB-10, AMB-11, and AMB-32 opportunity transition rules.
8. AMB-12 and AMB-13 SLA calendar and pause.
9. AMB-14 and AMB-16 internal scope matrix.
10. AMB-21 and AMB-22 certificate business rules and public verification payload.
11. AMB-28 aftercare states.
12. AMB-29 assistant release approval.
13. AMB-30 spatial defaults.
14. AMB-31 investor-visible fields and documents.
15. AMB-17, AMB-18, AMB-19, AMB-25, AMB-26, and AMB-27 for finance, files, reports, and privacy.

If district management invitation is not the intended staff-join method, the client must say so. Until then ADR-0005 stands. This report does not reopen it.

## 18. Recommended Next Implementation Sequence

Planning order only. Do not implement from this report.

1. Identity and investor onboarding: registration, email verification, phone verification, verified and unverified states, staff invitation boundary, and audit of those events. Stop at the confirmed checks. Do not add a silent GIPA approval step.
2. Opportunity lifecycle with explicit transitions, including a distinct activate step, even if activation preconditions stay configurable until AMB-32 is answered.
3. Authorization gate used by every opportunity read: verified investor and both approved and active. Apply it before any public catalog is written.
4. Investor dashboard, list, detail, allowlisted search and filters, and map viewport, all using that same gate.
5. Interest and inquiry submission with audit and notification, stored as their own records, with a minimal submitted-and-under-review holding state only if the client accepts that holding state. If they have not, build the capture and the management queue as assigned work items without inventing accept, reject, and aftercare states.
6. Management review permissions and district scope, using the holding records from step 5.
7. Geography, boundary import, and district workflow, in parallel once the opportunity gate exists, because the map needs districts but must not leak opportunities.
8. Audit export, notification templates, and maintenance behaviour from the original TOR.
9. Certificates only after AMB-21 identifies what is being certified. The lifecycle shape is already decided, the business trigger is not.
10. Matchmaking and aftercare only after AMB-05 and AMB-28. Do not block steps 1 through 6 on them.
11. External ORC, GIS, and payment adapters only after AMB-20. The assistant only after AMB-29.

The production application is still a Laravel skeleton. The prototype may be read to see an earlier attempt. It is not the specification for these steps.

## Architectural decisions not created

This pass did not add ADR-0014 or any other new decision. The following still need a future ADR or a client answer before they become design:

- public summary field list, if AMB-01 allows one;
- engagement and aftercare state machines;
- phone verification provider, which ADR-0005 already calls an implementation concern once AMB-02 sets the user-visible failure policy;
- spatial radius and spatial reference system;
- certificate issuance triggers.

Writing those as code without an ADR or a client answer would be a silent architecture decision.
