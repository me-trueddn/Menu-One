<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'licensegate_active')) {
                $table->dropColumn('licensegate_active');
            }
            if (Schema::hasColumn('tenants', 'licensegate_license_key')) {
                $table->dropColumn('licensegate_license_key');
            }
            if (Schema::hasColumn('tenants', 'licensegate_license_id')) {
                $table->dropColumn('licensegate_license_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'licensegate_license_id')) {
                $table->string('licensegate_license_id')->nullable()->after('stopped_by_user_id');
            }
            if (! Schema::hasColumn('tenants', 'licensegate_license_key')) {
                $table->string('licensegate_license_key')->nullable()->after('licensegate_license_id');
            }
            if (! Schema::hasColumn('tenants', 'licensegate_active')) {
                $table->boolean('licensegate_active')->default(true)->after('licensegate_license_key');
            }
        });
    }
};
