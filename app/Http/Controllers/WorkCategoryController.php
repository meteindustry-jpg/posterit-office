<?php

namespace App\Http\Controllers;

use App\Models\WorkCategory;
use App\Services\AuditService;
use Illuminate\Http\Request;

class WorkCategoryController extends Controller
{
    public function index()
    {
        $categories = WorkCategory::withCount('workEntries')->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:work_categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['required', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $cat = WorkCategory::create($validated);

        AuditService::log('create', 'Work Category', "Created category {$cat->name}", null, $cat->toArray());

        return back()->with('success', "Category '{$cat->name}' added successfully.");
    }

    public function update(Request $request, WorkCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:work_categories,name,'.$category->id],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['required', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $old = $category->toArray();
        $validated['is_active'] = $request->boolean('is_active');
        $category->update($validated);

        AuditService::log('update', 'Work Category', "Updated category {$category->name}", $old, $category->toArray());

        return back()->with('success', "Category '{$category->name}' updated.");
    }

    public function destroy(WorkCategory $category)
    {
        if ($category->workEntries()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete category that has existing work entries. Disable it instead.']);
        }

        $old = $category->toArray();
        $name = $category->name;
        $category->delete();

        AuditService::log('delete', 'Work Category', "Deleted category {$name}", $old);

        return back()->with('success', "Category '{$name}' deleted.");
    }
}
