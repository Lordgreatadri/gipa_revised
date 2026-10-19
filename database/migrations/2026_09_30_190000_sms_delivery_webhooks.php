<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_calls', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('url', 512);
            $table->json('headers')->nullable();
            $table->json('payload')->nullable();
            $table->json('attachments')->nullable();
            $table->text('exception')->nullable();
            $table->timestamps();
            $table->index(['name', 'created_at']);
        });

        Schema::table('sms_provider_configs', function (Blueprint $table): void {
            $table->text('webhook_secret')->nullable()->after('credentials');
        });

        Schema::table('sms_messages', function (Blueprint $table): void {
            $table->timestamp('delivered_at')->nullable()->after('sent_at');
        });

        Schema::create('sms_delivery_events', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sms_message_id')->nullable()->constrained('sms_messages')->nullOnDelete();
            $table->string('provider', 40);
            $table->string('provider_message_id')->nullable();
            $table->string('status', 24);
            $table->string('raw_status', 64)->nullable();
            $table->string('detail')->nullable();
            $table->unsignedBigInteger('webhook_call_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
            $table->index(['provider', 'provider_message_id']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sms_messages DROP CONSTRAINT IF EXISTS sms_messages_status_check');
            DB::statement("ALTER TABLE sms_messages ADD CONSTRAINT sms_messages_status_check CHECK (status IN ('queued', 'sent', 'failed', 'skipped', 'delivered', 'undelivered'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_delivery_events');

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sms_messages DROP CONSTRAINT IF EXISTS sms_messages_status_check');
            DB::statement("ALTER TABLE sms_messages ADD CONSTRAINT sms_messages_status_check CHECK (status IN ('queued', 'sent', 'failed', 'skipped'))");
        }

        Schema::table('sms_messages', function (Blueprint $table): void {
            $table->dropColumn('delivered_at');
        });

        Schema::table('sms_provider_configs', function (Blueprint $table): void {
            $table->dropColumn('webhook_secret');
        });

        Schema::dropIfExists('webhook_calls');
    }
};
