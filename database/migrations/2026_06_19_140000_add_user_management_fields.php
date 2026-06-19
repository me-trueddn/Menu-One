<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('public_id', 6)->nullable()->unique()->after('id');
            $table->string('phone', 30)->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('password');
            $table->boolean('is_super_admin')->default(false)->after('is_active');
            $table->boolean('two_factor_enabled')->default(false)->after('is_super_admin');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->string('description')->nullable()->after('guard_name');
            $table->boolean('is_system')->default(false)->after('description');
        });

        Schema::create('user_login_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64);
            $table->string('session_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login_tokens');

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_system']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['public_id', 'phone', 'is_active', 'is_super_admin', 'two_factor_enabled']);
        });
    }
};
