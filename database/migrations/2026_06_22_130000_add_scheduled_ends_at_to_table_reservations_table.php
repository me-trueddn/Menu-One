<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_reservations', function (Blueprint $table) {
            $table->dateTime('scheduled_ends_at')->nullable()->after('ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('table_reservations', function (Blueprint $table) {
            $table->dropColumn('scheduled_ends_at');
        });
    }
};
