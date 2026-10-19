<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Reference data, the ADR-0006 role catalogue, and bootstrap accounts
     * whose credentials come from the environment.
     */
    public function run(): void
    {
        $this->call([
            CountryTableSeeder::class,
            AccessSeeder::class,
            GeographySeeder::class,
            StaffDistrictSeeder::class,
            SectorCatalogueSeeder::class,
            OpportunityCatalogueSeeder::class,
            AssistantKnowledgeSeeder::class,
        ]);
    }
}
