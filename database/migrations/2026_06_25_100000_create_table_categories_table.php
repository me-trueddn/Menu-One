<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_categories', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });

        Schema::table('cafe_tables', function (Blueprint $table) {
            $table->foreignId('table_category_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('table_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cafe_tables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('table_category_id');
        });

        Schema::dropIfExists('table_categories');
    }
};
