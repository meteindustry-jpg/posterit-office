@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    addModalOpen: false,
    editModalOpen: false,
    editData: { id: null, name: '', description: '', url: '' },
    openEdit(dept) {
        this.editData = {
            id: dept.id,
            name: dept.name,
            description: dept.description || '',
            url: `{{ url('departments') }}/${dept.id}`
        };
        this.editModalOpen = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white tracking-tight">
                Department Management
            </h1>
            <p class="text-xs text-slate-500">
                Organize team members into operational units and departments.
            </p>
        </div>

        <div>
            <button type="button" @click="addModalOpen = true" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0071e3] hover:bg-[#0062c4] text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Add Department</span>
            </button>
        </div>
    </div>

    <!-- Departments Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @foreach($departments as $dept)
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-white font-display">{{ $dept->name }}</h3>
                    <span class="px-2.5 py-1 bg-blue-50 text-[#0071e3] font-bold rounded-full text-xs border border-blue-100">
                        {{ $dept->employees_count }} Members
                    </span>
                </div>

                <p class="text-xs text-slate-500 mt-2 min-h-[36px] line-clamp-2">{{ $dept->description ?? 'No description provided.' }}</p>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('employees.index', ['department_id' => $dept->id]) }}" class="text-xs font-bold text-[#0071e3] hover:underline">
                    View Members →
                </a>

                <div class="flex items-center gap-1">
                    <button type="button" @click="openEdit(@js($dept))" class="p-1.5 rounded-lg text-slate-400 hover:text-[#0071e3] hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>

                    @if($dept->employees_count == 0)
                    <form method="POST" action="{{ route('departments.destroy', $dept) }}" onsubmit="return confirm('Delete department {{ $dept->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Add Modal -->
    <div x-show="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" style="display: none;">
        <div @click.outside="addModalOpen = false" class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-extrabold text-base text-slate-900 dark:text-white font-display">Add Department</h3>
            
            <form method="POST" action="{{ route('departments.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Department Name *</label>
                    <input type="text" name="name" required placeholder="e.g. 3D Animation & VFX" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Description</label>
                    <textarea name="description" rows="3" placeholder="Department responsibilities..." class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-md">Create Department</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" style="display: none;">
        <div @click.outside="editModalOpen = false" class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-extrabold text-base text-slate-900 dark:text-white font-display">Edit Department</h3>
            
            <form method="POST" :action="editData.url" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Department Name *</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Description</label>
                    <textarea name="description" x-model="editData.description" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-md">Update Department</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
