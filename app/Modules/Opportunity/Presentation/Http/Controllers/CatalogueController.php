<?php

namespace App\Modules\Opportunity\Presentation\Http\Controllers;

use App\Modules\Opportunity\Domain\Models\EnterpriseType;
use App\Modules\Opportunity\Domain\Models\Sector;
use App\Modules\Opportunity\Domain\Models\SubSector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CatalogueController
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.sectors.index');
    }

    public function sectors(): View
    {
        $sectors = Sector::query()->withCount('opportunities')->orderBy('name')->get();

        return view('admin.catalogue.overview', [
            'title' => __('Sectors'),
            'intro' => __('Sectors used on opportunity records. Open the register to add or edit one.'),
            'listRoute' => route('admin.sectors.register'),
            'listLabel' => __('View records'),
            'summary' => [
                'total' => $sectors->count(),
                'active' => $sectors->where('is_active', true)->count(),
                'opportunities' => (int) $sectors->sum('opportunities_count'),
            ],
            'chart' => [
                'labels' => $sectors->pluck('name')->all(),
                'values' => $sectors->map(fn (Sector $sector): int => (int) $sector->opportunities_count)->all(),
                'series' => 'Opportunities',
            ],
        ]);
    }

    public function sectorRegister(): View
    {
        return view('admin.catalogue.sector-register', [
            'sectors' => Sector::query()->withCount(['subSectors', 'opportunities'])->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function subSectors(): View
    {
        $rows = SubSector::query()->with('sector:id,name')->withCount('opportunities')->orderBy('name')->get();

        return view('admin.catalogue.overview', [
            'title' => __('Sub-sectors'),
            'intro' => __('A sub-sector belongs to one sector. Healthcare cannot offer an agriculture activity.'),
            'listRoute' => route('admin.sub-sectors.register'),
            'listLabel' => __('View records'),
            'summary' => [
                'total' => $rows->count(),
                'active' => $rows->where('is_active', true)->count(),
                'opportunities' => (int) $rows->sum('opportunities_count'),
            ],
            'chart' => [
                'labels' => $rows->map(fn (SubSector $row): string => $row->name)->all(),
                'values' => $rows->map(fn (SubSector $row): int => (int) $row->opportunities_count)->all(),
                'series' => 'Opportunities',
            ],
        ]);
    }

    public function subSectorRegister(): View
    {
        return view('admin.catalogue.sub-sector-register', [
            'sectors' => Sector::query()->orderBy('name')->get(),
            'subSectors' => SubSector::query()->with('sector:id,uuid,name')->withCount('opportunities')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function enterpriseTypes(): View
    {
        $types = EnterpriseType::query()->withCount('opportunities')->orderBy('name')->get();

        return view('admin.catalogue.overview', [
            'title' => __('Enterprise types'),
            'intro' => __('The legal forms an opportunity can use. Open the register to add or edit one.'),
            'listRoute' => route('admin.types.register'),
            'listLabel' => __('View records'),
            'summary' => [
                'total' => $types->count(),
                'active' => $types->where('is_active', true)->count(),
                'opportunities' => (int) $types->sum('opportunities_count'),
            ],
            'chart' => [
                'labels' => $types->pluck('name')->all(),
                'values' => $types->map(fn (EnterpriseType $type): int => (int) $type->opportunities_count)->all(),
                'series' => 'Opportunities',
            ],
        ]);
    }

    public function enterpriseTypeRegister(): View
    {
        return view('admin.catalogue.type-register', [
            'enterpriseTypes' => EnterpriseType::query()->withCount('opportunities')->orderBy('name')->get(),
        ]);
    }

    public function storeSector(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160', 'unique:sectors,name'],
            'code' => ['required', 'string', 'max:32', 'unique:sectors,code', 'regex:/^[A-Z0-9-]+$/'],
        ]);

        Sector::query()->create([
            'name' => $data['name'],
            'code' => Str::upper($data['code']),
            'is_active' => true,
            'sort_order' => (int) Sector::query()->max('sort_order') + 1,
        ]);

        return back()->with('status', 'Sector saved.');
    }

    public function updateSector(Request $request, Sector $sector): RedirectResponse
    {
        $data = $this->validateRecord($request, [
            'name' => ['required', 'string', 'max:160', Rule::unique('sectors', 'name')->ignore($sector->id)],
            'code' => ['required', 'string', 'max:32', Rule::unique('sectors', 'code')->ignore($sector->id), 'regex:/^[A-Z0-9-]+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ], $sector->uuid);

        $sector->fill([
            'name' => $data['name'],
            'code' => Str::upper($data['code']),
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ])->save();

        return back()->with('status', 'Sector updated.');
    }

    public function destroySector(Sector $sector): RedirectResponse
    {
        if ($sector->subSectors()->exists() || $sector->opportunities()->exists()) {
            return back()->withErrors(['sector' => 'This sector is still used by a sub-sector or an opportunity.']);
        }

        $sector->delete();

        return back()->with('status', 'Sector removed.');
    }

    public function storeSubSector(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sector_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:160'],
            'code' => ['required', 'string', 'max:32', 'unique:sub_sectors,code', 'regex:/^[A-Z0-9-]+$/'],
        ]);

        $sector = Sector::query()->where('uuid', $data['sector_id'])->first();

        if ($sector === null) {
            return back()->withErrors(['sector_id' => 'Choose a sector.'])->withInput();
        }

        if ($sector->subSectors()->where('name', $data['name'])->exists()) {
            return back()->withErrors(['name' => 'That sub-sector already exists in this sector.'])->withInput();
        }

        SubSector::query()->create([
            'sector_id' => $sector->id,
            'name' => $data['name'],
            'code' => Str::upper($data['code']),
            'is_active' => true,
            'sort_order' => (int) $sector->subSectors()->max('sort_order') + 1,
        ]);

        return back()->with('status', 'Sub-sector saved.');
    }

    public function updateSubSector(Request $request, SubSector $subSector): RedirectResponse
    {
        $data = $this->validateRecord($request, [
            'sector_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:160'],
            'code' => ['required', 'string', 'max:32', Rule::unique('sub_sectors', 'code')->ignore($subSector->id), 'regex:/^[A-Z0-9-]+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ], $subSector->uuid);

        $sector = Sector::query()->where('uuid', $data['sector_id'])->first();

        if ($sector === null) {
            return back()->withErrors(['sector_id' => 'Choose a sector.'])->withInput()->with('editing', $subSector->uuid);
        }

        if ($sector->id !== $subSector->sector_id && $subSector->opportunities()->exists()) {
            return back()->withErrors(['sector_id' => 'This sub-sector is used by an opportunity, so it stays in its current sector.'])->withInput()->with('editing', $subSector->uuid);
        }

        if ($sector->subSectors()->where('name', $data['name'])->whereKeyNot($subSector->id)->exists()) {
            return back()->withErrors(['name' => 'That sub-sector already exists in this sector.'])->withInput()->with('editing', $subSector->uuid);
        }

        $subSector->fill([
            'sector_id' => $sector->id,
            'name' => $data['name'],
            'code' => Str::upper($data['code']),
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ])->save();

        return back()->with('status', 'Sub-sector updated.');
    }

    public function destroySubSector(SubSector $subSector): RedirectResponse
    {
        if ($subSector->opportunities()->exists()) {
            return back()->withErrors(['sub_sector' => 'This sub-sector is still used by an opportunity.']);
        }

        $subSector->delete();

        return back()->with('status', 'Sub-sector removed.');
    }

    public function storeEnterpriseType(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160', 'unique:enterprise_types,name'],
            'code' => ['required', 'string', 'max:32', 'unique:enterprise_types,code', 'regex:/^[A-Z0-9-]+$/'],
        ]);

        EnterpriseType::query()->create([
            'name' => $data['name'],
            'code' => Str::upper($data['code']),
            'is_active' => true,
        ]);

        return back()->with('status', 'Enterprise type saved.');
    }

    public function updateEnterpriseType(Request $request, EnterpriseType $enterpriseType): RedirectResponse
    {
        $data = $this->validateRecord($request, [
            'name' => ['required', 'string', 'max:160', Rule::unique('enterprise_types', 'name')->ignore($enterpriseType->id)],
            'code' => ['required', 'string', 'max:32', Rule::unique('enterprise_types', 'code')->ignore($enterpriseType->id), 'regex:/^[A-Z0-9-]+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ], $enterpriseType->uuid);

        $enterpriseType->fill([
            'name' => $data['name'],
            'code' => Str::upper($data['code']),
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ])->save();

        return back()->with('status', 'Enterprise type updated.');
    }

    public function destroyEnterpriseType(EnterpriseType $enterpriseType): RedirectResponse
    {
        if ($enterpriseType->opportunities()->exists()) {
            return back()->withErrors(['enterprise_type' => 'This enterprise type is still used by an opportunity.']);
        }

        $enterpriseType->delete();

        return back()->with('status', 'Enterprise type removed.');
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function validateRecord(Request $request, array $rules, string $uuid): array
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException(
                $validator,
                back()->withErrors($validator)->withInput()->with('editing', $uuid),
            );
        }

        return $validator->validated();
    }
}
