# IOMP requirements traceability

Reconciliation update: 28 September 2026.

This matrix replaces the earlier three-row stub. Implementation and test columns stay empty because production implementation has not started. That emptiness is intentional.

Precedence for every row is ADR-0013: explicit client decision, updated requirements, accepted ADR, current approved requirements, original TOR, then assumption.

| Requirement | Client decision or ADR | Module | Design | Implementation | Tests | Status |
| --- | --- | --- | --- | --- | --- | --- |
| FR-01 Geography registry | ADR-0002, ADR-0003 | Geography | District and region registry. Do not hardcode 261. | Not started | Not started | Open for build |
| FR-02 District workflow and boundaries | ADR-0007, ADR-0010 | Geography | Draft, under review, published. Reject destination remains AMB-09. | Not started | Not started | Open for build |
| FR-03 Opportunity capture | ADR-0002, ADR-0008 | Opportunity Management | Data form, documents private by default. | Not started | Not started | Open for build |
| FR-04 Opportunity workflow | ADR-0010. Client-INV-04 for visibility | Opportunity Management | Draft through completed, cancellation terminal. Investor catalog requires both approved and active. | Not started | Not started | Visibility decided. Rejection and activation rules open |
| FR-05 Cascading filters | ADR-0007 | Discovery | Same filters for investor discovery. Investor-only result set. | Not started | Not started | Open for build |
| FR-06 Geospatial queries | ADR-0003, ADR-0007 | Geospatial | PostGIS. Sample SQL in ADR-0007 is not policy. AMB-30. | Not started | Not started | Open for build |
| FR-07 Investment map | ADR-0007, Client-INV-04 | Geospatial | Map points use the same visibility rule as lists. | Not started | Not started | Visibility decided |
| FR-08 Inquiry | Superseded in part by Client-INV-05 and Client-INV-06. ADR-0012 | Opportunity Engagement | Verified investor, not anonymous public submit. | Not started | Not started | Capability decided. States open |
| FR-09 Access | Client-INV-01 to Client-INV-03. ADR-0005, ADR-0006, ADR-0011 | Identity and Investor Onboarding | Register, verify email, verify phone, then authorize. | Not started | Not started | Investor gate decided. Role matrix open |
| FR-10 Audit | ADR-0009 | Audit | Include investor registration, verification, interest, and inquiry. | Not started | Not started | Open for build |
| FR-11 Notifications | ADR-0010, ADR-0011 | Notifications | Email for verification and engagement as well as workflow. | Not started | Not started | Open for build |
| FR-12 Maintenance | ADR-0001 operations | Platform | Unchanged from TOR. | Not started | Not started | Open for build |
| FR-13 Reporting | Reporting context | Reporting | Investor dashboard shows investor-appropriate data only. Metric definitions remain AMB-25. | Not started | Not started | Open for build |
| FR-14 Admin UI | Implementation decision. Django wording superseded | Administration | Laravel, Blade, Alpine.js, Tailwind CSS. | Not started | Not started | Stack decided |
| FR-15 API | ADR-0004, Client-INV-04 | API | `/api/v1/`. Protected opportunity resources are not anonymous. | Not started | Not started | Visibility decided. Version scheme date-versus-semantic still open |
| FR-16 Admin onboarding and messaging | Separate from Client-INV-01 | Messaging | Admin tours are not investor verification. | Not started | Not started | Split decided. Messaging rules AMB-23 |
| FR-17 PWA | Implementation decision section 21 | Platform | Offline fallback must not bypass authorization. AMB-24. | Installable manifest and a navigation-only service worker. Failed page loads show a static offline notice. Authenticated pages and form posts are not cached. Health remains `GET /up`. | Implemented | Authoritative offline writes remain out of scope |
| FR-18 Settings | Administration context | Administration | Workflow settings must not bypass the investor visibility invariant. | Not started | Not started | Open for build |
| NFR-01 to NFR-09 | Unchanged quality targets | Platform | Load, uptime, and coverage numbers remain to be agreed. | Not started | Not started | Targets incomplete |
| Client-INV-01 Registration | Explicit client decision. ADR-0011 | Investor Onboarding | Direct self-registration is for investors. Staff invitation is ADR-0005. | Not started | Not started | Decided |
| Client-INV-02 Email verification | Explicit client decision. ADR-0011, ADR-0005 | Investor Onboarding | Separate trust signal. Failure policy is AMB-02. | Not started | Not started | Decided |
| Client-INV-03 Phone verification | Explicit client decision. ADR-0011, ADR-0005 | Investor Onboarding | Separate trust signal. Failure policy is AMB-02. | Not started | Not started | Decided |
| Client-INV-04 Protected catalog | Explicit client decision. ADR-0011, ADR-0006 | Opportunity access | Both approved and active. Enforced on UI, API, search, and map. | Not started | Not started | Decided |
| Client-INV-05 Interest and inquiry | Explicit client decision. ADR-0012 | Opportunity Engagement | Two concepts. Not a boolean flag. | Not started | Not started | Capability decided. Workflow open |
| Client-INV-06 Management review | Explicit client decision. ADR-0006, ADR-0012 | Opportunity Engagement | Permission-controlled. Not the opportunity lifecycle. | Not started | Not started | Review duty decided. States open |
| Certificates | Current requirements section 19. ADR-0003, ADR-0008 | Certificates | Lifecycle events, not a single valid flag. Business rules AMB-21 and AMB-22. | Not started | Not started | In scope. Rules open |
| Matchmaking and aftercare | Current requirements sections 3 and 5 | Opportunity Engagement | Continuation after management processing. AMB-05 and AMB-28. | Not started | Not started | In scope. Rules open |
| AI assistant | Client decision, AMB-29 approved | Assistant | Offline retrieval is the default. OpenAI is a config driver and is unused until a key is set. Public answers must not list opportunities. Certificates are not in the knowledge base. | Knowledge documents, live tools, widget on the public site and the investor workspace, staff editor, tests. Write-up in `.cursor/ai-assistant.md`. | Implemented | Hosted model waits on an API key |
| Database backup | Project requirements section 30. Operations rule. NFR-04. | Backup | Frequency, retention, encryption, and a restoration procedure. A backup that has not been opened is not treated as proven. No restore from the browser. | Admin screen for Super Administrator and Content / Data Manager. Daily database dump, retention, integrity check, download, and delete. | Implemented | Live restore test remains an operator exercise |
