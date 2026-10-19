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
        Schema::table('sms_provider_configs', function (Blueprint $table): void {
            $table->decimal('credit_threshold', 12, 2)->nullable()->after('priority');
            $table->unsignedInteger('bundle_threshold')->nullable()->after('credit_threshold');
            $table->string('balance_alert_level', 32)->nullable()->after('balance_checked_at');
            $table->timestamp('balance_alerted_at')->nullable()->after('balance_alert_level');
        });

        Schema::table('sms_messages', function (Blueprint $table): void {
            $table->foreignId('campaign_id')->nullable()->after('context');
            $table->foreignId('resent_from_id')->nullable()->after('campaign_id');
        });

        Schema::create('sms_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 120);
            $table->string('audience', 40);
            $table->text('body');
            $table->boolean('personalize')->default(false);
            $table->string('status', 16)->default('draft');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sms_campaign_recipients', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sms_campaign_id')->constrained('sms_campaigns')->cascadeOnDelete();
            $table->string('phone', 20);
            $table->string('name')->nullable();
            $table->string('business_name')->nullable();
            $table->string('status', 16)->default('pending');
            $table->string('detail')->nullable();
            $table->foreignId('sms_message_id')->nullable()->constrained('sms_messages')->nullOnDelete();
            $table->timestamps();
            $table->index(['sms_campaign_id', 'status']);
        });

        Schema::table('sms_messages', function (Blueprint $table): void {
            $table->foreign('campaign_id')->references('id')->on('sms_campaigns')->nullOnDelete();
            $table->foreign('resent_from_id')->references('id')->on('sms_messages')->nullOnDelete();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE sms_campaigns ADD CONSTRAINT sms_campaigns_status_check CHECK (status IN ('draft', 'sending', 'completed', 'failed'))");
            DB::statement("ALTER TABLE sms_campaigns ADD CONSTRAINT sms_campaigns_audience_check CHECK (audience IN ('active_investors', 'all_investors', 'overdue_certificates', 'all_staff', 'numbers', 'file'))");
            DB::statement("ALTER TABLE sms_campaign_recipients ADD CONSTRAINT sms_campaign_recipients_status_check CHECK (status IN ('pending', 'sent', 'failed', 'skipped'))");
        }

        Schema::dropIfExists('sms_settings');

        $this->grant('sms.messages.view', ['Super Administrator', 'Content / Data Manager']);
        $this->grant('sms.campaigns.manage', ['Super Administrator', 'Content / Data Manager']);
    }

    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('campaign_id');
            $table->dropConstrainedForeignId('resent_from_id');
        });

        Schema::dropIfExists('sms_campaign_recipients');
        Schema::dropIfExists('sms_campaigns');

        Schema::table('sms_provider_configs', function (Blueprint $table): void {
            $table->dropColumn(['credit_threshold', 'bundle_threshold', 'balance_alert_level', 'balance_alerted_at']);
        });
    }

    /**
     * @param  list<string>  $roles
     */
    private function grant(string $permission, array $roles): void
    {
        $now = now();
        $permissionId = DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->value('id');

        if ($permissionId === null) {
            $permissionId = DB::table('permissions')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roleIds = DB::table('roles')->where('guard_name', 'web')->whereIn('name', $roles)->pluck('id');

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
};
