<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('license_types')) {
            Schema::create('license_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->unsignedInteger('duration_days');
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tenant_licenses')) {
            Schema::create('tenant_licenses', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id');
                $table->foreignId('license_type_id')->constrained()->cascadeOnDelete();
                $table->dateTime('starts_at');
                $table->dateTime('expires_at');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->index(['tenant_id', 'expires_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_licenses');
        Schema::dropIfExists('license_types');
    }
};
