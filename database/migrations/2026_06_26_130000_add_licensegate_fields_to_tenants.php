<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (! Schema::hasColumn('tenants', 'licensegate_license_id')) {
                    $table->string('licensegate_license_id')->nullable()->after('stopped_by_user_id');
                }
                if (! Schema::hasColumn('tenants', 'licensegate_license_key')) {
                    $table->string('licensegate_license_key')->nullable()->after('licensegate_license_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (Schema::hasColumn('tenants', 'licensegate_license_key')) {
                    $table->dropColumn('licensegate_license_key');
                }
                if (Schema::hasColumn('tenants', 'licensegate_license_id')) {
                    $table->dropColumn('licensegate_license_id');
                }
            });
        }
    }
};
