<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reports.view_all')
            || $user->hasPermission('reports.view_operational')
            || $user->hasPermission('reports.view_clinical')
            || $user->hasPermission('reports.view_spa');
    }

    public function viewAdministrative(User $user): bool
    {
        return $user->hasPermission('reports.view_all');
    }

    public function viewOperational(User $user): bool
    {
        return $user->hasPermission('reports.view_operational') || $user->hasPermission('reports.view_all');
    }

    public function viewClinical(User $user): bool
    {
        return $user->hasPermission('reports.view_clinical') || $user->hasPermission('reports.view_all');
    }

    public function viewSpa(User $user): bool
    {
        return $user->hasPermission('reports.view_spa') || $user->hasPermission('reports.view_all');
    }
}
