<?php

use App\Modules\Geography\Application\GhanaSamplePoint;
use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileOnboardingState;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileStatus;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investor_profiles', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('organization_name');
            $table->string('business_email');
            $table->string('contact_phone', 20);
            $table->string('profile_type', 20)->default(ProfileType::Individual->value);
            $table->string('nationality', 120);
            $table->string('country_code', 8);
            $table->string('currency', 3)->default('GHS');
            $table->string('preferred_language', 8)->default('en');
            $table->string('onboarding_state', 32)->default(ProfileOnboardingState::PendingApproval->value);
            $table->string('status', 16)->default(ProfileStatus::Active->value);
            $table->timestamp('last_engaged_at')->nullable();
            $table->boolean('consent_to_delete')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('in_app_notifications', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('href')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });

        Schema::create('contact_replies', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('contact_us_id')->constrained('contact_us')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('engagement_messages', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('investor_engagement_id')->constrained('investor_engagements')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('sms_messages', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference', 40)->unique();
            $table->string('recipient', 20);
            $table->text('body');
            $table->string('context', 64);
            $table->string('provider', 32);
            $table->string('status', 16);
            $table->string('provider_message_id')->nullable();
            $table->string('error', 500)->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX investor_profiles_user_active_unique ON investor_profiles (user_id) WHERE deleted_at IS NULL');
            DB::statement("ALTER TABLE investor_profiles ADD CONSTRAINT investor_profiles_type_check CHECK (profile_type IN ('individual', 'organization'))");
            DB::statement("ALTER TABLE investor_profiles ADD CONSTRAINT investor_profiles_onboarding_check CHECK (onboarding_state IN ('pending_approval', 'approved'))");
            DB::statement("ALTER TABLE investor_profiles ADD CONSTRAINT investor_profiles_status_check CHECK (status IN ('active', 'inactive'))");
            DB::statement("ALTER TABLE sms_messages ADD CONSTRAINT sms_messages_status_check CHECK (status IN ('queued', 'sent', 'failed', 'skipped'))");
        }

        // $this->grantProfileReview();
        // $this->placeSampleOpportunities();
        // $this->backfillDemoProfiles();
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
        Schema::dropIfExists('engagement_messages');
        Schema::dropIfExists('contact_replies');
        Schema::dropIfExists('in_app_notifications');
        Schema::dropIfExists('investor_profiles');
    }

    // private function grantProfileReview(): void
    // {
    //     $now = now();
    //     $permissionId = DB::table('permissions')->where('name', 'investor.profiles.review')->where('guard_name', 'web')->value('id');

    //     if ($permissionId === null) {
    //         $permissionId = DB::table('permissions')->insertGetId([
    //             'uuid' => (string) Str::uuid(),
    //             'name' => 'investor.profiles.review',
    //             'guard_name' => 'web',
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ]);
    //     }

    //     $roleIds = DB::table('roles')->where('guard_name', 'web')->whereIn('name', ['Super Administrator', 'Reviewer / Approver'])->pluck('id');

    //     foreach ($roleIds as $roleId) {
    //         $exists = DB::table('role_has_permissions')->where('permission_id', $permissionId)->where('role_id', $roleId)->exists();

    //         if (! $exists) {
    //             DB::table('role_has_permissions')->insert([
    //                 'permission_id' => $permissionId,
    //                 'role_id' => $roleId,
    //             ]);
    //         }
    //     }
    // }

    // private function placeSampleOpportunities(): void
    // {
    //     if (! Schema::hasTable('opportunities') || ! Schema::hasColumn('opportunities', 'location')) {
    //         return;
    //     }

    //     $rows = DB::table('opportunities')
    //         ->join('districts', 'districts.id', '=', 'opportunities.district_id')
    //         ->join('regions', 'regions.id', '=', 'districts.region_id')
    //         ->get(['opportunities.id', 'regions.code']);

    //     foreach ($rows as $row) {
    //         [$latitude, $longitude] = GhanaSamplePoint::near((string) $row->code, (int) $row->id);
    //         DB::update(
    //             'update opportunities set latitude = ?, longitude = ?, location = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography where id = ?',
    //             [$latitude, $longitude, $longitude, $latitude, $row->id],
    //         );
    //     }
    // }

    // private function backfillDemoProfiles(): void
    // {
    //     $investors = DB::table('users')
    //         ->where('user_type', UserType::Investor->value)
    //         ->where('email', 'like', '%@example.test')
    //         ->get(['id', 'name', 'email', 'phone']);

    //     foreach ($investors as $investor) {
    //         $exists = DB::table('investor_profiles')->where('user_id', $investor->id)->whereNull('deleted_at')->exists();

    //         if ($exists) {
    //             continue;
    //         }

    //         $now = now();
    //         DB::table('investor_profiles')->insert([
    //             'uuid' => (string) Str::uuid(),
    //             'user_id' => $investor->id,
    //             'organization_name' => $investor->name,
    //             'business_email' => $investor->email,
    //             'contact_phone' => $investor->phone,
    //             'profile_type' => ProfileType::Individual->value,
    //             'nationality' => 'Ghana',
    //             'country_code' => 'GH',
    //             'currency' => 'GHS',
    //             'preferred_language' => 'en',
    //             'onboarding_state' => ProfileOnboardingState::Approved->value,
    //             'status' => ProfileStatus::Active->value,
    //             'consent_to_delete' => false,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ]);
    //     }
    // }
};
