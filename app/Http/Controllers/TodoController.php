<?php

namespace App\Http\Controllers;

use App\Models\DailyWorkEntry;
use App\Models\Employee;
use App\Models\Todo;
use App\Models\User;
use App\Models\WorkCategory;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'all');
        $priority = $request->get('priority');
        $search = $request->get('search');

        $query = Todo::with(['user.employee', 'assignedTo.employee', 'workEntry']);

        // Non-admins see tasks created by them OR assigned to them
        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('assigned_to_user_id', $user->id);
            });
        }

        // Tab Filtering
        $todayStr = now()->format('Y-m-d');
        if ($tab === 'today') {
            $query->whereDate('due_date', $todayStr)->where('is_completed', false);
        } elseif ($tab === 'upcoming') {
            $query->whereDate('due_date', '>', $todayStr)->where('is_completed', false);
        } elseif ($tab === 'completed') {
            $query->where('is_completed', true);
        } elseif ($tab === 'high_priority') {
            $query->where('priority', 'high')->where('is_completed', false);
        } elseif ($tab === 'assigned_to_me') {
            $query->where('assigned_to_user_id', $user->id)->where('is_completed', false);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $allTodosForKanban = (clone $query)->orderBy('id', 'desc')->get();

        $todos = $query->orderBy('is_completed', 'asc')
            ->orderByRaw("CASE WHEN priority = 'high' THEN 1 WHEN priority = 'medium' THEN 2 ELSE 3 END")
            ->orderBy('due_date', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(30)
            ->withQueryString();

        // Counts for tab badges
        $baseQuery = Todo::query();
        if (! $user->isAdmin()) {
            $baseQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('assigned_to_user_id', $user->id);
            });
        }

        $counts = [
            'all' => (clone $baseQuery)->where('is_completed', false)->count(),
            'today' => (clone $baseQuery)->whereDate('due_date', $todayStr)->where('is_completed', false)->count(),
            'upcoming' => (clone $baseQuery)->whereDate('due_date', '>', $todayStr)->where('is_completed', false)->count(),
            'high' => (clone $baseQuery)->where('priority', 'high')->where('is_completed', false)->count(),
            'completed' => (clone $baseQuery)->where('is_completed', true)->count(),
        ];

        $users = User::where('is_active', true)->orderBy('name')->get();
        $workCategories = WorkCategory::where('is_active', true)->orderBy('name')->get();
        $categories = ['Design', 'Client Delivery', 'Social Media', 'Video Edit', 'Revision', 'Meeting', 'General'];

        return view('todos.index', compact('todos', 'allTodosForKanban', 'tab', 'counts', 'users', 'categories', 'workCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['nullable', 'in:todo,in_progress,in_review,completed'],
            'category' => ['nullable', 'string', 'max:100'],
            'due_date' => ['nullable', 'date'],
            'due_time' => ['nullable'],
            'reference_url' => ['nullable', 'string', 'max:1000'],
            'reference_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:10240'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'subtasks' => ['nullable', 'array'],
        ]);

        $subtasksData = [];
        if (! empty($request->input('subtasks_text'))) {
            $lines = array_filter(array_map('trim', explode("\n", $request->input('subtasks_text'))));
            foreach ($lines as $line) {
                $subtasksData[] = ['title' => $line, 'completed' => false];
            }
        }

        $status = $validated['status'] ?? 'todo';
        $isCompleted = ($status === 'completed');

        $referenceUrl = $validated['reference_url'] ?? null;
        if ($request->hasFile('reference_file')) {
            $path = $request->file('reference_file')->store('todos', 'public');
            $referenceUrl = 'todos/'.basename($path);
        }

        $todo = Todo::create([
            'user_id' => Auth::id(),
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'status' => $status,
            'category' => ($validated['category'] ?? null) ?: 'General',
            'due_date' => $validated['due_date'] ?? null,
            'due_time' => $validated['due_time'] ?? null,
            'reference_url' => $referenceUrl,
            'subtasks' => $subtasksData,
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        AuditService::log('create', 'Todo', "Created task #{$todo->id}: {$todo->title}");

        return redirect()->route('todos.index')->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Todo $todo)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['nullable', 'in:todo,in_progress,in_review,completed'],
            'category' => ['nullable', 'string', 'max:100'],
            'due_date' => ['nullable', 'date'],
            'due_time' => ['nullable'],
            'reference_url' => ['nullable', 'string', 'max:1000'],
            'reference_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:10240'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $status = $validated['status'] ?? $todo->status;
        $isCompleted = ($status === 'completed');

        $referenceUrl = $validated['reference_url'] ?? $todo->reference_url;
        if ($request->hasFile('reference_file')) {
            $path = $request->file('reference_file')->store('todos', 'public');
            $referenceUrl = 'todos/'.basename($path);
        }

        $old = $todo->toArray();
        $todo->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'status' => $status,
            'category' => ($validated['category'] ?? null) ?: 'General',
            'due_date' => $validated['due_date'] ?? null,
            'due_time' => $validated['due_time'] ?? null,
            'reference_url' => $referenceUrl,
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? ($todo->completed_at ?? now()) : null,
        ]);

        AuditService::log('update', 'Todo', "Updated task #{$todo->id}", $old, $todo->toArray());

        return back()->with('success', 'Task updated successfully.');
    }

    public function updateStatus(Request $request, Todo $todo)
    {
        $request->validate([
            'status' => ['required', 'in:todo,in_progress,in_review,completed'],
        ]);

        $status = $request->input('status');
        $isCompleted = ($status === 'completed');

        $old = $todo->toArray();
        $todo->update([
            'status' => $status,
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        AuditService::log('update', 'Todo', "Changed task #{$todo->id} status to {$status}", $old, $todo->toArray());

        return back()->with('success', "Task moved to {$status}.");
    }

    public function toggle(Todo $todo)
    {
        $old = $todo->toArray();
        $isCompleted = ! $todo->is_completed;
        $newStatus = $isCompleted ? 'completed' : 'todo';

        $todo->update([
            'is_completed' => $isCompleted,
            'status' => $newStatus,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        $statusText = $isCompleted ? 'completed' : 'marked active';
        AuditService::log('update', 'Todo', "Task #{$todo->id} {$statusText}", $old, $todo->toArray());

        return back()->with('success', "Task {$statusText}.");
    }

    public function toggleSubtask(Request $request, Todo $todo)
    {
        $request->validate([
            'index' => ['required', 'integer'],
        ]);

        $index = $request->input('index');
        $subtasks = $todo->subtasks ?? [];

        if (isset($subtasks[$index])) {
            $subtasks[$index]['completed'] = ! ($subtasks[$index]['completed'] ?? false);
            $todo->update(['subtasks' => $subtasks]);
        }

        return back()->with('success', 'Subtask updated.');
    }

    public function convertToWorkEntry(Request $request, Todo $todo)
    {
        $validated = $request->validate([
            'work_category_id' => ['required', 'exists:work_categories,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        // Find employee associated with assigned user or current user
        $targetUserId = $todo->assigned_to_user_id ?: Auth::id();
        $targetUser = User::find($targetUserId);
        $employee = Employee::where('user_id', $targetUserId)->first()
                 ?? Employee::where('email', $targetUser->email)->first()
                 ?? Employee::first();

        if (! $employee) {
            return back()->with('error', 'No active employee profile linked to this user to log daily work.');
        }

        $workEntry = DailyWorkEntry::create([
            'employee_id' => $employee->id,
            'work_category_id' => $validated['work_category_id'],
            'date' => $validated['date'],
            'quantity' => $validated['quantity'],
            'remarks' => $validated['remarks'] ?: $todo->title,
            'created_by_user_id' => Auth::id(),
        ]);

        $todo->update([
            'is_completed' => true,
            'status' => 'completed',
            'completed_at' => now(),
            'work_entry_id' => $workEntry->id,
        ]);

        AuditService::log('create', 'DailyWorkEntry', "Converted task #{$todo->id} into Daily Work Entry #{$workEntry->id}", null, $workEntry->toArray());

        return back()->with('success', "⚡ Logged {$validated['quantity']} deliverable(s) into Daily Work History!");
    }

    public function destroy(Todo $todo)
    {
        $old = $todo->toArray();
        $title = $todo->title;
        $todo->delete();

        AuditService::log('delete', 'Todo', "Deleted task {$title}", $old);

        return back()->with('success', 'Task removed from Todo list.');
    }
}
