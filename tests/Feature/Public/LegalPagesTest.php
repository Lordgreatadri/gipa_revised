<?php

namespace Tests\Feature\Public;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function placeholderPages(): array
    {
        return [
            'privacy' => ['legal.privacy', 'Privacy'],
            'terms' => ['legal.terms', 'Terms of use'],
            'data-policy' => ['legal.data-policy', 'Data policy'],
            'guide' => ['platform.guide', 'User guide'],
            'api' => ['api.documentation', 'API documentation'],
        ];
    }

    #[DataProvider('placeholderPages')]
    public function test_placeholder_pages_are_public(string $route, string $heading): void
    {
        $this->get(route($route))
            ->assertOk()
            ->assertSee($heading)
            ->assertSee('Contact us');
    }
}
