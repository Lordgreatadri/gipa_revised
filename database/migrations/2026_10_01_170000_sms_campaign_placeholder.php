<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_campaigns', function (Blueprint $table): void {
            $table->string('placeholder', 32)->nullable()->after('personalize');
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE sms_campaigns ADD CONSTRAINT sms_campaigns_placeholder_check CHECK (placeholder IS NULL OR placeholder IN ('name', 'business_name'))");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sms_campaigns DROP CONSTRAINT IF EXISTS sms_campaigns_placeholder_check');
        }

        Schema::table('sms_campaigns', function (Blueprint $table): void {
            $table->dropColumn('placeholder');
        });
    }
};
