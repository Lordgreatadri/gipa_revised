0013 — Requirements and client decision precedence

# ADR-0013: Requirements and Client Decision Precedence

## Status

Accepted

## Context

The IOMP implementation has evolved through client discussions and decisions
after the original TOR was produced.

Some current production requirements are therefore more specific than, or
different from, the original TOR wording.

Treating the original TOR as an immutable implementation specification would
risk implementing behavior that the client has subsequently changed.

## Decision

IOMP requirements shall follow this precedence:

1. Explicit current client decision
2. Updated client requirements provided by the client/project owner
3. Approved Architecture Decision Records
4. Current approved functional requirements
5. Original TOR requirements and implementation wording
6. AI or developer assumptions

Where a higher-priority requirement conflicts with lower-priority documentation,
the higher-priority requirement governs implementation.

## Traceability

Material deviations from the original TOR must be traceable.

Where practical, maintain:

Requirement
→ Architecture Decision
→ Module
→ Implementation
→ Test

The traceability record must identify superseded or amended requirements rather
than silently overwriting historical requirements.

## No silent interpretation

Developers and AI assistants must not invent business behavior when the
requirements are incomplete.

An unresolved business rule must be:

- identified
- documented
- isolated from unrelated implementation
- escalated for confirmation where necessary

## Architectural impact

If a client decision changes:

- bounded contexts
- authorization boundaries
- authentication
- persistence
- workflows
- API contracts
- security boundaries
- deployment architecture

the change must be evaluated for an ADR update.

## Consequences

The TOR remains an important source of requirements and contractual context,
but current approved client decisions govern where they supersede the TOR.

This prevents implementation from being artificially constrained by
superseded wording while preserving traceability.
