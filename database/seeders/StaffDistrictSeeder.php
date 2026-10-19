<?php

namespace Database\Seeders;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Seeder;

class StaffDistrictSeeder extends Seeder
{
    public function run(): void
    {
        $this->assign(AccessRoles::DISTRICT_OFFICER, 'GH-AA-001');
        $this->assign(AccessRoles::FIELD_AGENT, 'GH-AA-002');
    }

    private function assign(string $role, string $districtCode): void
    {
        $user = User::role($role)->first();
        $district = District::query()->where('code', $districtCode)->first();

        if ($user === null || $district === null) {
            return;
        }

        $user->districts()->syncWithoutDetaching([$district->id]);
    }
}
