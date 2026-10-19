# IOMP API version 1

External clients call `/api/v1`. Visitors read the reference at `/api/documentation`: each endpoint, its fields and formats, a cURL, PHP, and Node.js sample of the same call, and every response status that call can return. The page is for reading and copying a request. It does not offer a JSON or YAML download.

## Authentication

`POST /api/v1/auth/token` accepts `email` and `password` and returns a bearer access token plus a refresh token. The access token is a JWT signed with `JWT_SIGNING_KEY` using HS256. It expires after `JWT_ACCESS_TTL_MINUTES` (15 by default). The refresh token is a random string. Only its SHA-256 hash is stored. `POST /api/v1/auth/refresh` revokes the presented refresh token and issues a new pair. `POST /api/v1/auth/revoke` revokes a refresh token. These three routes are limited to 10 requests a minute per address.

Set `JWT_SIGNING_KEY` to a long random value before issuing tokens. Leave it empty and token routes respond with HTTP 503. Do not commit a real key.

Successful and failed token requests are audited. The password is not written to the audit log.

## Who can call what

| Route group | Who |
| --- | --- |
| Status, OpenAPI, regions, districts, sectors, sub-sectors, enterprise types | Anyone |
| `GET /api/v1/me` | Any account with a valid access token |
| Opportunities, map, inquiries, interest, engagements | A verified investor who has saved a business profile. An inquiry or expression of interest also needs that profile approved and active |

Staff tokens receive 403 on the investor register. Unverified investors can read `/me` and are refused the register. Records that are not active return 404. Contact name, phone, and e-mail are not serialized. District boundaries are not published.

## Response shape

Success:

```json
{"data": {}, "meta": {"request_id": "uuid"}}
```

Collections add `page`, `per_page`, `total`, and `last_page`. Opportunity lists are 12 per page. Reference lists are 50.

Errors:

```json
{"error": {"code": "validation_failed", "message": "The request is invalid.", "details": {}}, "meta": {"request_id": "uuid"}}
```

Codes include `unauthenticated`, `forbidden`, `not_found`, `validation_failed`, `too_many_requests`, and `method_not_allowed`.

## Allowlisted filters

Opportunity list: `q`, `sector`, `sub_sector`, `type`, `region`, `district`, `min_amount`, `max_amount`, `sort` (`amount_asc` or `amount_desc`), `page`. Any other sort value is ignored. Workflow state cannot be requested.

Map window: `minLng`, `minLat`, `maxLng`, `maxLat`, plus the same catalogue filters. The window may not exceed 30 degrees and returns at most 300 features. Nearby: `lat`, `lng`, `metres` from 1000 to 50000 (default 25000), at most 100 features. The caller’s point is not stored.

## Not in this version

Certificates, identity-document upload, readiness scores, job figures, engagement states beyond `submitted`, SMS gateway credentials, and staff administration are outside this contract.
