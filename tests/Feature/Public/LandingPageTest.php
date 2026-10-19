<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_the_landing_page_explains_the_platform_and_investor_access(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee("Ghana's investment opportunities");
        $response->assertSee('Register as investor');
        $response->assertSee('Investor sign in');
        $response->assertSee('Four steps between this page and your dashboard.');
        $response->assertSee('approved and active');
        $response->assertSee('gipa-logo.png', false);
        $response->assertSee('An official investment platform of the Republic of Ghana');
        $response->assertSee('Contact us');
        $response->assertSee('Privacy');
        $response->assertSee('Terms');
        $response->assertSee('Data policy');
        $response->assertSee('User guide');
        $response->assertSee('API Doc');
    }

    public function test_the_public_landing_page_does_not_list_opportunities(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Featured opportunities');
        $response->assertDontSee('Explore opportunities');
        $response->assertDontSee('View all opportunities');
    }
}
