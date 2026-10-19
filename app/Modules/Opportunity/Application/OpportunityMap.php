<?php

namespace App\Modules\Opportunity\Application;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Geography\Domain\Models\Region;
use App\Modules\Opportunity\Domain\Models\EnterpriseType;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\Sector;
use App\Modules\Opportunity\Domain\Models\SubSector;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Viewport and nearby reads for the investor map. PostGIS returns only
 * the rows inside the requested envelope or distance.
 */
final class OpportunityMap
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function viewport(array $filters): array
    {
        [$minLng, $minLat, $maxLng, $maxLat] = $this->envelope($filters);
        $query = $this->base($filters)
            ->whereRaw(
                'opportunities.location && ST_MakeEnvelope(?, ?, ?, ?, 4326)::geography',
                [$minLng, $minLat, $maxLng, $maxLat],
            );

        return $this->collection($query->limit(300)->get());
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function nearby(array $filters): array
    {
        $longitude = $this->coordinate($filters['lng'] ?? null, -180, 180, 'lng');
        $latitude = $this->coordinate($filters['lat'] ?? null, -90, 90, 'lat');
        $metres = (int) ($filters['metres'] ?? 25000);

        if ($metres < 1000 || $metres > 50000) {
            throw ValidationException::withMessages([
                'metres' => 'Choose a distance between 1 km and 50 km.',
            ]);
        }

        $query = $this->base($filters)
            ->whereRaw(
                'ST_DWithin(opportunities.location, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)',
                [$longitude, $latitude, $metres],
            )
            ->selectRaw(
                'ST_Distance(opportunities.location, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography) as distance_m',
                [$longitude, $latitude],
            )
            ->orderBy('distance_m');

        return $this->collection($query->limit(100)->get());
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Database\Eloquent\Builder<Opportunity>
     */
    private function base(array $filters)
    {
        $query = Opportunity::query()
            ->investorVisible()
            ->whereNotNull('opportunities.location')
            ->select([
                'opportunities.id',
                'opportunities.uuid',
                'opportunities.title',
                'opportunities.reference',
                'opportunities.latitude',
                'opportunities.longitude',
                'opportunities.investment_amount',
                'opportunities.currency',
                'opportunities.sector_id',
                'opportunities.district_id',
            ])
            ->with([
                'sector:id,name',
                'district:id,name,region_id',
                'district.region:id,name',
            ]);

        $sector = trim((string) ($filters['sector'] ?? ''));

        if ($sector !== '') {
            $query->whereIn('opportunities.sector_id', Sector::query()->where('uuid', $sector)->select('id'));
        }

        $subSector = trim((string) ($filters['sub_sector'] ?? ''));

        if ($subSector !== '') {
            $query->whereIn('opportunities.sub_sector_id', SubSector::query()->where('uuid', $subSector)->select('id'));
        }

        $type = trim((string) ($filters['type'] ?? ''));

        if ($type !== '') {
            $query->whereIn('opportunities.enterprise_type_id', EnterpriseType::query()->where('uuid', $type)->select('id'));
        }

        $district = trim((string) ($filters['district'] ?? ''));

        if ($district !== '') {
            $query->whereIn('opportunities.district_id', District::query()->where('uuid', $district)->select('id'));
        }

        $region = trim((string) ($filters['region'] ?? ''));

        if ($region !== '') {
            $query->whereIn(
                'opportunities.district_id',
                District::query()->whereIn('region_id', Region::query()->where('uuid', $region)->select('id'))->select('id'),
            );
        }

        return $query;
    }

    /**
     * @param  Collection<int, Opportunity>  $rows
     * @return array<string, mixed>
     */
    private function collection(Collection $rows): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => $rows->map(function (Opportunity $opportunity): array {
                return [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [(float) $opportunity->longitude, (float) $opportunity->latitude],
                    ],
                    'properties' => [
                        'uuid' => $opportunity->uuid,
                        'title' => $opportunity->title,
                        'reference' => $opportunity->reference,
                        'sector' => $opportunity->sector?->name,
                        'district' => $opportunity->district?->name,
                        'region' => $opportunity->district?->region?->name,
                        'amount' => $opportunity->currency.' '.number_format((float) $opportunity->investment_amount, 0),
                        'url' => route('investor.opportunities.show', $opportunity),
                        'distance_m' => isset($opportunity->distance_m) ? (int) round((float) $opportunity->distance_m) : null,
                    ],
                ];
            })->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    private function envelope(array $filters): array
    {
        $minLng = $this->coordinate($filters['minLng'] ?? null, -180, 180, 'minLng');
        $minLat = $this->coordinate($filters['minLat'] ?? null, -90, 90, 'minLat');
        $maxLng = $this->coordinate($filters['maxLng'] ?? null, -180, 180, 'maxLng');
        $maxLat = $this->coordinate($filters['maxLat'] ?? null, -90, 90, 'maxLat');

        if ($minLng >= $maxLng || $minLat >= $maxLat || ($maxLng - $minLng) > 30 || ($maxLat - $minLat) > 30) {
            throw ValidationException::withMessages([
                'minLng' => 'The map area is too large. Zoom in and try again.',
            ]);
        }

        return [$minLng, $minLat, $maxLng, $maxLat];
    }

    private function coordinate(mixed $value, float $min, float $max, string $field): float
    {
        if (! is_numeric($value) || (float) $value < $min || (float) $value > $max) {
            throw ValidationException::withMessages([
                $field => 'The map request is not valid.',
            ]);
        }

        return (float) $value;
    }
}
