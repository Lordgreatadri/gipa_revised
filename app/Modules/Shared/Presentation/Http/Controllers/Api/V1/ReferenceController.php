<?php

namespace App\Modules\Shared\Presentation\Http\Controllers\Api\V1;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Geography\Domain\Models\Region;
use App\Modules\Opportunity\Domain\Models\EnterpriseType;
use App\Modules\Opportunity\Domain\Models\Sector;
use App\Modules\Opportunity\Domain\Models\SubSector;
use App\Modules\Shared\Presentation\Http\Api\ApiEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferenceController
{
    public function regions(): JsonResponse
    {
        $page = Region::query()->orderBy('name')->paginate(50);

        return ApiEnvelope::page($page, fn (Region $region): array => [
            'id' => $region->uuid,
            'code' => $region->code,
            'name' => $region->name,
        ]);
    }

    public function districts(Request $request): JsonResponse
    {
        $region = trim((string) $request->query('region', ''));
        $query = District::query()->with('region:id,uuid,name')->orderBy('name');

        if ($region !== '') {
            $query->whereIn('region_id', Region::query()->where('uuid', $region)->select('id'));
        }

        $page = $query->paginate(50)->withQueryString();

        return ApiEnvelope::page($page, fn (District $district): array => [
            'id' => $district->uuid,
            'code' => $district->code,
            'name' => $district->name,
            'region_id' => $district->region?->uuid,
            'region' => $district->region?->name,
        ]);
    }

    public function sectors(): JsonResponse
    {
        $page = Sector::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->paginate(50);

        return ApiEnvelope::page($page, fn (Sector $sector): array => [
            'id' => $sector->uuid,
            'code' => $sector->code,
            'name' => $sector->name,
        ]);
    }

    public function subSectors(Request $request): JsonResponse
    {
        $sector = trim((string) $request->query('sector', ''));
        $query = SubSector::query()->where('is_active', true)->with('sector:id,uuid')->orderBy('sort_order')->orderBy('name');

        if ($sector !== '') {
            $query->whereIn('sector_id', Sector::query()->where('uuid', $sector)->select('id'));
        }

        $page = $query->paginate(50)->withQueryString();

        return ApiEnvelope::page($page, fn (SubSector $subSector): array => [
            'id' => $subSector->uuid,
            'code' => $subSector->code,
            'name' => $subSector->name,
            'sector_id' => $subSector->sector?->uuid,
        ]);
    }

    public function enterpriseTypes(): JsonResponse
    {
        $page = EnterpriseType::query()->where('is_active', true)->orderBy('name')->paginate(50);

        return ApiEnvelope::page($page, fn (EnterpriseType $type): array => [
            'id' => $type->uuid,
            'code' => $type->code,
            'name' => $type->name,
        ]);
    }
}
