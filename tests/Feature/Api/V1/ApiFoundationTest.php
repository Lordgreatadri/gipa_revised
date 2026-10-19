<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_status_endpoint_is_versioned_and_correlated(): void
    {
        $response = $this->getJson('/api/v1/status');

        $response->assertOk()
            ->assertJsonPath('data.api_version', 'v1')
            ->assertJsonPath('data.status', 'ok');

        $this->assertSame($response->headers->get('X-Request-Id'), $response->json('meta.request_id'));
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_unknown_api_routes_return_the_json_error_envelope(): void
    {
        $response = $this->getJson('/api/v1/does-not-exist');

        $response->assertNotFound()
            ->assertJsonPath('error.code', 'not_found')
            ->assertJsonStructure(['error' => ['code', 'message'], 'meta' => ['request_id']]);
    }

    public function test_api_errors_are_json_even_without_an_accept_header(): void
    {
        $response = $this->get('/api/v1/does-not-exist');

        $response->assertNotFound();
        $this->assertStringStartsWith('application/json', (string) $response->headers->get('Content-Type'));
        $response->assertJsonPath('error.code', 'not_found');
    }

    public function test_method_not_allowed_uses_the_envelope(): void
    {
        $this->postJson('/api/v1/status')
            ->assertStatus(405)
            ->assertJsonPath('error.code', 'method_not_allowed');
    }
}
