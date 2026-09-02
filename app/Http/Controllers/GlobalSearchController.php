<?php

namespace App\Http\Controllers;

use App\Models\DailyWorkEntry;
use App\Models\Employee;
use App\Models\WorkCategory;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([
                'employees' => [],
                'categories' => [],
                'work_entries' => [],
            ]);
        }

        $isAdmin = auth()->user()?->isAdmin() ?? false;
        $currentUserId = auth()->id();

        $employees = Employee::with('department')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('employee_code', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('designation', 'like', "%{$q}%");
            })
            ->take(6)
            ->get()
            ->map(function ($emp) use ($isAdmin, $currentUserId) {
                $url = $isAdmin 
                    ? route('employees.show', $emp->id)
                    : ($emp->user_id === $currentUserId ? route('profile') : route('work-entries.index', ['employee_id' => $emp->id]));

                return [
                    'id' => $emp->id,
                    'title' => $emp->name . ' (' . $emp->employee_code . ')',
                    'subtitle' => $emp->designation . ' • ' . ($emp->department->name ?? 'No Dept'),
                    'url' => $url,
                    'avatar' => $emp->photo_url,
                ];
            });

        $categories = WorkCategory::where('name', 'like', "%{$q}%")
            ->take(4)
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'title' => $cat->name,
                    'subtitle' => $cat->description ?? 'Work category',
                    'url' => route('work-entries.index', ['category_id' => $cat->id]),
                    'color' => $cat->color,
                ];
            });

        $workEntries = DailyWorkEntry::with(['employee', 'category'])
            ->where('remarks', 'like', "%{$q}%")
            ->orWhere('date', 'like', "%{$q}%")
            ->take(5)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'title' => ($entry->employee->name ?? 'Emp') . ' - ' . ($entry->category->name ?? 'Task') . ' (' . $entry->quantity . ')',
                    'subtitle' => $entry->date->format('d M Y') . ($entry->remarks ? ' • ' . $entry->remarks : ''),
                    'url' => route('work-entries.index', ['date' => $entry->date->format('Y-m-d')]),
                ];
            });

        return response()->json([
            'employees' => $employees,
            'categories' => $categories,
            'work_entries' => $workEntries,
        ]);
    }
}
