<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class StaffManagementController extends Controller
{
    public function create(Request $request, NavigationService $navigation)
    {
        $this->authorize('create', Staff::class);
        $this->authorize('assignRole', User::class);

        return view('portal.staff-create', [
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'service_type']),
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Staff::class);
        $this->authorize('assignRole', User::class);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_slug' => ['required', Rule::in(['receptionist', 'doctor', 'therapist'])],
            'staff_code' => ['required', 'string', 'max:80', 'unique:staff,staff_code'],
            'license_number' => ['nullable', 'string', 'max:120', 'unique:staff,license_number'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'service_ids' => ['required_if:role_slug,doctor,therapist', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'distinct', 'exists:services,id'],
            'days' => ['required_if:role_slug,doctor,therapist', 'array', 'min:1'],
            'days.*' => ['integer', Rule::in(range(0, 6))],
            'starts_at' => ['required_if:role_slug,doctor,therapist', 'date_format:H:i'],
            'ends_at' => ['required_if:role_slug,doctor,therapist', 'date_format:H:i', 'after:starts_at'],
        ]);

        if (in_array($validated['role_slug'], ['doctor', 'therapist'], true)) {
            $expectedServiceType = $validated['role_slug'] === 'doctor' ? 'clinic' : 'spa';
            $qualifiedCount = Service::query()->whereIn('id', $validated['service_ids'])->where('service_type', $expectedServiceType)->count();

            if ($qualifiedCount !== count($validated['service_ids'])) {
                throw ValidationException::withMessages(['service_ids' => 'Selected services must match the staff role.']);
            }
        }

        DB::transaction(function () use ($validated): void {
            $role = Role::query()->where('slug', $validated['role_slug'])->firstOrFail();
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role_id' => $role->getKey(),
            ]);
            $staff = $user->staff()->create([
                'staff_code' => $validated['staff_code'],
                'license_number' => $validated['license_number'] ?? null,
                'specialty' => $validated['specialty'] ?? null,
            ]);

            if (! empty($validated['service_ids'])) {
                $staff->services()->sync($validated['service_ids']);
            }

            if (! empty($validated['days'])) {
                $staff->schedules()->createMany(array_map(
                    fn (int $day) => [
                        'day_of_week' => $day,
                        'starts_at' => $validated['starts_at'],
                        'ends_at' => $validated['ends_at'],
                        'is_available' => true,
                    ],
                    $validated['days'],
                ));
            }
        });

        return redirect()->route('admin.staff.index')->with('status', 'Staff account created.');
    }

    public function updateRole(Request $request, Staff $staff)
    {
        $this->authorize('update', $staff);
        $this->authorize('assignRole', $staff->user);
        $validated = $request->validate([
            'role_slug' => ['required', Rule::in(['receptionist', 'doctor', 'therapist'])],
        ]);

        $role = Role::query()->where('slug', $validated['role_slug'])->firstOrFail();
        $staff->user()->update(['role_id' => $role->getKey()]);

        return back()->with('status', 'Staff role updated.');
    }
}
