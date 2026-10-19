0005-api-authentication.md

# ADR-0005: Authentication Architecture

- Status: Accepted
- Date: 2026-09-28
- Decision Type: Security Architecture
- Scope: Authentication

## Context

IOMP has multiple user populations:

- public visitors;
- registered investors;
- management/admin personnel, district level;
- district officers;
- content managers;
- IT/system administrators;
- compliance personnel
- other authorized internal users.

The API requires JWT authentication.

Staff will be invited by their district management to use invite link to sign up and update their profile, for best security.
Only investor can register directly.

Investor access has also been explicitly confirmed as a business requirement.

Approved/active investment opportunities are available to authenticated investors after successful registration and verification.

## Decision

Authentication will distinguish between:

### Public Users

Unauthenticated visitors may access public platform functionality that is explicitly designated as public.

Public access must not expose protected opportunity information where the business rules require investor authentication.

### Investors

Investors must:

1. register;
2. verify their email;
3. verify their phone;
4. complete the required onboarding state;
5. authenticate;
6. access their investor dashboard.

Only investors satisfying the required verification/activation rules may access investor-protected functionality.

### Internal Users

GIPA personnel authenticate through the approved application authentication mechanisms and are subject to roles, permissions and applicable organizational/district scope. Use Spatie persion package with admin configurable UI feature. 

Staff will be invited by their district management to use invite link to sign up and update their profile, for best security.
Only investor can register directly.

## API Authentication

The versioned API uses JWT authentication.

JWT handling must use an established, maintained library.

The application must not implement custom cryptographic algorithms.

## Token Security

The implementation must consider:

- token expiry;
- refresh handling;
- revocation/invalidation requirements;
- secure storage;
- replay considerations;
- brute-force protection;
- authentication failure logging;
- token exposure prevention.

Exact token lifetime and refresh/revocation policy must be implemented according to the approved security configuration.

## Password Security

Passwords must use Laravel's established password hashing mechanisms.

Passwords must never be logged, exposed through APIs, or stored in plaintext.

## Email Verification

Investor registration does not by itself grant investor access.

Email verification is an explicit onboarding state.

## Phone Verification

Phone verification is an explicit onboarding state.

The exact verification mechanism/provider is an implementation concern unless separately specified.

OTP values must never be logged.

## Session Security

Browser sessions must use appropriate:

- secure cookie settings;
- HttpOnly;
- SameSite;
- HTTPS in production;
- session expiration/invalidation.

## Authentication vs Authorization

Authentication establishes identity.

Authorization determines what that identity may do.

A valid JWT is never sufficient by itself to authorize an operation.

## Consequences

The system has a clear distinction between public, investor and internal authenticated users.

Investor onboarding becomes a controlled security boundary rather than merely a registration form.

## Related Requirements

FR-09
FR-15
FR-16

NFR-03
