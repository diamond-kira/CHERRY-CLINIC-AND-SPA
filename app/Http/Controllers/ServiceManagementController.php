<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\NavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceManagementController extends Controller
{
    public function create(Request $request, NavigationService $navigation)
    {
        $this->authorize('create', Service::class);

        return view('portal.service-create', ['navigation' => $navigation->forUser($request->user())]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Service::class);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_name' => ['required', 'string', 'max:255'],
            'service_type' => ['required', Rule::in(['clinic', 'spa'])],
            'description' => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:600'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        DB::transaction(function () use ($validated): void {
            $categorySlug = Str::slug($validated['category_name']);
            $category = ServiceCategory::query()->firstOrCreate(
                ['slug' => $categorySlug],
                ['name' => $validated['category_name'], 'service_type' => $validated['service_type'], 'is_active' => true],
            );

            if ($category->service_type !== $validated['service_type']) {
                throw ValidationException::withMessages(['category_name' => 'That category is already assigned to a different service type.']);
            }

            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $suffix = 2;
            while (Service::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            Service::query()->create([
                'service_category_id' => $category->getKey(),
                'name' => $validated['name'],
                'slug' => $slug,
                'service_type' => $validated['service_type'],
                'description' => $validated['description'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'price' => $validated['price'],
                'is_active' => true,
            ]);
        });

        return redirect()->route('admin.services.index')->with('status', 'Service created.');
    }
}
