<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        User::query()
            ->whereNotNull('tenant_id')
            ->whereHas('roles', fn ($query) => $query->where('name', 'user'))
            ->each(function (User $user) {
                DB::table('tenant_user')->insertOrIgnore([
                    'user_id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $user->update(['tenant_id' => null]);
            });
    }

    public function down(): void
    {
        DB::table('tenant_user')
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                User::where('id', $row->user_id)
                    ->whereNull('tenant_id')
                    ->update(['tenant_id' => $row->tenant_id]);
            });

        DB::table('tenant_user')->truncate();
    }
};
