<?php

namespace App\Modules\Shared\Infrastructure\Api;

/**
 * OpenAPI 3.0 contract for /api/v1. The public documentation page and
 * GET /api/v1/openapi.json both read this catalogue so the samples stay
 * with the routes.
 */
final class OpenApiDocument
{
    /**
     * @return array<string, mixed>
     */
    public function specification(): array
    {
        $paths = [];

        foreach ($this->operations() as $operation) {
            $method = strtolower($operation['method']);
            $entry = [
                'tags' => [$operation['tag']],
                'operationId' => $operation['operation_id'],
                'summary' => $operation['summary'],
                'description' => $operation['description'],
                'security' => $operation['auth'] === 'bearer' ? [['bearerAuth' => []]] : [],
                'parameters' => array_map(fn (array $parameter): array => [
                    'name' => $parameter['name'],
                    'in' => $parameter['in'],
                    'required' => $parameter['required'],
                    'description' => $parameter['description'],
                    'schema' => ['type' => $parameter['schema']],
                ], $operation['parameters']),
                'responses' => [
                    (string) $operation['status'] => [
                        'description' => 'Success',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/Envelope'],
                                'example' => json_decode($operation['response'], true),
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationFailed'],
                ],
            ];

            if ($operation['request'] !== null) {
                $entry['requestBody'] = [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => json_decode($operation['request'], true),
                        ],
                    ],
                ];
            }

            $paths[$operation['path']][$method] = $entry;
        }

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'IOMP API',
                'version' => '1.0.0',
                'description' => 'Versioned external API for the Investment Opportunities Mapping Platform. Active opportunities are visible only to a verified investor with an approved business profile when they inquire or express interest. Contact details, drafts, and staff workflow fields are not part of this contract.',
            ],
            'servers' => [
                ['url' => rtrim((string) config('app.url'), '/')],
            ],
            'tags' => [
                ['name' => 'Service'],
                ['name' => 'Reference'],
                ['name' => 'Authentication'],
                ['name' => 'Account'],
                ['name' => 'Opportunities'],
                ['name' => 'Engagement'],
                ['name' => 'Map'],
            ],
            'paths' => $paths,
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                        'description' => 'Access token from POST /api/v1/auth/token. Send Authorization: Bearer {access_token}.',
                    ],
                ],
                'schemas' => [
                    'Envelope' => [
                        'type' => 'object',
                        'required' => ['data', 'meta'],
                        'properties' => [
                            'data' => ['description' => 'Resource or collection.'],
                            'meta' => [
                                'type' => 'object',
                                'properties' => [
                                    'request_id' => ['type' => 'string', 'format' => 'uuid'],
                                    'page' => ['type' => 'integer'],
                                    'per_page' => ['type' => 'integer'],
                                    'total' => ['type' => 'integer'],
                                    'last_page' => ['type' => 'integer'],
                                ],
                            ],
                        ],
                    ],
                    'Error' => [
                        'type' => 'object',
                        'required' => ['error', 'meta'],
                        'properties' => [
                            'error' => [
                                'type' => 'object',
                                'properties' => [
                                    'code' => ['type' => 'string', 'example' => 'validation_failed'],
                                    'message' => ['type' => 'string'],
                                    'details' => ['type' => 'object'],
                                ],
                            ],
                            'meta' => [
                                'type' => 'object',
                                'properties' => [
                                    'request_id' => ['type' => 'string', 'format' => 'uuid'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    'Unauthenticated' => [
                        'description' => 'Missing or invalid access token.',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]],
                    ],
                    'Forbidden' => [
                        'description' => 'The account cannot use this resource.',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]],
                    ],
                    'ValidationFailed' => [
                        'description' => 'The request body or query is invalid.',
                        'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function matching(?string $query): array
    {
        $needle = mb_strtolower(trim((string) $query));

        if ($needle === '') {
            return $this->operations();
        }

        return array_values(array_filter(
            $this->operations(),
            function (array $operation) use ($needle): bool {
                $haystack = mb_strtolower(implode(' ', [
                    $operation['method'],
                    $operation['path'],
                    $operation['summary'],
                    $operation['description'],
                    $operation['tag'],
                ]));

                return str_contains($haystack, $needle);
            },
        ));
    }

    /**
     * @return list<array{
     *     operation_id: string,
     *     tag: string,
     *     method: string,
     *     path: string,
     *     auth: string,
     *     summary: string,
     *     description: string,
     *     status: int,
     *     parameters: list<array{name: string, in: string, required: bool, schema: string, description: string}>,
     *     request: ?string,
     *     response: string
     * }>
     */
    public function operations(): array
    {
        return [
            $this->operation(
                'getStatus',
                'Service',
                'GET',
                '/api/v1/status',
                'none',
                'Service status',
                'Confirms that API version 1 is reachable. No authentication.',
                200,
                [],
                null,
                <<<'JSON'
{"data":{"api_version":"v1","status":"ok"},"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40"}}
JSON,
            ),
            $this->operation(
                'listRegions',
                'Reference',
                'GET',
                '/api/v1/regions',
                'none',
                'List regions',
                'Ghana regions. Page size 50.',
                200,
                [['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => 'integer', 'description' => 'Page number, starting at 1.']],
                null,
                <<<'JSON'
{"data":[{"id":"0b1c2d3e-4f50-6123-8456-778899aabbcc","code":"AA","name":"Greater Accra"}],"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40","page":1,"per_page":50,"total":16,"last_page":1}}
JSON,
            ),
            $this->operation(
                'listDistricts',
                'Reference',
                'GET',
                '/api/v1/districts',
                'none',
                'List districts',
                'District names and codes. Optional region filter uses the region id from the regions resource. Boundaries are not published.',
                200,
                [
                    ['name' => 'region', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'Region id (UUID).'],
                    ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => 'integer', 'description' => 'Page number.'],
                ],
                null,
                <<<'JSON'
{"data":[{"id":"11111111-2222-3333-4444-555555555555","code":"GH-AA-001","name":"Ablekuma Central","region_id":"0b1c2d3e-4f50-6123-8456-778899aabbcc","region":"Greater Accra"}],"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40","page":1,"per_page":50,"total":1,"last_page":1}}
JSON,
            ),
            $this->operation(
                'listSectors',
                'Reference',
                'GET',
                '/api/v1/sectors',
                'none',
                'List sectors',
                'Active investment sectors.',
                200,
                [['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => 'integer', 'description' => 'Page number.']],
                null,
                <<<'JSON'
{"data":[{"id":"aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee","code":"AGR","name":"Agriculture"}],"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40","page":1,"per_page":50,"total":1,"last_page":1}}
JSON,
            ),
            $this->operation(
                'listSubSectors',
                'Reference',
                'GET',
                '/api/v1/sub-sectors',
                'none',
                'List sub-sectors',
                'Active sub-sectors. Filter with the sector id.',
                200,
                [
                    ['name' => 'sector', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'Sector id (UUID).'],
                    ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => 'integer', 'description' => 'Page number.'],
                ],
                null,
                <<<'JSON'
{"data":[{"id":"bbbbbbbb-cccc-dddd-eeee-ffffffffffff","code":"AGR-PROC","name":"Processing","sector_id":"aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee"}],"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40","page":1,"per_page":50,"total":1,"last_page":1}}
JSON,
            ),
            $this->operation(
                'listEnterpriseTypes',
                'Reference',
                'GET',
                '/api/v1/enterprise-types',
                'none',
                'List enterprise types',
                'Active enterprise types.',
                200,
                [['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => 'integer', 'description' => 'Page number.']],
                null,
                <<<'JSON'
{"data":[{"id":"cccccccc-dddd-eeee-ffff-000000000001","code":"PLL","name":"Public limited liability"}],"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40","page":1,"per_page":50,"total":1,"last_page":1}}
JSON,
            ),
            $this->operation(
                'issueToken',
                'Authentication',
                'POST',
                '/api/v1/auth/token',
                'none',
                'Issue an access token',
                'Exchange an account e-mail and password for a short-lived JWT and a refresh token. The refresh token is returned once. Failed attempts are audited without the password. Limited to 10 requests a minute per address.',
                200,
                [],
                <<<'JSON'
{"email":"investor@example.test","password":"your-password"}
JSON,
                <<<'JSON'
{"data":{"token_type":"Bearer","access_token":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.example","expires_in":900,"refresh_token":"replace-with-the-token-returned-once"},"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40"}}
JSON,
            ),
            $this->operation(
                'refreshToken',
                'Authentication',
                'POST',
                '/api/v1/auth/refresh',
                'none',
                'Refresh an access token',
                'Exchange a valid refresh token for a new access token and a new refresh token. The previous refresh token is revoked.',
                200,
                [],
                <<<'JSON'
{"refresh_token":"replace-with-the-token-returned-once"}
JSON,
                <<<'JSON'
{"data":{"token_type":"Bearer","access_token":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.example","expires_in":900,"refresh_token":"a-new-refresh-token"},"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40"}}
JSON,
            ),
            $this->operation(
                'revokeToken',
                'Authentication',
                'POST',
                '/api/v1/auth/revoke',
                'none',
                'Revoke a refresh token',
                'Marks a refresh token as revoked. An unknown token still returns revoked true so the response does not reveal whether it existed.',
                200,
                [],
                <<<'JSON'
{"refresh_token":"replace-with-the-token-returned-once"}
JSON,
                <<<'JSON'
{"data":{"revoked":true},"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40"}}
JSON,
            ),
            $this->operation(
                'currentAccount',
                'Account',
                'GET',
                '/api/v1/me',
                'bearer',
                'Current account',
                'The authenticated account. Verification flags tell a client whether the investor workspace is open. Roles and permissions are not listed.',
                200,
                [],
                null,
                <<<'JSON'
{"data":{"id":"dddddddd-eeee-ffff-0000-111111111111","name":"Ama Mensah","email":"investor@example.test","account_type":"investor","email_verified":true,"phone_verified":true},"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40"}}
JSON,
            ),
            $this->operation(
                'listOpportunities',
                'Opportunities',
                'GET',
                '/api/v1/opportunities',
                'bearer',
                'List active opportunities',
                'Verified investors only. Records in any state other than active are omitted. Filters and sorts are allowlisted. Page size is 12. Contact details are not included.',
                200,
                [
                    ['name' => 'q', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'Title, district name, or sector name. At most 80 characters.'],
                    ['name' => 'sector', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'Sector id.'],
                    ['name' => 'sub_sector', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'Sub-sector id.'],
                    ['name' => 'type', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'Enterprise type id.'],
                    ['name' => 'region', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'Region id.'],
                    ['name' => 'district', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'District id.'],
                    ['name' => 'min_amount', 'in' => 'query', 'required' => false, 'schema' => 'number', 'description' => 'Minimum investment amount.'],
                    ['name' => 'max_amount', 'in' => 'query', 'required' => false, 'schema' => 'number', 'description' => 'Maximum investment amount.'],
                    ['name' => 'sort', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'amount_asc or amount_desc. Any other value is ignored.'],
                    ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => 'integer', 'description' => 'Page number.'],
                ],
                null,
                <<<'JSON'
{"data":[{"id":"eeeeeeee-ffff-0000-1111-222222222222","reference":"OPP-2026-000001","title":"Coastal cold store","summary":"A cold storage facility for landed fish.","sector":"Agriculture","sub_sector":"Processing","enterprise_type":"Public limited liability","region":"Greater Accra","district":"Ablekuma Central","investment_amount":"1500000.00","currency":"GHS","project_status":"in_progress"}],"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40","page":1,"per_page":12,"total":1,"last_page":1}}
JSON,
            ),
            $this->operation(
                'showOpportunity',
                'Opportunities',
                'GET',
                '/api/v1/opportunities/{opportunity}',
                'bearer',
                'Show an active opportunity',
                'Verified investors only. A draft or other non-active record returns 404. Latitude and longitude are a discovery aid, not a surveyed site boundary.',
                200,
                [['name' => 'opportunity', 'in' => 'path', 'required' => true, 'schema' => 'string', 'description' => 'Opportunity id (UUID).']],
                null,
                <<<'JSON'
{"data":{"id":"eeeeeeee-ffff-0000-1111-222222222222","reference":"OPP-2026-000001","title":"Coastal cold store","summary":"A cold storage facility for landed fish.","description":"A cold storage facility for landed fish.","location_description":"Near the landing beach","sector":"Agriculture","sub_sector":"Processing","enterprise_type":"Public limited liability","region":"Greater Accra","district":"Ablekuma Central","investment_amount":"1500000.00","currency":"GHS","project_status":"in_progress","latitude":"5.603700","longitude":"-0.187000"},"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40"}}
JSON,
            ),
            $this->operation(
                'mapOpportunities',
                'Map',
                'GET',
                '/api/v1/map/opportunities',
                'bearer',
                'Opportunities in a map window',
                'GeoJSON FeatureCollection of active opportunities inside a bounding box. The box may not span more than 30 degrees. At most 300 features.',
                200,
                [
                    ['name' => 'minLng', 'in' => 'query', 'required' => true, 'schema' => 'number', 'description' => 'Western edge.'],
                    ['name' => 'minLat', 'in' => 'query', 'required' => true, 'schema' => 'number', 'description' => 'Southern edge.'],
                    ['name' => 'maxLng', 'in' => 'query', 'required' => true, 'schema' => 'number', 'description' => 'Eastern edge.'],
                    ['name' => 'maxLat', 'in' => 'query', 'required' => true, 'schema' => 'number', 'description' => 'Northern edge.'],
                    ['name' => 'sector', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'Sector id.'],
                    ['name' => 'region', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'Region id.'],
                    ['name' => 'district', 'in' => 'query', 'required' => false, 'schema' => 'string', 'description' => 'District id.'],
                ],
                null,
                <<<'JSON'
{"data":{"type":"FeatureCollection","features":[{"type":"Feature","geometry":{"type":"Point","coordinates":[-0.187,5.6037]},"properties":{"uuid":"eeeeeeee-ffff-0000-1111-222222222222","title":"Coastal cold store","reference":"OPP-2026-000001","sector":"Agriculture","district":"Ablekuma Central","region":"Greater Accra","amount":"GHS 1,500,000","distance_m":null}}]},"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40"}}
JSON,
            ),
            $this->operation(
                'nearbyOpportunities',
                'Map',
                'GET',
                '/api/v1/map/nearby',
                'bearer',
                'Opportunities near a point',
                'Active opportunities within a distance of a point. Distance is 1000 to 50000 metres and defaults to 25000. At most 100 features. The point is not stored.',
                200,
                [
                    ['name' => 'lat', 'in' => 'query', 'required' => true, 'schema' => 'number', 'description' => 'Latitude.'],
                    ['name' => 'lng', 'in' => 'query', 'required' => true, 'schema' => 'number', 'description' => 'Longitude.'],
                    ['name' => 'metres', 'in' => 'query', 'required' => false, 'schema' => 'integer', 'description' => 'Search radius from 1000 to 50000. Default 25000.'],
                ],
                null,
                <<<'JSON'
{"data":{"type":"FeatureCollection","features":[]},"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40"}}
JSON,
            ),
            $this->operation(
                'createInquiry',
                'Engagement',
                'POST',
                '/api/v1/opportunities/{opportunity}/inquiries',
                'bearer',
                'Send an inquiry',
                'Requires a verified investor and an approved active business profile. One inquiry per opportunity. The message is 20 to 2000 characters. Consent must be true. The engagement stays submitted.',
                201,
                [['name' => 'opportunity', 'in' => 'path', 'required' => true, 'schema' => 'string', 'description' => 'Opportunity id (UUID).']],
                <<<'JSON'
{"message":"We would like the feasibility note and a site visit.","consent":true}
JSON,
                <<<'JSON'
{"data":{"id":"ffffffff-0000-1111-2222-333333333333","reference":"INQ-2026-ABC123","kind":"inquiry","state":"submitted","message":"We would like the feasibility note and a site visit.","submitted_at":"2026-10-01T18:00:00+00:00","opportunity":{"id":"eeeeeeee-ffff-0000-1111-222222222222","title":"Coastal cold store","reference":"OPP-2026-000001"}},"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40"}}
JSON,
            ),
            $this->operation(
                'createInterest',
                'Engagement',
                'POST',
                '/api/v1/opportunities/{opportunity}/interest',
                'bearer',
                'Express interest',
                'Same approval gate as an inquiry. One expression of interest per opportunity. A message is optional and at most 2000 characters.',
                201,
                [['name' => 'opportunity', 'in' => 'path', 'required' => true, 'schema' => 'string', 'description' => 'Opportunity id (UUID).']],
                <<<'JSON'
{"message":"Our fund would like to discuss this record.","consent":true}
JSON,
                <<<'JSON'
{"data":{"id":"00000000-1111-2222-3333-444444444444","reference":"INT-2026-ABC123","kind":"interest","state":"submitted","message":"Our fund would like to discuss this record.","submitted_at":"2026-10-01T18:00:00+00:00","opportunity":{"id":"eeeeeeee-ffff-0000-1111-222222222222","title":"Coastal cold store","reference":"OPP-2026-000001"}},"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40"}}
JSON,
            ),
            $this->operation(
                'listEngagements',
                'Engagement',
                'GET',
                '/api/v1/engagements',
                'bearer',
                'List my engagements',
                'Inquiries and expressions of interest belonging to the authenticated investor. Page size 12.',
                200,
                [['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => 'integer', 'description' => 'Page number.']],
                null,
                <<<'JSON'
{"data":[{"id":"ffffffff-0000-1111-2222-333333333333","reference":"INQ-2026-ABC123","kind":"inquiry","state":"submitted","message":"We would like the feasibility note and a site visit.","submitted_at":"2026-10-01T18:00:00+00:00","opportunity":{"id":"eeeeeeee-ffff-0000-1111-222222222222","title":"Coastal cold store","reference":"OPP-2026-000001"}}],"meta":{"request_id":"3f1c0b2e-6a4d-4e1a-9c2b-1d8e7f6a5b40","page":1,"per_page":12,"total":1,"last_page":1}}
JSON,
            ),
        ];
    }

    /**
     * @param  list<array{name: string, in: string, required: bool, schema: string, description: string}>  $parameters
     * @return array<string, mixed>
     */
    private function operation(
        string $operationId,
        string $tag,
        string $method,
        string $path,
        string $auth,
        string $summary,
        string $description,
        int $status,
        array $parameters,
        ?string $request,
        string $response,
    ): array {
        return [
            'operation_id' => $operationId,
            'tag' => $tag,
            'method' => $method,
            'path' => $path,
            'auth' => $auth,
            'summary' => $summary,
            'description' => $description,
            'status' => $status,
            'parameters' => $parameters,
            'request' => $request,
            'response' => $response,
        ];
    }
}
