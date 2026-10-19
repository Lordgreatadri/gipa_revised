<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('create extension if not exists postgis');
        DB::statement('alter table opportunities add column location geography(Point, 4326)');
        DB::statement('
            update opportunities
            set location = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography
            where latitude is not null and longitude is not null
        ');
        DB::statement('create index opportunities_location_gist on opportunities using gist (location)');
        DB::statement('alter table districts add column boundary geography(MultiPolygon, 4326)');
        DB::statement('create index districts_boundary_gist on districts using gist (boundary)');

        Schema::create('investor_engagements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('investor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 16);
            $table->string('state', 32)->default('submitted');
            $table->string('reference', 40)->unique();
            $table->text('message')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['investor_id', 'opportunity_id', 'kind']);
            $table->index(['state', 'id']);
        });

        DB::statement("alter table investor_engagements add constraint investor_engagements_kind_check check (kind in ('inquiry', 'interest'))");
        DB::statement("alter table investor_engagements add constraint investor_engagements_state_check check (state = 'submitted')");

        $now = now();
        $permissionId = DB::table('permissions')->where('name', 'catalogue.manage')->where('guard_name', 'web')->value('id');

        if ($permissionId === null) {
            $permissionId = DB::table('permissions')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'name' => 'catalogue.manage',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roleIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereIn('name', ['Super Administrator', 'Content / Data Manager'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            $exists = DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('role_id', $roleId)
                ->exists();

            if (! $exists) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_engagements');

        DB::statement('alter table districts drop column if exists boundary');
        DB::statement('alter table opportunities drop column if exists location');
    }
};
