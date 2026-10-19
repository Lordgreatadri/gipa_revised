<?php

namespace Tests\Feature\Modules\Shared;

use App\Modules\Shared\Domain\Models\Country;
use Database\Seeders\CountryTableSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryReferenceDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_country_seeder_is_idempotent_and_assigns_uuids(): void
    {
        $this->seed(CountryTableSeeder::class);
        $count = Country::query()->count();

        $this->assertGreaterThan(30, $count);
        $this->assertNotNull(Country::query()->where('abbreviation', 'GH')->first()?->uuid);

        $this->seed(CountryTableSeeder::class);

        $this->assertSame($count, Country::query()->count());
    }

    public function test_country_route_key_is_the_uuid(): void
    {
        $country = $this->makeCountry();

        $this->assertSame('uuid', $country->getRouteKeyName());
        $this->assertSame($country->uuid, $country->getRouteKey());
    }

    public function test_name_and_abbreviation_are_unique(): void
    {
        $this->makeCountry();

        $this->expectException(QueryException::class);

        $this->makeCountry(['abbreviation' => 'XX']);
    }
}
