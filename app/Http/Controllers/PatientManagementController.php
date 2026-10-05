<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class PatientManagementController extends Controller
{
    public function create(Request $request, NavigationService $navigation)
    {
        $this->authorize('create', Patient::class);

        return view('portal.patient-create', ['navigation' => $navigation->forUser($request->user())]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Patient::class);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        DB::transaction(function () use ($validated): void {
            $patientRole = Role::query()->where('slug', 'patient')->firstOrFail();
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role_id' => $patientRole->getKey(),
            ]);
            $user->patient()->create(['phone' => $validated['phone'] ?? null]);
        });

        return redirect()->route($request->user()->dashboardRouteName())->with('status', 'Patient account created.');
    }
}
