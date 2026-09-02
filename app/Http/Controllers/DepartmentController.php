<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\AuditService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->orderBy('name')->get();
        return view('departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $dept = Department::create($validated);

        AuditService::log('create', 'Department', "Created department {$dept->name}", null, $dept->toArray());

        return back()->with('success', "Department '{$dept->name}' created.");
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,' . $department->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $old = $department->toArray();
        $department->update($validated);

        AuditService::log('update', 'Department', "Updated department {$department->name}", $old, $department->toArray());

        return back()->with('success', "Department '{$department->name}' updated.");
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete department that has active employees. Reassign them first.']);
        }

        $old = $department->toArray();
        $name = $department->name;
        $department->delete();

        AuditService::log('delete', 'Department', "Deleted department {$name}", $old);

        return back()->with('success', "Department '{$name}' deleted.");
    }
}
