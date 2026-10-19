<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Public enquiries plus the workflow columns the staff inbox will use later.
     * Management UI is intentionally not shipped with this migration.
     */
    public function up(): void
    {
        Schema::create('contact_us', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference', 32)->unique();

            $table->string('name');
            $table->string('email');
            $table->string('phone_number', 20)->nullable();
            $table->string('available_time_for_call')->nullable();

            $table->string('subject');
            $table->text('message');
            $table->string('ip_address', 45)->nullable();

            $table->string('status', 20)->default('new');
            $table->string('read_by')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('sms_sent')->default(false);
            $table->timestamp('sms_sent_at')->nullable();
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE contact_us ADD CONSTRAINT contact_us_status_check CHECK (status IN ('new', 'read', 'responded', 'closed'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_us');
    }
};
