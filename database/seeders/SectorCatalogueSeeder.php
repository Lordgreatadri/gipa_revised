<?php

namespace Database\Seeders;

use App\Modules\Opportunity\Domain\Catalogue\InvestmentCatalogue;
use App\Modules\Opportunity\Domain\Models\EnterpriseType;
use App\Modules\Opportunity\Domain\Models\Sector;
use App\Modules\Opportunity\Domain\Models\SubSector;
use Illuminate\Database\Seeder;

/**
 * Keeps the investment catalogue aligned with the sample definitions.
 * The migration inserts the same rows; this seeder is safe to run again.
 */
class SectorCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        foreach (InvestmentCatalogue::sectors() as $row) {
            Sector::query()->updateOrCreate(
                ['code' => $row['code']],
                ['name' => $row['name'], 'is_active' => true, 'sort_order' => $row['sort_order']],
            );
        }

        $sectors = Sector::query()->pluck('id', 'code');

        foreach (InvestmentCatalogue::subSectors() as $row) {
            SubSector::query()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'sector_id' => $sectors[$row['sector']],
                    'name' => $row['name'],
                    'is_active' => true,
                    'sort_order' => $row['sort_order'],
                ],
            );
        }

        foreach (InvestmentCatalogue::enterpriseTypes() as $row) {
            EnterpriseType::query()->updateOrCreate(
                ['code' => $row['code']],
                ['name' => $row['name'], 'is_active' => true],
            );
        }
    }
}
