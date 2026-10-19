<?php

use App\Modules\Opportunity\Domain\Catalogue\InvestmentCatalogue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 32)->unique();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sub_sectors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sector_id')->constrained()->restrictOnDelete();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['sector_id', 'name']);
        });

        Schema::create('enterprise_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 32)->unique();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        $sectorIds = [];

        foreach (InvestmentCatalogue::sectors() as $sector) {
            $id = DB::table('sectors')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'code' => $sector['code'],
                'name' => $sector['name'],
                'is_active' => true,
                'sort_order' => $sector['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $sectorIds[$sector['code']] = $id;
        }

        $subIds = [];

        foreach (InvestmentCatalogue::subSectors() as $subSector) {
            $id = DB::table('sub_sectors')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'sector_id' => $sectorIds[$subSector['sector']],
                'code' => $subSector['code'],
                'name' => $subSector['name'],
                'is_active' => true,
                'sort_order' => $subSector['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $subIds[$subSector['code']] = $id;
        }

        $typeIds = [];

        foreach (InvestmentCatalogue::enterpriseTypes() as $type) {
            $id = DB::table('enterprise_types')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'code' => $type['code'],
                'name' => $type['name'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $typeIds[$type['code']] = $id;
        }

        Schema::table('opportunities', function (Blueprint $table) {
            $table->foreignId('sector_id')->nullable()->after('title')->constrained()->restrictOnDelete();
            $table->foreignId('sub_sector_id')->nullable()->after('sector_id')->constrained()->restrictOnDelete();
            $table->foreignId('enterprise_type_id')->nullable()->after('sub_sector_id')->constrained()->restrictOnDelete();
        });

        if (Schema::hasColumn('opportunities', 'sector')) {
            $sectorMap = [
                'Agriculture' => ['AGR', 'AGR-CROP'],
                'Agro-processing' => ['AGR', 'AGR-PROC'],
                'Manufacturing' => ['MAN', 'MAN-PACK'],
                'Energy' => ['ENE', 'ENE-SOLAR'],
                'Tourism' => ['TOU', 'TOU-ECO'],
                'Information and communication' => ['ICT', 'ICT-DATA'],
                'Mining' => ['MIN', 'MIN-IND'],
            ];

            foreach ($sectorMap as $legacy => [$sectorCode, $subCode]) {
                DB::table('opportunities')->where('sector', $legacy)->update([
                    'sector_id' => $sectorIds[$sectorCode],
                    'sub_sector_id' => $subIds[$subCode],
                ]);
            }

            DB::table('opportunities')->whereNull('sector_id')->update([
                'sector_id' => $sectorIds['AGR'],
                'sub_sector_id' => $subIds['AGR-CROP'],
            ]);

            $typeMap = [
                'sole_proprietorship' => 'SP',
                'joint_venture' => 'JV',
                'private_limited' => 'PLL',
                'public_limited' => 'PUB',
                'cooperative' => 'COOP',
            ];

            foreach ($typeMap as $legacy => $code) {
                DB::table('opportunities')->where('enterprise_type', $legacy)->update([
                    'enterprise_type_id' => $typeIds[$code],
                ]);
            }

            DB::table('opportunities')->whereNull('enterprise_type_id')->update([
                'enterprise_type_id' => $typeIds['PLL'],
            ]);

            if (DB::getDriverName() === 'pgsql') {
                DB::statement('alter table opportunities drop constraint if exists opportunities_enterprise_type_check');
            }

            Schema::table('opportunities', function (Blueprint $table) {
                $table->dropColumn(['sector', 'sub_sector', 'enterprise_type']);
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table opportunities alter column sector_id set not null');
            DB::statement('alter table opportunities alter column sub_sector_id set not null');
            DB::statement('alter table opportunities alter column enterprise_type_id set not null');
            DB::statement("alter table opportunities alter column project_status set default 'not_started'");
        }

        DB::table('opportunities')->whereIn('workflow_state', ['draft', 'pending_approval', 'approved'])->update(['project_status' => 'not_started']);
        DB::table('opportunities')->whereIn('workflow_state', ['active', 'completed'])->update(['project_status' => 'in_progress']);
        DB::table('opportunities')->where('workflow_state', 'cancelled')->update(['project_status' => 'on_hold']);
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enterprise_type_id');
            $table->dropConstrainedForeignId('sub_sector_id');
            $table->dropConstrainedForeignId('sector_id');
        });

        Schema::dropIfExists('enterprise_types');
        Schema::dropIfExists('sub_sectors');
        Schema::dropIfExists('sectors');
    }
};
