<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_integrations')) {
            Schema::create('tenant_integrations', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->string('provider');
                $table->boolean('is_enabled')->default(false);
                $table->json('config')->nullable();
                $table->text('webhook_secret')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'provider']);
            });
        }

        if (! Schema::hasTable('integration_product_mappings')) {
            Schema::create('integration_product_mappings', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->string('provider');
                $table->string('external_id');
                $table->string('external_name');
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();

                $table->unique(['tenant_id', 'provider', 'external_id'], 'int_prod_map_unique');
            });
        }

        Schema::table('cafe_tables', function (Blueprint $table) {
            if (! Schema::hasColumn('cafe_tables', 'is_virtual')) {
                $table->boolean('is_virtual')->default(false)->after('status');
            }
            if (! Schema::hasColumn('cafe_tables', 'integration_provider')) {
                $table->string('integration_provider')->nullable()->after('is_virtual');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type')->default('dine_in')->after('user_id');
            }
            if (! Schema::hasColumn('orders', 'integration_provider')) {
                $table->string('integration_provider')->nullable()->after('order_type');
            }
            if (! Schema::hasColumn('orders', 'external_order_id')) {
                $table->string('external_order_id')->nullable()->after('integration_provider');
            }
            if (! Schema::hasColumn('orders', 'integration_status')) {
                $table->string('integration_status')->nullable()->after('external_order_id');
            }
            if (! Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('integration_status');
            }
            if (! Schema::hasColumn('orders', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_name');
            }
            if (! Schema::hasColumn('orders', 'delivery_note')) {
                $table->text('delivery_note')->nullable()->after('customer_phone');
            }
            if (! Schema::hasColumn('orders', 'integration_payload')) {
                $table->json('integration_payload')->nullable()->after('delivery_note');
            }
            if (! Schema::hasColumn('orders', 'payment_collected_externally')) {
                $table->boolean('payment_collected_externally')->default(false)->after('integration_payload');
            }
        });

        if (! Schema::hasIndex('orders', 'orders_integration_external_unique')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unique(
                    ['tenant_id', 'integration_provider', 'external_order_id'],
                    'orders_integration_external_unique',
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasIndex('orders', 'orders_integration_external_unique')) {
                $table->dropUnique('orders_integration_external_unique');
            }
            $table->dropColumn([
                'order_type',
                'integration_provider',
                'external_order_id',
                'integration_status',
                'customer_name',
                'customer_phone',
                'delivery_note',
                'integration_payload',
                'payment_collected_externally',
            ]);
        });

        Schema::table('cafe_tables', function (Blueprint $table) {
            $table->dropColumn(['is_virtual', 'integration_provider']);
        });

        Schema::dropIfExists('integration_product_mappings');
        Schema::dropIfExists('tenant_integrations');
    }
};
