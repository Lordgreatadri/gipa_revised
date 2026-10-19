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
        Schema::create('sms_settings', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->boolean('enabled')->default(false);
            $table->string('provider', 16)->default('log');
            $table->string('sender_id', 32)->default('GIPA');
            $table->string('frog_base_url')->nullable();
            $table->string('frog_username')->nullable();
            $table->text('frog_api_key')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE sms_settings ADD CONSTRAINT sms_settings_provider_check CHECK (provider IN ('log', 'frog'))");
        }

        $now = now();
        $permissionId = DB::table('permissions')->where('name', 'sms.manage')->where('guard_name', 'web')->value('id');

        if ($permissionId === null) {
            $permissionId = DB::table('permissions')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'name' => 'sms.manage',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roleId = DB::table('roles')->where('name', 'Super Administrator')->where('guard_name', 'web')->value('id');

        if ($roleId !== null && ! DB::table('role_has_permissions')->where('permission_id', $permissionId)->where('role_id', $roleId)->exists()) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_settings');
    }
};
