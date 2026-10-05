<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterPatientRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterPatientRequest $request)
    {
        $patientRole = Role::query()->where('slug', 'patient')->firstOrFail();

        $user = DB::transaction(function () use ($request, $patientRole): User {
            $user = User::query()->create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'role_id' => $patientRole->getKey(),
            ]);

            $user->patient()->create();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->dashboardRouteName());
    }
}
