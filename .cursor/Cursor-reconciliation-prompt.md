You are working on the IOMP production project.

Do NOT modify application code yet.

Your task is to reconcile the project's requirements and architecture documentation after recent client decisions and ADR updates.

Primary project location:
gipa_prod/

The demo project:
gipa_demo/

The production project is the ONLY implementation target for now, unless the developer request you to work on some aspect of the gipa_demo/.

Use gipa_demo/ only as reference material where explicitly useful.

Read completely:

1. .cursor/requirements/project-requirements.md
2. .cursor/requirements/requirements-ambiguities.md
3. .cursor/requirements/implementation-decision.md
4. .cursor/docs/architecture/discovery/*
5. .cursor/docs/architecture/adr/*
6. .cursor/rules/*

Also review the original TOR where required for traceability.
gipa_demo/* is only there for you to pip and find your way back on truck and finetune the prod for industry standard built.

IMPORTANT CURRENT CLIENT DECISION:

The investor access model is CONFIRMED, not ambiguous and not out of scope.

The confirmed flow is:

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
Approved + active investment opportunities
    ↓
Investor reviews opportunities
    ↓
Investor expresses interest and/or submits inquiry
    ↓
Authorized GIPA management personnel review
    ↓
Management approval/rejection/processing
    ↓
Further engagement / matchmaking / aftercare workflow

Now active opportunity list/detail/map points are now available to registered verified investors. 

Do not downgrade this to an assumption.
Do not mark it out of scope.
Do not remove it because it was not fully described in the original TOR.

The original TOR is not the only source of truth.
The requirement precedence is:

1. Explicit current client decision
2. Updated client requirements
3. Approved ADRs
4. Current approved requirements
5. Original TOR
6. AI assumptions

TASK

Reconcile the discovery report against the current requirements and ADRs.

Do not implement features.

Do not modify:

- application code
- migrations
- controllers
- models
- routes
- frontend code
- configuration
- Git history

Documentation changes are allowed only where required to make the requirements/discovery/ADR documentation internally consistent.

Put your feature change implementation brief description into ./documentation/ and well labeled.

Perform the following:

1. Compare the discovery report against:
   - project-requirements.md
   - requirements-ambiguities.md
   - implementation-decision.md
   - ADR 0001 through ADR 0010, 0013
   - .cursor/rules/*

2. Identify every discovery finding that is now:
   - confirmed
   - superseded
   - resolved
   - still ambiguous
   - contradicted
   - missing

3. Pay particular attention to:
   - investor registration
   - email verification
   - phone verification
   - investor dashboard
   - investor-only opportunity access
   - approved + active opportunity visibility
   - investor expression of interest
   - investor inquiry
   - management review
   - management authorization
   - matchmaking
   - aftercare
   - opportunity workflow
   - district authorization
   - audit
   - API visibility
   - geospatial visibility
   - certificate lifecycle

4. Verify that investor-only opportunity visibility is consistently reflected across:
   - public portal requirements
   - API requirements
   - authorization requirements
   - map requirements
   - search/filter requirements
   - dashboard requirements
   - audit requirements

5. Identify any contradictions between:
   - TOR
   - current requirements
   - ADRs
   - Cursor rules

6. Do NOT silently resolve a contradiction.
   If it requires a business decision, record it in requirements/requirements-ambiguities.md and clearly under line for proper future reference and noticing.

7. Remove or update ambiguity entries that are no longer ambiguous because the client has explicitly decided them.

8. Ensure the following are NOT incorrectly classified as ambiguities:
   - investor registration
   - email verification
   - phone verification
   - investor dashboard
   - approved + active opportunity access
   - investor interest/inquiry
   - management review

9. Ensure ADRs do not contradict the current requirements.

10. Ensure the discovery report distinguishes:
    - documented requirement
    - confirmed client decision
    - architecture decision
    - proposed design
    - assumption
    - unresolved question

11. Update the traceability documentation where necessary and make your changes to it noticeable.

12. Do not create new architecture decisions silently.
    If a new architectural decision is required, report it separately and explain why.

OUTPUT

Produce a reconciliation report at:

docs/architecture/discovery/reconciliation-report.md

The report must contain:

# IOMP Requirements and Architecture Reconciliation

## 1. Executive Summary

## 2. Confirmed Client Decisions

## 3. Discovery Findings Now Resolved

## 4. Discovery Findings Superseded

## 5. Remaining Ambiguities

## 6. Requirements ↔ ADR Conflicts

## 7. TOR ↔ Current Requirements Differences

## 8. Investor Journey

Show the confirmed investor journey as a complete sequence.

## 9. Opportunity Visibility Rules

Clearly distinguish:
- public visitor
- unverified investor
- verified investor
- GIPA management personnel
- administrators

## 10. Authorization Implications

## 11. API Implications

## 12. Geospatial/Map Visibility Implications

## 13. Audit and Compliance Implications

## 14. Data Model Implications

Do NOT design database tables yet.
Describe domain/data implications only.

## 15. Workflow Implications

## 16. Traceability Changes

## 17. Remaining Client Decisions

## 18. Recommended Next Implementation Sequence

The final section should recommend the order in which the production system should be built, but MUST NOT implement anything.

IMPORTANT:

Do not modify application code yet.

Do not install packages.

Do not create migrations.

Do not create controllers.

Do not create models.

Do not create routes.

Do not create tests.

Do not modify Git not at all throughout.

Documentation reconciliation only at this stage.

After completing the report, provide a concise summary of:
- files changed
- files created
- contradictions found
- ambiguities remaining
- whether the documentation is ready for implementation planning

Do not start feature implementation until this reconciliation is complete and reviewed.
