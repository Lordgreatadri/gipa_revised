008-document-storage.md

# ADR-0008: Document and File Storage Architecture

- Status: Accepted
- Date: 2026-09-28
- Decision Type: Data / Infrastructure Architecture
- Scope: Documents and Uploaded Files

## Context

IOMP requires storage of supporting documents and other managed files, including:

- Business Plan;
- Technical Feasibility;
- Market Feasibility;
- Funding Structure;
- certificate-related assets;
- potentially other controlled administrative documents.

Uploaded files are untrusted input.

Some documents may contain sensitive or confidential information and must not be publicly exposed by default.

## Decision

Document storage will be abstracted behind an application-level file/document storage contract.

The business domain must not depend directly on a specific storage provider.

The initial deployment may use filesystem/object storage appropriate to the hosting environment.

The storage implementation must be replaceable without changing domain behavior.

## Document Metadata

The system should maintain authoritative metadata such as:

- document identifier;
- owning resource;
- document category/type;
- original filename;
- stored filename/key;
- MIME type;
- file size;
- checksum/hash where appropriate;
- storage location;
- uploader;
- upload timestamp;
- access classification;
- lifecycle/status.

The database stores metadata and references rather than assuming the database itself is the binary document store.

Images and files use media library

## Security

Uploaded files must be treated as untrusted.

Validate:

- file type;
- MIME type;
- file size;
- filename;
- extension;
- storage path;
- access permissions.

Never execute uploaded content.

Where appropriate, content inspection/scanning should be performed before the document becomes available for use.

## Public vs Private Storage

Sensitive documents must use private storage.

Access must occur through authorized application-controlled mechanisms.

Do not expose private storage paths directly.

Signed or temporary access mechanisms may be used where appropriate.

## Authorization

Document access must verify authorization against the owning business resource.

Possession of a document identifier must not be sufficient to retrieve the file.

For example, an investor must not be able to retrieve internal opportunity documents unless the business visibility policy explicitly permits the document.

## Opportunity Documents

Opportunity supporting documents are associated with the relevant opportunity.

Document visibility must follow the opportunity's visibility and document-specific access policy.

An approved/active opportunity being visible to an investor does not automatically mean every attached internal document is visible.

## Certificate Assets

Official certificate QR assets may be generated server-side.

Authoritative certificate verification information includes:

- certificate code;
- verification URL;
- QR asset path/reference.

The QR must be regenerable from the certificate code.

Loss of the QR image must therefore not invalidate the certificate.

## Retention and Deletion

Deletion or replacement of documents must respect:

- audit requirements;
- legal/retention requirements;
- business lifecycle;
- references from historical records.

Destructive deletion must not silently remove evidence required for audit.

## Storage Abstraction

The application should depend on a document/file storage contract rather than directly coupling business logic to Laravel's filesystem implementation.

## Consequences

Documents remain replaceable between local, filesystem and object-storage environments.

Security and authorization become explicit rather than relying on storage path secrecy.

## Related Requirements

FR-03
FR-10
FR-14

NFR-03
NFR-08
