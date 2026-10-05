<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Policies\Concerns\ChecksRelationships;

class PaymentPolicy
{
    use ChecksRelationships;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'payments.view_all')
            || $this->hasPermission($user, 'payments.view_limited')
            || $this->hasPermission($user, 'payments.view_own');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->hasPermission($user, 'payments.view_all')
            || $this->hasPermission($user, 'payments.view_limited')
            || ($this->hasPermission($user, 'payments.view_own')
                && $user->patient()->whereKey($payment->patient_id)->exists());
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'payments.record');
    }

    public function receipt(User $user, Payment $payment): bool
    {
        return ($this->hasPermission($user, 'receipts.view_all') && $this->view($user, $payment))
            || ($this->hasPermission($user, 'receipts.view_own')
                && $user->patient()->whereKey($payment->patient_id)->exists());
    }
}
