@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white">Edit Work Entry #{{ $workEntry->id }}</h1>
            <p class="text-xs text-slate-500">Update quantity, category, or task remarks.</p>
        </div>
        <a href="{{ route('work-entries.index', ['date' => $workEntry->date->format('Y-m-d')]) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
            ← Back to logs
        </a>
    </div>

    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <form method="POST" action="{{ route('work-entries.update', $workEntry) }}" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1.5">Date</label>
                <input type="date" name="date" value="{{ old('date', $workEntry->date->format('Y-m-d')) }}" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1.5">Employee</label>
                <select name="employee_id" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $workEntry->employee_id == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->employee_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1.5">Work Category</label>
                <select name="work_category_id" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $workEntry->work_category_id == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1.5">Quantity Completed</label>
                <input type="number" name="quantity" min="1" value="{{ old('quantity', $workEntry->quantity) }}" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-[#0071e3] text-sm">
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1.5">Remarks</label>
                <textarea name="remarks" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl">{{ old('remarks', $workEntry->remarks) }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('work-entries.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-semibold hover:bg-slate-200 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-[#0071e3] hover:bg-[#0062c4] text-white font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                    Update Work Entry
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
