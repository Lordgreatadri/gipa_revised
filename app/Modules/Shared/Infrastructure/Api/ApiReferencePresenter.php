<?php

namespace App\Modules\Shared\Infrastructure\Api;

/**
 * View model for the public API reference. Visitors read this in the browser.
 * It is not a file download.
 */
final class ApiReferencePresenter
{
    private const REQUEST_ID = '3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40';

    public function __construct(
        private readonly OpenApiDocument $document,
        private readonly ApiCodeSamples $samples,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function operations(?string $query): array
    {
        return array_map($this->present(...), $this->document->matching($query));
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    private function present(array $operation): array
    {
        $operation['parameters'] = array_map($this->describeParameter(...), $operation['parameters'] ?? []);
        $operation['request_fields'] = $this->requestFields($operation['operation_id']);
        $operation['responses'] = $this->responses($operation);
        $operation['samples'] = $this->samples->for($operation);

        return $operation;
    }

    /**
     * @param  array{name: string, in: string, required: bool, schema: string, description: string}  $parameter
     * @return array<string, mixed>
     */
    private function describeParameter(array $parameter): array
    {
        $formats = [
            'page' => ['format' => 'integer, minimum 1', 'example' => '1'],
            'q' => ['format' => 'string, 1–80 characters', 'example' => 'cold store'],
            'sector' => ['format' => 'UUID', 'example' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'],
            'sub_sector' => ['format' => 'UUID', 'example' => 'bbbbbbbb-cccc-dddd-eeee-ffffffffffff'],
            'type' => ['format' => 'UUID', 'example' => 'cccccccc-dddd-eeee-ffff-000000000001'],
            'region' => ['format' => 'UUID', 'example' => '0b1c2d3e-4f50-6123-8456-778899aabbcc'],
            'district' => ['format' => 'UUID', 'example' => '11111111-2222-3333-4444-555555555555'],
            'min_amount' => ['format' => 'number, minimum 0', 'example' => '1000000'],
            'max_amount' => ['format' => 'number, maximum 999999999999', 'example' => '5000000'],
            'sort' => ['format' => 'amount_asc or amount_desc', 'example' => 'amount_asc'],
            'opportunity' => ['format' => 'UUID', 'example' => 'eeeeeeee-ffff-0000-1111-222222222222'],
            'minLng' => ['format' => 'decimal degrees, -180 to 180', 'example' => '-1'],
            'maxLng' => ['format' => 'decimal degrees, -180 to 180', 'example' => '0'],
            'minLat' => ['format' => 'decimal degrees, -90 to 90', 'example' => '5'],
            'maxLat' => ['format' => 'decimal degrees, -90 to 90', 'example' => '7'],
            'lat' => ['format' => 'decimal degrees, -90 to 90', 'example' => '5.6037'],
            'lng' => ['format' => 'decimal degrees, -180 to 180', 'example' => '-0.187'],
            'metres' => ['format' => 'integer, 1000 to 50000', 'example' => '25000'],
        ];

        $extra = $formats[$parameter['name']] ?? ['format' => $parameter['schema'], 'example' => null];

        return $parameter + [
            'format' => $extra['format'],
            'example' => $extra['example'],
        ];
    }

    /**
     * @return list<array{name: string, type: string, format: string, required: string, description: string, example: string}>
     */
    private function requestFields(string $operationId): array
    {
        return match ($operationId) {
            'issueToken' => [
                $this->field('email', 'string', 'email address, max 255 characters', 'required', 'Account e-mail. Matched without regard to letter case.', 'investor@example.test'),
                $this->field('password', 'string', 'string, max 255 characters', 'required', 'Account password. It is checked and then discarded. It is not written to the audit log.', 'your-password'),
            ],
            'refreshToken', 'revokeToken' => [
                $this->field('refresh_token', 'string', 'opaque string, max 255 characters', 'required', 'The refresh token returned once by the token or refresh call. Only its hash is stored.', 'replace-with-the-token-returned-once'),
            ],
            'createInquiry' => [
                $this->field('message', 'string', '20–2000 characters', 'required', 'What you want to ask about this opportunity. Do not include a password or an identity document.', 'We would like the feasibility note and a site visit.'),
                $this->field('consent', 'boolean', 'true', 'required', 'Must be true. The server rejects false, 0, and an omitted value.', 'true'),
            ],
            'createInterest' => [
                $this->field('message', 'string', '0–2000 characters', 'optional', 'An optional note. The same length limit as an inquiry, without the 20-character minimum.', 'Our fund would like to discuss this record.'),
                $this->field('consent', 'boolean', 'true', 'required', 'Must be true.', 'true'),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return list<array<string, mixed>>
     */
    private function responses(array $operation): array
    {
        $id = $operation['operation_id'];
        $success = (string) $operation['status'];
        $responses = [[
            'status' => $success,
            'title' => $success === '201' ? 'Created' : 'OK',
            'description' => 'The call completed. Read data for the resource and meta.request_id to correlate logs.',
            'body' => $this->pretty((string) $operation['response']),
            'fields' => $this->successFields($id),
        ]];

        if (in_array($id, ['issueToken', 'refreshToken', 'currentAccount'], true) || ($operation['auth'] ?? '') === 'bearer') {
            if ($id !== 'revokeToken') {
                $responses[] = $this->error(
                    '401',
                    'unauthenticated',
                    $id === 'issueToken' ? 'The credentials are invalid.' : ($id === 'refreshToken' ? 'The refresh token is invalid.' : 'An access token is required.'),
                    $id === 'issueToken'
                        ? 'The e-mail or password does not match an account. The response does not say which one failed.'
                        : 'The Authorization header is missing, the access token is expired or altered, or the refresh token was already used or revoked.',
                );
            }
        }

        if (in_array($id, ['listOpportunities', 'showOpportunity', 'mapOpportunities', 'nearbyOpportunities', 'createInquiry', 'createInterest', 'listEngagements'], true)) {
            $responses[] = $this->error(
                '403',
                'forbidden',
                'This resource is available to investor accounts.',
                'A staff token, an investor who has not verified both e-mail and phone, or a verified investor with no business profile cannot read or write these resources. An inquiry without an approved profile fails validation instead, with HTTP 422.',
            );
        }

        if ($id === 'showOpportunity') {
            $responses[] = $this->error(
                '404',
                'not_found',
                'Not found.',
                'The id is unknown, or the opportunity is not active. Drafts and closed records use the same response so their existence is not disclosed.',
            );
        }

        if (in_array($id, ['issueToken', 'refreshToken', 'revokeToken', 'createInquiry', 'createInterest', 'mapOpportunities', 'nearbyOpportunities'], true)) {
            $details = match ($id) {
                'createInquiry' => ['message' => ['The message field must be at least 20 characters.']],
                'mapOpportunities' => ['minLng' => ['Choose a smaller map window.']],
                'nearbyOpportunities' => ['metres' => ['Choose a distance between 1 km and 50 km.']],
                default => ['email' => ['The email field is required.']],
            };
            $responses[] = $this->error(
                '422',
                'validation_failed',
                'The request is invalid.',
                'A required field is missing, a value is the wrong format, consent was not accepted, or a business rule rejected the body. Field messages are in error.details.',
                $details,
            );
        }

        if (in_array($id, ['issueToken', 'refreshToken', 'revokeToken'], true)) {
            $responses[] = $this->error(
                '429',
                'too_many_requests',
                'Too many attempts.',
                'These three routes accept 10 requests a minute from one address.',
            );
            $responses[] = $this->error(
                '503',
                'http_error',
                'API signing is not configured.',
                'The server has no JWT signing key. No token is issued. This is an operator fault, not a client fault.',
            );
        }

        return $responses;
    }

    /**
     * @return list<array{name: string, type: string, format: string, required: string, description: string, example: string}>
     */
    private function successFields(string $operationId): array
    {
        $meta = [
            $this->field('meta.request_id', 'string', 'UUID', 'always', 'Correlates this response with server logs. The same value is sent as the X-Request-Id header.', self::REQUEST_ID),
        ];
        $page = [
            $this->field('meta.page', 'integer', 'minimum 1', 'always', 'Current page.', '1'),
            $this->field('meta.per_page', 'integer', '12 or 50', 'always', 'Opportunity and engagement pages contain 12 records. Reference pages contain 50.', '12'),
            $this->field('meta.total', 'integer', 'minimum 0', 'always', 'Records that match the filters, across all pages.', '1'),
            $this->field('meta.last_page', 'integer', 'minimum 1', 'always', 'Last page number for this filter.', '1'),
        ];

        $data = match ($operationId) {
            'getStatus' => [
                $this->field('data.api_version', 'string', 'v1', 'always', 'The version you called.', 'v1'),
                $this->field('data.status', 'string', 'ok', 'always', 'The process is serving this version.', 'ok'),
            ],
            'listRegions' => [
                $this->field('data[].id', 'string', 'UUID', 'always', 'Region id. Use it as the region query on districts and opportunities.', '0b1c2d3e-4f50-6123-8456-778899aabbcc'),
                $this->field('data[].code', 'string', '2 letters', 'always', 'Region code, for example AA for Greater Accra.', 'AA'),
                $this->field('data[].name', 'string', 'text', 'always', 'Region name.', 'Greater Accra'),
            ],
            'listDistricts' => [
                $this->field('data[].id', 'string', 'UUID', 'always', 'District id.', '11111111-2222-3333-4444-555555555555'),
                $this->field('data[].code', 'string', 'text', 'always', 'District code.', 'GH-AA-001'),
                $this->field('data[].name', 'string', 'text', 'always', 'District name.', 'Ablekuma Central'),
                $this->field('data[].region_id', 'string', 'UUID', 'always', 'Parent region id.', '0b1c2d3e-4f50-6123-8456-778899aabbcc'),
                $this->field('data[].region', 'string', 'text', 'always', 'Parent region name.', 'Greater Accra'),
            ],
            'listSectors', 'listSubSectors', 'listEnterpriseTypes' => [
                $this->field('data[].id', 'string', 'UUID', 'always', 'Use this id as the sector, sub_sector, or type filter.', 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'),
                $this->field('data[].code', 'string', 'uppercase letters, digits, hyphen', 'always', 'Stable short code.', 'AGR'),
                $this->field('data[].name', 'string', 'text', 'always', 'Display name.', 'Agriculture'),
            ],
            'issueToken', 'refreshToken' => [
                $this->field('data.token_type', 'string', 'Bearer', 'always', 'Send the access token as Authorization: Bearer {access_token}.', 'Bearer'),
                $this->field('data.access_token', 'string', 'JWT, HS256', 'always', 'Short-lived access token. Default lifetime is 15 minutes (900 seconds).', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.example'),
                $this->field('data.expires_in', 'integer', 'seconds', 'always', 'Seconds until the access token expires.', '900'),
                $this->field('data.refresh_token', 'string', 'opaque string', 'always', 'Returned once. Store it. The next refresh call revokes it and returns a new one.', 'replace-with-the-token-returned-once'),
            ],
            'revokeToken' => [
                $this->field('data.revoked', 'boolean', 'true', 'always', 'Always true, including when the token was already unknown, so the response does not reveal whether it existed.', 'true'),
            ],
            'currentAccount' => [
                $this->field('data.id', 'string', 'UUID', 'always', 'Account id.', 'dddddddd-eeee-ffff-0000-111111111111'),
                $this->field('data.name', 'string', 'text', 'always', 'Account name.', 'Ama Mensah'),
                $this->field('data.email', 'string', 'email', 'always', 'Account e-mail.', 'investor@example.test'),
                $this->field('data.account_type', 'string', 'investor or staff', 'always', 'Which workspace this account belongs to.', 'investor'),
                $this->field('data.email_verified', 'boolean', 'true or false', 'always', 'The investor register stays closed until this is true.', 'true'),
                $this->field('data.phone_verified', 'boolean', 'true or false', 'always', 'The investor register stays closed until this is true.', 'true'),
            ],
            'listOpportunities', 'showOpportunity' => [
                $this->field('data.id', 'string', 'UUID', 'always', 'Opportunity id. On a list this is data[].id.', 'eeeeeeee-ffff-0000-1111-222222222222'),
                $this->field('data.reference', 'string', 'text', 'always', 'Human reference, such as OPP-2026-000001.', 'OPP-2026-000001'),
                $this->field('data.title', 'string', 'text', 'always', 'Opportunity title.', 'Coastal cold store'),
                $this->field('data.summary', 'string', 'text, max 240 characters', 'always', 'Short description. The show call also returns the full description.', 'A cold storage facility for landed fish.'),
                $this->field('data.currency', 'string', 'GHS or USD', 'always', 'Currency of investment_amount. The two currencies are not added together.', 'GHS'),
                $this->field('data.investment_amount', 'string', 'decimal with 2 places', 'always', 'Amount as a string so large values are not rounded.', '1500000.00'),
                $this->field('data.latitude', 'string or null', 'decimal degrees, 6 places', 'show only', 'Present on the show call. A discovery point, not a surveyed boundary.', '5.603700'),
                $this->field('data.longitude', 'string or null', 'decimal degrees, 6 places', 'show only', 'Present on the show call.', '-0.187000'),
            ],
            'mapOpportunities', 'nearbyOpportunities' => [
                $this->field('data.type', 'string', 'FeatureCollection', 'always', 'GeoJSON collection.', 'FeatureCollection'),
                $this->field('data.features[].geometry.coordinates', 'array', '[longitude, latitude]', 'always', 'GeoJSON order is longitude then latitude.', '[-0.187, 5.6037]'),
                $this->field('data.features[].properties.uuid', 'string', 'UUID', 'always', 'Opportunity id.', 'eeeeeeee-ffff-0000-1111-222222222222'),
                $this->field('data.features[].properties.amount', 'string', 'currency and grouped number', 'always', 'Display amount. It is not a number you should parse for arithmetic.', 'GHS 1,500,000'),
                $this->field('data.features[].properties.distance_m', 'integer or null', 'metres', 'nearby', 'Set on a nearby search. Null on a map window.', '1200'),
            ],
            'createInquiry', 'createInterest', 'listEngagements' => [
                $this->field('data.reference', 'string', 'INQ-year-XXXXXX or INT-year-XXXXXX', 'always', 'The reference to quote in later messages.', 'INQ-2026-ABC123'),
                $this->field('data.kind', 'string', 'inquiry or interest', 'always', 'Which engagement this is.', 'inquiry'),
                $this->field('data.state', 'string', 'submitted', 'always', 'The only state this version records. A follow-up message does not change it.', 'submitted'),
                $this->field('data.submitted_at', 'string', 'ISO-8601', 'always', 'When the engagement was recorded.', '2026-10-01T18:00:00+00:00'),
                $this->field('data.opportunity.id', 'string', 'UUID', 'always', 'The opportunity this engagement belongs to.', 'eeeeeeee-ffff-0000-1111-222222222222'),
            ],
            default => [],
        };

        $withPage = in_array($operationId, [
            'listRegions', 'listDistricts', 'listSectors', 'listSubSectors', 'listEnterpriseTypes',
            'listOpportunities', 'listEngagements',
        ], true);

        return [...$data, ...($withPage ? $page : []), ...$meta];
    }

    /**
     * @param  array<string, mixed>|null  $details
     * @return array<string, mixed>
     */
    private function error(string $status, string $code, string $message, string $description, ?array $details = null): array
    {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($details !== null) {
            $error['details'] = $details;
        }

        return [
            'status' => $status,
            'title' => $message,
            'description' => $description,
            'body' => $this->pretty((string) json_encode([
                'error' => $error,
                'meta' => ['request_id' => self::REQUEST_ID],
            ])),
            'fields' => [
                $this->field('error.code', 'string', $code, 'always', 'Stable code for this outcome. Branch on this, not on the sentence.', $code),
                $this->field('error.message', 'string', 'short sentence', 'always', 'A safe explanation. Stack traces and SQL are not included.', $message),
                $this->field('error.details', 'object', 'field name to a list of sentences', '422 only', 'Present when validation failed. Keys match the request fields.', 'message'),
                $this->field('meta.request_id', 'string', 'UUID', 'always', 'Quote this if you ask GIPA about the call.', self::REQUEST_ID),
            ],
        ];
    }

    /**
     * @return array{name: string, type: string, format: string, required: string, description: string, example: string}
     */
    private function field(string $name, string $type, string $format, string $required, string $description, string $example): array
    {
        return compact('name', 'type', 'format', 'required', 'description', 'example');
    }

    private function pretty(string $json): string
    {
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return trim($json);
        }

        return (string) json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
