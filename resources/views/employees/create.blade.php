@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white">Add New Employee</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 font-medium mt-0.5">Register employee details, department, salary, and initial leave quota.</p>
        </div>
        <a href="{{ route('employees.index') }}" class="text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white">
            ← Back to directory
        </a>
    </div>

    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" 
          class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-6 text-xs"
          x-data="{ createUser: true, previewUrl: null }">
        @csrf

        <!-- Section 1: Basic Information -->
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                1. Personal Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Photo Upload with Preview -->
                <div class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl bg-slate-50/50 dark:bg-slate-800/40">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" class="w-24 h-24 rounded-2xl object-cover mb-3 shadow-md ring-1 ring-black/10">
                    </template>
                    <template x-if="!previewUrl">
                        <div class="w-24 h-24 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3 border border-slate-200 dark:border-slate-700">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    </template>
                    <label class="cursor-pointer px-3 py-1.5 bg-blue-50 dark:bg-blue-950/40 text-[#0071e3] font-bold rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/60 transition text-[11px] border border-blue-200 dark:border-blue-800">
                        <span>Upload Photo</span>
                        <input type="file" name="photo" accept="image/*" class="hidden" 
                                @change="previewUrl = URL.createObjectURL($event.target.files[0])">
                    </label>
                </div>

                <!-- Name, Code, Email -->
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Employee Code *</label>
                        <input type="text" name="employee_code" value="{{ old('employee_code', $nextCode) }}" required
                                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono font-bold text-[#0071e3]">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. John Doe"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. john@posterit.com"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white placeholder-slate-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Mobile Number</label>
                        <input type="text" name="mobile_number" value="{{ old('mobile_number') }}" placeholder="e.g. +91 98765 43210"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white placeholder-slate-500">
                    </div>
                </div>

            </div>
        </div>

        <!-- Section 2: Job Details -->
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                2. Job & Department Information
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Department *</label>
                    <select name="department_id" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-900 dark:text-white">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Designation *</label>
                    <input type="text" name="designation" value="{{ old('designation') }}" required placeholder="e.g. Senior Graphic Designer"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white placeholder-slate-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Joining Date</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', now()->format('Y-m-d')) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Employment Status *</label>
                    <select name="employment_status" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-900 dark:text-white">
                        <option value="active" {{ old('employment_status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('employment_status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="resigned" {{ old('employment_status') === 'resigned' ? 'selected' : '' }}>Resigned</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Monthly Salary (Optional)</label>
                    <input type="number" step="0.01" name="salary" value="{{ old('salary') }}" placeholder="e.g. 45000"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white placeholder-slate-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Yearly Leave Quota</label>
                    <input type="number" name="leave_quota" value="{{ old('leave_quota', 18) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="mt-4">
                <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Notes / Description</label>
                <textarea name="notes" rows="2" placeholder="Optional background notes..." class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white placeholder-slate-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Section 3: Emergency Contact & Banking Details -->
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                3. Emergency Contact & Banking (For Salary Deposit)
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Emergency Contact Person</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" placeholder="e.g. Parent, Spouse name"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white placeholder-slate-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Emergency Phone Number</label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" placeholder="e.g. +91 98765 00000"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white placeholder-slate-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="e.g. HDFC Bank, SBI"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white placeholder-slate-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Bank Account Number</label>
                    <input type="text" name="bank_account_no" value="{{ old('bank_account_no') }}" placeholder="e.g. 50100234567890"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono text-slate-900 dark:text-white placeholder-slate-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Bank IFSC Code</label>
                    <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc') }}" placeholder="e.g. HDFC0001234"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono uppercase text-slate-900 dark:text-white placeholder-slate-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">UPI ID</label>
                    <input type="text" name="upi_id" value="{{ old('upi_id') }}" placeholder="e.g. employee@okhdfcbank"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium text-slate-900 dark:text-white placeholder-slate-500">
                </div>
            </div>
        </div>

        <!-- Section 4: User Account Creation -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-900 dark:text-white">
                <input type="checkbox" name="create_user_account" value="1" x-model="createUser" class="rounded text-[#0071e3] focus:ring-[#0071e3]">
                <span>Create Portal Login Account for this Employee</span>
            </label>

            <div x-show="createUser" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-[11px] tracking-wider">Initial Password</label>
                    <input type="password" name="password" value="password" 
                           class="w-full px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-mono text-slate-900 dark:text-white">
                    <span class="text-[10px] text-slate-600 dark:text-slate-400 font-medium mt-1 block">Default: password (can be changed anytime)</span>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
            <a href="{{ route('employees.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-[#0071e3] hover:bg-[#0062c4] text-white font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                Save & Create Employee
            </button>
        </div>

    </form>

</div>
@endsection
