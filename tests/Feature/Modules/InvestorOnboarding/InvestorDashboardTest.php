<?php

namespace Tests\Feature\Modules\InvestorOnboarding;

use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_overview_shows_the_investors_own_figures(): void
    {
        $investor = User::factory()->create();
        $investor->assignRole(AccessRoles::investorRole());
        $this->giveBusinessProfile($investor);

        $this->actingAs($investor)
            ->get(route('investor.dashboard'))
            ->assertOk()
            ->assertSee('Welcome, '.$investor->name)
            ->assertSee('E-mail and phone verified')
            ->assertSee('Active opportunities')
            ->assertSee('Inquiries')
            ->assertSee('Visible pipeline')
            ->assertSee('Pending approval')
            ->assertSee('No active records yet.')
            ->assertSee('Ask GIPA')
            ->assertSee('GIPA Assistant')
            ->assertSee('Open the GIPA Assistant', false);
    }
}
