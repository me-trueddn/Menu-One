<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('okc_devices') && ! Schema::hasColumn('okc_devices', 'device_type')) {
            Schema::table('okc_devices', function (Blueprint $table) {
                $table->string('device_type', 32)->default('pos')->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('okc_devices') && Schema::hasColumn('okc_devices', 'device_type')) {
            Schema::table('okc_devices', function (Blueprint $table) {
                $table->dropColumn('device_type');
            });
        }
    }
};
