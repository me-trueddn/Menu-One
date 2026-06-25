<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('okc_devices')) {
            Schema::create('okc_devices', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->string('name');
                $table->string('device_type', 32)->default('pos');
                $table->string('brand')->nullable();
                $table->string('model')->nullable();
                $table->string('endpoint')->nullable();
                $table->string('api_key')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('okc_sales')) {
            Schema::create('okc_sales', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->foreignId('okc_device_id')->constrained('okc_devices')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('amount', 10, 2);
                $table->string('payment_method');
                $table->string('status')->default('queued');
                $table->text('response_message')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('okc_sales');
        Schema::dropIfExists('okc_devices');
    }
};

