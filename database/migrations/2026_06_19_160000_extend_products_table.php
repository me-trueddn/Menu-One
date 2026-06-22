<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->text('description')->nullable()->after('code');
            $table->string('barcode')->nullable()->after('description');
            $table->string('unit_type', 20)->default('piece')->after('barcode');
            $table->decimal('purchase_price', 10, 2)->nullable()->after('price');
            $table->unsignedTinyInteger('vat_rate')->default(10)->after('purchase_price');
            $table->string('image_path')->nullable()->after('vat_rate');
            $table->json('extras')->nullable()->after('image_path');

            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'code']);
            $table->dropColumn([
                'code',
                'description',
                'barcode',
                'unit_type',
                'purchase_price',
                'vat_rate',
                'image_path',
                'extras',
            ]);
        });
    }
};
