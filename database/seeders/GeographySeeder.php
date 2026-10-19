<?php

namespace Database\Seeders;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Geography\Domain\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Ghana's region and district register. The count is data, not a hardcoded
 * business rule. Rows are written in batches.
 */
class GeographySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/ghana-districts.json');
        $payload = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $now = now();

        foreach ($payload['regions'] as $regionData) {
            $region = Region::query()->updateOrCreate(
                ['code' => $regionData['code']],
                [
                    'name' => $regionData['name'],
                    'capital' => $regionData['capital'] ?? null,
                ],
            );

            $batch = [];

            foreach ($regionData['districts'] as $district) {
                $batch[] = [
                    'uuid' => (string) Str::uuid(),
                    'region_id' => $region->id,
                    'code' => $district['code'],
                    'name' => $district['name'],
                    'capital' => $district['capital'] ?? null,
                    'category' => $district['category'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) === 50) {
                    $this->storeDistricts($batch);
                    $batch = [];
                }
            }

            if ($batch !== []) {
                $this->storeDistricts($batch);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $batch
     */
    private function storeDistricts(array $batch): void
    {
        District::query()->upsert(
            $batch,
            ['code'],
            ['region_id', 'name', 'capital', 'category', 'updated_at'],
        );
    }
}
