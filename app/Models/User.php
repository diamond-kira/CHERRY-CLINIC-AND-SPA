<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role?->slug === $role;
    }

    public function hasPermission(string $permission): bool
    {
        $this->loadMissing('role.permissions');

        return $this->role?->permissions->contains('slug', $permission) ?? false;
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role?->slug) {
            'super_admin' => 'admin.dashboard',
            'receptionist' => 'receptionist.dashboard',
            'doctor' => 'doctor.dashboard',
            'therapist' => 'therapist.dashboard',
            'patient' => 'patient.dashboard',
            default => 'login',
        };
    }
}
