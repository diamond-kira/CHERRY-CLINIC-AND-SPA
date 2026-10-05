<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class MakeSuperAdmin extends Command
{
    protected $signature = 'czcms:make-super-admin {email : Email address of an existing account}';

    protected $description = 'Grant the super-admin role to an existing account from the server console';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No account exists for that email address.');

            return self::FAILURE;
        }

        $role = Role::query()->where('slug', 'super_admin')->firstOrFail();

        if ($user->role_id === $role->getKey()) {
            $this->info('This account is already a super admin.');

            return self::SUCCESS;
        }

        if (! $this->confirm("Grant the super-admin role to {$user->email}?")) {
            $this->warn('No changes were made.');

            return self::SUCCESS;
        }

        $user->role()->associate($role);
        $user->save();

        AuditLog::query()->create([
            'user_id' => null,
            'action' => 'user.super_admin_bootstrapped',
            'entity_type' => User::class,
            'entity_id' => $user->getKey(),
            'description' => 'Super-admin access granted through the server console.',
        ]);

        $this->info('Super-admin access granted.');

        return self::SUCCESS;
    }
}
