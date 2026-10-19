<?php

namespace Tests\Feature\Modules\PublicPortal;

use App\Modules\PublicPortal\Application\OfficeHours;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeHoursTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_desk_is_open_on_a_weekday_morning(): void
    {
        $status = $this->hours()->snapshot(CarbonImmutable::parse('2026-10-02 10:15', 'Africa/Accra'));

        $this->assertTrue($status['open']);
        $this->assertSame('Open now', $status['headline']);
        $this->assertSame('Closes today at 5:00 pm.', $status['detail']);
    }

    public function test_the_desk_closes_at_five_and_on_weekends(): void
    {
        $closing = $this->hours()->snapshot(CarbonImmutable::parse('2026-10-02 17:00', 'Africa/Accra'));
        $saturday = $this->hours()->snapshot(CarbonImmutable::parse('2026-10-03 10:00', 'Africa/Accra'));

        $this->assertFalse($closing['open']);
        $this->assertSame('Closed for today. Opens Monday at 8:00 am.', $closing['detail']);
        $this->assertFalse($saturday['open']);
        $this->assertStringContainsString('weekend', $saturday['detail']);
    }

    public function test_public_holidays_and_a_weekend_observance_are_closed(): void
    {
        $christmas = $this->hours()->snapshot(CarbonImmutable::parse('2026-12-25 10:00', 'Africa/Accra'));
        $observed = $this->hours()->snapshot(CarbonImmutable::parse('2026-12-28 10:00', 'Africa/Accra'));

        $this->assertSame('Christmas Day', $christmas['holiday']);
        $this->assertFalse($christmas['open']);
        $this->assertSame('Boxing Day (observed)', $observed['holiday']);
        $this->assertFalse($observed['open']);
    }

    public function test_an_announced_closure_can_be_added_without_a_code_change(): void
    {
        config(['public.office.extra_closures' => ['2026-03-20' => 'Eid al-Fitr']]);

        $status = $this->hours()->snapshot(CarbonImmutable::parse('2026-03-20 11:00', 'Africa/Accra'));

        $this->assertSame('Eid al-Fitr', $status['holiday']);
        $this->assertFalse($status['open']);
    }

    public function test_the_contact_page_shows_the_live_office_state(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-02 10:15', 'Africa/Accra'));

        $this->get(route('contact.create'))
            ->assertOk()
            ->assertSee('Office hours')
            ->assertSee('Open now')
            ->assertSee('Monday to Friday, 8:00 am to 5:00 pm')
            ->assertSee('Other channels');

        $this->travelTo(CarbonImmutable::parse('2026-12-25 11:00', 'Africa/Accra'));

        $this->get(route('contact.create'))
            ->assertOk()
            ->assertSee('Closed')
            ->assertSee('Christmas Day');
    }

    private function hours(): OfficeHours
    {
        return $this->app->make(OfficeHours::class);
    }
}
