<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('slug');
            $table->string('company_tax_number')->nullable()->after('company_name');
            $table->string('company_phone')->nullable()->after('company_tax_number');
            $table->string('company_email')->nullable()->after('company_phone');
            $table->text('company_address')->nullable()->after('company_email');
            $table->string('logo_path')->nullable()->after('company_address');
            $table->timestamp('stopped_at')->nullable()->after('owner_user_id');
            $table->text('stop_note')->nullable()->after('stopped_at');
            $table->foreignId('stopped_by_user_id')->nullable()->after('stop_note')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stopped_by_user_id');
            $table->dropColumn([
                'company_name',
                'company_tax_number',
                'company_phone',
                'company_email',
                'company_address',
                'logo_path',
                'stopped_at',
                'stop_note',
            ]);
        });
    }
};
