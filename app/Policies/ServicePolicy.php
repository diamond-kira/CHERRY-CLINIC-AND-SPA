<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Policies\Concerns\ChecksRelationships;

class ServicePolicy
{
    use ChecksRelationships;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'services.view');
    }

    public function view(User $user, Service $service): bool
    {
        return $this->hasPermission($user, 'services.view')
            && ($service->is_active || $this->hasPermission($user, 'services.manage'));
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'services.manage');
    }

    public function update(User $user, Service $service): bool
    {
        return $this->hasPermission($user, 'services.manage');
    }

    public function changePrice(User $user, Service $service): bool
    {
        return $this->hasPermission($user, 'services.change_prices');
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->hasPermission($user, 'services.manage');
    }
}
