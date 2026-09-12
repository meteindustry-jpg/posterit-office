@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="batchWorkForm()">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-[10px] font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-300 dark:border-amber-700/50 rounded-full uppercase tracking-wider">⚡ Fast Batch</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                Daily Work Entry Batch
            </h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                Log completed design tasks, deliverables, and revisions for the team in under 5 minutes.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('work-entries.index') }}" class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-700 shadow-2xs transition">
                Work History →
            </a>
        </div>
    </div>

    <!-- Date selector & Quick Bar -->
    <div class="p-4 rounded-3xl glass-panel flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('work-entries.batch') }}" class="flex items-center gap-3 text-xs">
            <label class="font-bold text-slate-700 dark:text-slate-300 uppercase text-[11px] tracking-wider">Work Date</label>
            <input type="date" name="date" x-model="date" onchange="this.form.submit()" 
                   class="px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-[#0071e3]">
        </form>

        <div class="flex items-center gap-4 text-xs font-semibold">
            <div class="text-slate-700 dark:text-slate-300">
                Total Rows: <strong class="text-slate-900 dark:text-white font-extrabold" x-text="rows.length"></strong>
            </div>
            <div class="text-slate-700 dark:text-slate-300">
                Total Deliverables: <strong class="text-[#0071e3] dark:text-blue-400 font-black text-sm" x-text="getTotalQuantity()"></strong>
            </div>
            <button type="button" @click="addRow()" class="px-3.5 py-2 bg-blue-50 dark:bg-blue-900/30 text-[#0071e3] dark:text-blue-400 border border-blue-200 dark:border-blue-700/50 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-2xl text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                <span>+ Extra Task Row</span>
            </button>
        </div>
    </div>

    @if($existingEntries->count() > 0)
    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 text-xs text-amber-900 dark:text-amber-300 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2 font-medium">
            <span>ℹ️</span>
            <span><strong class="font-bold">{{ $existingEntries->count() }}</strong> entries already recorded for {{ $date }} ({{ $existingEntries->sum('quantity') }} deliverables).</span>
        </div>
        <a href="{{ route('work-entries.index', ['date' => $date]) }}" class="font-bold text-[#0071e3] dark:text-blue-400 hover:underline">
            View Existing →
        </a>
    </div>
    @endif

    <!-- Batch Work Entry Table -->
    <form method="POST" action="{{ route('work-entries.batchStore') }}">
        @csrf
        <input type="hidden" name="date" :value="date">

        <div class="rounded-3xl glass-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider">
                            <th class="py-3.5 px-4 w-12 text-center">#</th>
                            <th class="py-3.5 px-4 min-w-[220px]">Employee</th>
                            <th class="py-3.5 px-4 min-w-[220px]">Work Category</th>
                            <th class="py-3.5 px-4 w-32 text-center">Quantity</th>
                            <th class="py-3.5 px-4 min-w-[240px]">Deliverables & Remarks</th>
                            <th class="py-3.5 px-4 w-20 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="(row, index) in rows" :key="index">
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                <td class="py-3 px-4 text-center font-bold text-slate-600 dark:text-slate-400" x-text="index + 1"></td>
                                
                                <td class="py-3 px-4">
                                    <select :name="`entries[${index}][employee_id]`" 
                                            x-model="row.employee_id" 
                                            required
                                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-[#0071e3]">
                                        <template x-for="emp in employees" :key="emp.id">
                                            <option :value="emp.id" x-text="`${emp.name} (${emp.employee_code}) - ${emp.department ? emp.department.name : 'Dept'}`"></option>
                                        </template>
                                    </select>
                                </td>

                                <td class="py-3 px-4">
                                    <select :name="`entries[${index}][work_category_id]`" 
                                            x-model="row.work_category_id" 
                                            required
                                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-[#0071e3]">
                                        <template x-for="cat in categories" :key="cat.id">
                                            <option :value="cat.id" x-text="cat.name"></option>
                                        </template>
                                    </select>
                                </td>

                                <td class="py-3 px-4">
                                    <input type="number" 
                                           :name="`entries[${index}][quantity]`" 
                                           x-model.number="row.quantity" 
                                           min="1" 
                                           required
                                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-black text-center focus:ring-2 focus:ring-[#0071e3] text-[#0071e3] dark:text-blue-400">
                                </td>

                                <td class="py-3 px-4">
                                    <input type="text" 
                                           :name="`entries[${index}][remarks]`" 
                                           x-model="row.remarks" 
                                           placeholder="e.g. Campaign banner, Reel edit, PSD batch #12..."
                                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-[#0071e3] placeholder-slate-500 text-slate-800 dark:text-white font-medium">
                                </td>

                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" 
                                                @click="addRow(row.employee_id, row.work_category_id)" 
                                                title="Duplicate row"
                                                class="p-1.5 rounded-lg text-[#0071e3] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        </button>
                                        <button type="button" 
                                                @click="removeRow(index)" 
                                                title="Remove row"
                                                class="p-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-[#ff3b30] hover:bg-rose-50 dark:hover:bg-rose-900/30 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Footer Action Bar -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <button type="button" @click="addRow()" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-[#0071e3] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Add Task Row</span>
                </button>

                <div class="flex items-center gap-3">
                    <button type="submit" class="px-6 py-2.5 bg-[#0071e3] hover:bg-[#0077ed] text-white font-bold text-xs rounded-2xl shadow-[0_4px_16px_rgba(0,113,227,0.35)] transition active:scale-[0.98] flex items-center gap-2 cursor-pointer">
                        <span>Save All Work Entries</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
    function batchWorkForm() {
        const empList = @json($employees);
        const catList = @json($categories);
        const defaultCatId = catList.length > 0 ? catList[0].id : '';

        const initialRows = empList.map(emp => ({
            employee_id: emp.id,
            work_category_id: defaultCatId,
            quantity: 1,
            remarks: ''
        }));

        if (initialRows.length === 0) {
            initialRows.push({
                employee_id: '',
                work_category_id: defaultCatId,
                quantity: 1,
                remarks: ''
            });
        }

        return {
            date: '{{ $date }}',
            employees: empList,
            categories: catList,
            rows: initialRows,
            addRow(employeeId = null, categoryId = null) {
                this.rows.push({
                    employee_id: employeeId || (this.employees.length > 0 ? this.employees[0].id : ''),
                    work_category_id: categoryId || (this.categories.length > 0 ? this.categories[0].id : defaultCatId),
                    quantity: 1,
                    remarks: ''
                });
            },
            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },
            getTotalQuantity() {
                return this.rows.reduce((acc, row) => acc + (parseInt(row.quantity) || 0), 0);
            }
        };
    }
</script>
@endpush
@endsection
