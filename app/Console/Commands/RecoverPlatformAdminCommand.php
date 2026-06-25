<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserPublicIdGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RecoverPlatformAdminCommand extends Command
{
    protected $signature = 'platform:recover-admin
        {--email= : Super admin email}
        {--name= : Display name}
        {--password= : Login password}';

    protected $description = 'Create or restore the platform super admin when the users table is empty';

    public function handle(): int
    {
        Role::firstOrCreate(['name' => 'platform_admin', 'guard_name' => 'web']);

        $email = (string) ($this->option('email') ?: env('SUPER_ADMIN_EMAIL', ''));

        if ($email === '') {
            $email = (string) $this->ask('Super admin email');
        }

        $email = strtolower(trim($email));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email is required.');

            return self::FAILURE;
        }

        $name = (string) ($this->option('name') ?: env('SUPER_ADMIN_NAME', 'Platform Admin'));
        $password = (string) ($this->option('password') ?: env('SUPER_ADMIN_PASSWORD', ''));

        if ($password === '') {
            $password = (string) $this->secret('Password (min 8 characters)');
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->whereNull('tenant_id')->first();

        if ($user === null) {
            $user = User::create([
                'tenant_id' => null,
                'public_id' => UserPublicIdGenerator::generate(),
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'password_changed_at' => now(),
                'email_verified_at' => now(),
                'is_super_admin' => true,
                'is_active' => true,
            ]);

            $this->info("Created super admin: {$email}");
        } else {
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
                'password_changed_at' => now(),
                'is_super_admin' => true,
                'is_active' => true,
                'tenant_id' => null,
            ]);

            $this->info("Updated existing account as super admin: {$email}");
        }

        $user->syncRoles(['platform_admin']);

        $totalUsers = User::query()->count();
        $totalTenants = \App\Models\Tenant::query()->count();

        $this->newLine();
        $this->line('users: '.$totalUsers);
        $this->line('tenants: '.$totalTenants);

        if ($totalTenants === 0) {
            $this->warn('Cafe/tenant records are still empty. Restore a MySQL backup if you had production data.');
        }

        $this->newLine();
        $this->comment('You can log in at /login with the email and password above.');

        return self::SUCCESS;
    }
}
