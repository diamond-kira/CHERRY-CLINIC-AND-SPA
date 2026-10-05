<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\NavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index(Request $request, NavigationService $navigation)
    {
        abort_unless($request->user()->hasPermission('settings.manage'), 403);

        return view('portal.settings', [
            'settings' => Setting::query()->orderBy('setting_key')->get(),
            'navigation' => $navigation->forUser($request->user()),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->hasPermission('settings.manage'), 403);
        $validated = $request->validate([
            'settings' => ['required', 'array', 'max:100'],
            'settings.*' => ['nullable', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['settings'] as $key => $value) {
                if (! preg_match('/^[a-z][a-z0-9_.-]{1,99}$/', (string) $key)) {
                    continue;
                }

                Setting::query()->updateOrCreate(
                    ['setting_key' => $key],
                    ['value' => $value, 'is_public' => false],
                );
            }
        });

        return back()->with('status', 'Settings saved.');
    }
}
