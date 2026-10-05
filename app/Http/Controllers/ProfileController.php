<?php

namespace App\Http\Controllers;

use App\Services\NavigationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request, NavigationService $navigation)
    {
        $this->authorize('view', $request->user());

        return view('portal.profile', [
            'user' => $request->user()->load('role', 'patient', 'staff'),
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $this->authorize('update', $user);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update(['name' => $validated['name'], 'email' => $validated['email']]);
        $user->patient?->update(['phone' => $validated['phone'] ?? null]);

        return back()->with('status', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return back()->with('status', 'Password changed.');
    }
}
