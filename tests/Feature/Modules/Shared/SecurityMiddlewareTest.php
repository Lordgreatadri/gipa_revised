<?php

namespace Tests\Feature\Modules\Shared;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_web_responses(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotNull($response->headers->get('Permissions-Policy'));

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertMatchesRegularExpression("/script-src [^;]*'nonce-[A-Za-z0-9+\\/=_-]+'/", $csp);
        $this->assertStringNotContainsString("'unsafe-eval'", $csp);
        $this->assertStringNotContainsString("'unsafe-inline'", $csp);
    }

    public function test_the_csp_nonce_is_applied_to_generated_script_tags(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        preg_match("/script-src [^;]*'nonce-([^']+)'/", (string) $response->headers->get('Content-Security-Policy'), $m);

        $this->assertNotEmpty($m[1] ?? null);
        $this->assertStringContainsString('nonce="'.$m[1].'"', $html);
        $this->assertStringContainsString('js/theme-boot.js', $html);
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $html);
    }

    public function test_csp_names_script_src_elem_and_does_not_allow_unsafe_inline(): void
    {
        $csp = (string) $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('script-src-elem', $csp);
        $this->assertStringContainsString('style-src-elem', $csp);
        $this->assertStringNotContainsString("'unsafe-eval'", $csp);
        $this->assertStringNotContainsString("'unsafe-inline'", $csp);
        $this->assertDoesNotMatchRegularExpression("/script-src-elem [^;]*'unsafe-inline'/", $csp);
    }

    public function test_hsts_is_only_sent_on_https(): void
    {
        $this->call('GET', 'http://localhost/')->assertHeaderMissing('Strict-Transport-Security');

        $secure = $this->get('https://localhost/');

        $secure->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_every_response_carries_a_request_id(): void
    {
        $response = $this->get('/');

        $id = $response->headers->get('X-Request-Id');
        $this->assertNotNull($id);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $id);
    }

    public function test_a_well_formed_client_request_id_is_honoured(): void
    {
        $response = $this->withHeader('X-Request-Id', 'AAAAAAAA-BBBB-4CCC-8DDD-EEEEEEEEEEEE')->get('/');

        $response->assertHeader('X-Request-Id', 'aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee');
    }

    public function test_a_malformed_client_request_id_is_replaced(): void
    {
        $response = $this->withHeader('X-Request-Id', "evil\ninjected")->get('/');

        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $response->headers->get('X-Request-Id'));
    }
}
