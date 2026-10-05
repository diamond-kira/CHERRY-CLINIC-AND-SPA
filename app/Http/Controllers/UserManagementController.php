<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request, NavigationService $navigation)
    {
        $this->authorize('viewAny', User::class);

        return view('portal.users', [
            'users' => User::query()->with('role:id,name,slug')->orderBy('name')->paginate(25),
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }
}
