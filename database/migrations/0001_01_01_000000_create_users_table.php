<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 150);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            // E.164. Separate trust signal from e-mail (ADR-0011).
            $table->string('phone', 20)->unique();
            $table->timestamp('phone_verified_at')->nullable();
            // 'investor' (self-registered) or 'staff' (invited). Never mass-assigned.
            $table->string('user_type', 16);
            $table->foreignId('country_id')->nullable()->constrained('countries')->restrictOnDelete();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->index('user_type');
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type IN ('investor', 'staff'))");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_phone_e164_check CHECK (phone ~ '^\\+[1-9][0-9]{6,14}$')");
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
