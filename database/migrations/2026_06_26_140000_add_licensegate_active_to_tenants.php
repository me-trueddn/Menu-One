<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenants') && ! Schema::hasColumn('tenants', 'licensegate_active')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->boolean('licensegate_active')->default(true)->after('licensegate_license_key');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'licensegate_active')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('licensegate_active');
            });
        }
    }
};
