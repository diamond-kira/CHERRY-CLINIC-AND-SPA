<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use App\Policies\Concerns\ChecksRelationships;

class AuditLogPolicy
{
    use ChecksRelationships;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'audit_logs.view');
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        return $this->hasPermission($user, 'audit_logs.view');
    }
}
