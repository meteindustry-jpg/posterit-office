@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    addModalOpen: false,
    editModalOpen: false,
    editData: { id: null, name: '', description: '', color: '#3B82F6', is_active: true, url: '' },
    openEdit(cat) {
        this.editData = {
            id: cat.id,
            name: cat.name,
            description: cat.description || '',
            color: cat.color || '#3B82F6',
            is_active: Boolean(cat.is_active),
            url: `{{ url('categories') }}/${cat.id}`
        };
        this.editModalOpen = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white tracking-tight">
                Work Categories
            </h1>
            <p class="text-xs text-slate-500">
                Manage customizable design & marketing task categories for daily work entry.
            </p>
        </div>

        <div>
            <button type="button" @click="addModalOpen = true" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0071e3] hover:bg-[#0062c4] text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Add Category</span>
            </button>
        </div>
    </div>

    <!-- Category Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($categories as $cat)
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full shrink-0 shadow-xs" style="background-color: {{ $cat->color }}"></span>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $cat->name }}</h3>
                    </div>

                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $cat->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                        {{ $cat->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <p class="text-xs text-slate-500 mt-2 min-h-[32px] line-clamp-2">{{ $cat->description ?? 'No description' }}</p>

                <div class="mt-3 text-[11px] text-slate-400">
                    Logged tasks: <strong class="text-slate-700 dark:text-slate-300">{{ $cat->work_entries_count }}</strong>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('work-entries.index', ['category_id' => $cat->id]) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">
                    View Logs →
                </a>

                <div class="flex items-center gap-1">
                    <button type="button" @click="openEdit(@js($cat))" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>

                    @if($cat->work_entries_count == 0)
                    <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirm('Delete category {{ $cat->name }}?')">
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
            <h3 class="font-extrabold text-base text-slate-900 dark:text-white font-display">Add Work Category</h3>
            
            <form method="POST" action="{{ route('categories.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. 3D Modeling" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Color Tag *</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" value="#4F46E5" class="w-10 h-10 rounded-xl border border-slate-200 cursor-pointer p-0.5">
                        <span class="text-slate-400">Pick color badge representation</span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief description..." class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active_add" checked class="rounded text-indigo-600">
                    <label for="is_active_add" class="font-semibold text-slate-700 dark:text-slate-300">Active Category</label>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-md">Create Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" style="display: none;">
        <div @click.outside="editModalOpen = false" class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-extrabold text-base text-slate-900 dark:text-white font-display">Edit Work Category</h3>
            
            <form method="POST" :action="editData.url" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Category Name *</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Color Tag *</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" x-model="editData.color" class="w-10 h-10 rounded-xl border border-slate-200 cursor-pointer p-0.5">
                        <span class="text-slate-400">Color swatch</span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Description</label>
                    <textarea name="description" x-model="editData.description" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active_edit" x-model="editData.is_active" class="rounded text-indigo-600">
                    <label for="is_active_edit" class="font-semibold text-slate-700 dark:text-slate-300">Active Category</label>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-md">Update Category</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
