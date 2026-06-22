<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_staff_invitations', function (Blueprint $table) {
            $table->foreignId('revoked_by_user_id')->nullable()->after('declined_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenant_staff_invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revoked_by_user_id');
        });
    }
};
