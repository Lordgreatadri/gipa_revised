<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 16)->unique();
            $table->string('name');
            $table->string('capital')->nullable();
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('capital')->nullable();
            $table->string('category', 32)->nullable();
            $table->timestamps();

            $table->index(['region_id', 'name']);
        });

        Schema::create('staff_districts', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_id', 'district_id']);
            $table->index('district_id');
        });

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference', 64)->unique();

            $table->string('title');
            $table->string('sector', 120);
            $table->string('sub_sector', 120)->nullable();
            $table->string('enterprise_type', 40);
            $table->string('project_status', 32);
            $table->string('workflow_state', 32);

            $table->text('description');
            $table->text('objectives')->nullable();
            $table->text('success_factors')->nullable();
            $table->text('competitive_advantages')->nullable();

            $table->foreignId('district_id')->constrained()->restrictOnDelete();
            $table->string('location_description');
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();

            $table->decimal('investment_amount', 18, 2);
            $table->char('currency', 3);
            $table->string('investment_structure', 40);
            $table->decimal('roi', 8, 4)->nullable();
            $table->decimal('irr', 8, 4)->nullable();
            $table->decimal('npv', 18, 2)->nullable();
            $table->unsignedSmallInteger('payback_period_months')->nullable();
            $table->decimal('projected_revenue', 18, 2)->nullable();

            $table->string('contact_name');
            $table->string('contact_organization')->nullable();
            $table->string('contact_phone', 32);
            $table->string('contact_email');

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['workflow_state', 'id']);
            $table->index(['district_id', 'workflow_state']);
            $table->index('sector');
            $table->index('assigned_to');
        });

        Schema::create('opportunity_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->string('from_state', 32)->nullable();
            $table->string('to_state', 32);
            $table->string('action', 32);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['opportunity_id', 'id']);
        });

        Schema::create('opportunity_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40);
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('mime_type', 120);
            $table->unsignedInteger('size');
            $table->char('checksum', 64);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['opportunity_id', 'category']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("alter table opportunities add constraint opportunities_workflow_state_check check (workflow_state in ('draft', 'pending_approval', 'approved', 'active', 'completed', 'cancelled'))");
            DB::statement("alter table opportunities add constraint opportunities_project_status_check check (project_status in ('not_started', 'in_progress', 'on_hold'))");
            DB::statement("alter table opportunities add constraint opportunities_enterprise_type_check check (enterprise_type in ('sole_proprietorship', 'joint_venture', 'private_limited', 'public_limited', 'cooperative'))");
            DB::statement("alter table opportunities add constraint opportunities_investment_structure_check check (investment_structure in ('local_equity', 'foreign_equity', 'local_loan', 'foreign_loan'))");
            DB::statement("alter table opportunities add constraint opportunities_currency_check check (currency in ('GHS', 'USD'))");
            DB::statement('alter table opportunities add constraint opportunities_latitude_check check (latitude is null or (latitude >= -90 and latitude <= 90))');
            DB::statement('alter table opportunities add constraint opportunities_longitude_check check (longitude is null or (longitude >= -180 and longitude <= 180))');
            DB::statement('alter table opportunities add constraint opportunities_amount_check check (investment_amount >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_documents');
        Schema::dropIfExists('opportunity_transitions');
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('staff_districts');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regions');
    }
};
