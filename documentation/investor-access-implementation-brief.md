# Feature change brief: verified investor access

Status: documentation only. Do not implement from this brief until the reconciliation report has been reviewed.

Date: 28 September 2026

Target: `gipa_prod` only.

## What changed in the requirements

The original Terms of Reference allowed a public reader to browse opportunities and submit an inquiry without an account. The current client decision replaces that for protected opportunity data.

The production behaviour to plan, and not to build in this change, is:

1. A public visitor may register as an investor.
2. The investor verifies email and then phone.
3. Only then is the investor account verified.
4. The verified investor opens an investor dashboard.
5. That dashboard lists, filters, and maps opportunities that are both approved and active.
6. The investor reviews those opportunities.
7. The investor expresses interest and/or submits an inquiry.
8. Authorized GIPA management personnel review that engagement.
9. Management approval, rejection, or other processing follows the engagement workflow once its states are confirmed.
10. Further engagement, matchmaking, and aftercare continue from that review. Their states are not defined yet and must not be invented in the first slice.

## Visibility rule to preserve in every later slice

| Actor | Protected opportunity list, detail, search, and map points |
| --- | --- |
| Public visitor | No |
| Registered investor, email or phone not verified | No |
| Verified investor | Yes, only where the opportunity is both approved and active |
| Authorized GIPA management | According to role and district scope, including non-public lifecycle states |
| System administrator | According to administrative permission, not by skipping audit |

Draft, pending approval, completed, and cancelled opportunities stay out of the verified investor catalog.

## What this brief does not authorize

- Application code, migrations, routes, controllers, models, tests, or Git changes.
- A public anonymous inquiry form for protected opportunities.
- Treating interest as a boolean on the opportunity.
- Hiding the approved-and-active catalog behind an unconfirmed matching algorithm.
- Implementing the AI assistant.
- Copying `gipa_demo` behaviour that the ambiguities register still marks open.

## Open items that block later slices

Verification failure behaviour (AMB-02), extra investor approval (AMB-03), profile fields (AMB-04), matching rules (AMB-05), engagement states (AMB-06 and AMB-07), aftercare states (AMB-28), investor-visible fields (AMB-31), and activation preconditions (AMB-32).

The registration, verification, dashboard, and approved-and-active catalog are not in that blocked list.
