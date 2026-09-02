@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 dark:text-white">Edit Employee: {{ $employee->name }}</h1>
            <p class="text-xs text-slate-500">Update employee information, designation, or employment status.</p>
        </div>
        <a href="{{ route('employees.show', $employee) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
            ← Back to profile
        </a>
    </div>

    <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data" 
          class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-6 text-xs"
          x-data="{ previewUrl: null }">
        @csrf
        @method('PUT')

        <!-- Section 1: Basic Information -->
        <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                1. Personal Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Photo Upload with Preview -->
                <div class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" class="w-24 h-24 rounded-2xl object-cover mb-3 shadow-md">
                    </template>
                    <template x-if="!previewUrl">
                        <img src="{{ $employee->photo_url }}" class="w-24 h-24 rounded-2xl object-cover mb-3 shadow-md">
                    </template>
                    <label class="cursor-pointer px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-bold rounded-xl hover:bg-indigo-100 transition text-[11px]">
                        <span>Change Photo</span>
                        <input type="file" name="photo" accept="image/*" class="hidden" 
                               @change="previewUrl = URL.createObjectURL($event.target.files[0])">
                    </label>
                </div>

                <!-- Name, Code, Email -->
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-500 uppercase mb-1">Employee Code *</label>
                        <input type="text" name="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono font-bold text-indigo-600 dark:text-indigo-400">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase mb-1">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $employee->name) }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase mb-1">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email', $employee->email) }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase mb-1">Mobile Number</label>
                        <input type="text" name="mobile_number" value="{{ old('mobile_number', $employee->mobile_number) }}"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                    </div>
                </div>

            </div>
        </div>

        <!-- Section 2: Job Details -->
        <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                2. Job & Department Information
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Department *</label>
                    <select name="department_id" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Designation *</label>
                    <input type="text" name="designation" value="{{ old('designation', $employee->designation) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Joining Date</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', $employee->joining_date?->format('Y-m-d')) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Employment Status *</label>
                    <select name="employment_status" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold">
                        <option value="active" {{ old('employment_status', $employee->employment_status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('employment_status', $employee->employment_status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="resigned" {{ old('employment_status', $employee->employment_status) === 'resigned' ? 'selected' : '' }}>Resigned</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Monthly Salary (Optional)</label>
                    <input type="number" step="0.01" name="salary" value="{{ old('salary', $employee->salary) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Yearly Leave Quota</label>
                    <input type="number" name="leave_quota" value="{{ old('leave_quota', $employee->leave_quota) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>
            </div>

            <div class="mt-4">
                <label class="block font-bold text-slate-500 uppercase mb-1">Notes / Description</label>
                <textarea name="notes" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl">{{ old('notes', $employee->notes) }}</textarea>
            </div>
        </div>

        <!-- Section 3: Emergency Contact & Banking Details -->
        <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                3. Emergency Contact & Banking (For Salary Deposit)
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Emergency Contact Person</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}" placeholder="e.g. Parent, Spouse name"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Emergency Phone Number</label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}" placeholder="e.g. +91 98765 00000"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}" placeholder="e.g. HDFC Bank, SBI"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Bank Account Number</label>
                    <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $employee->bank_account_no) }}" placeholder="e.g. 50100234567890"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Bank IFSC Code</label>
                    <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc', $employee->bank_ifsc) }}" placeholder="e.g. HDFC0001234"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono uppercase">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">UPI ID</label>
                    <input type="text" name="upi_id" value="{{ old('upi_id', $employee->upi_id) }}" placeholder="e.g. employee@okhdfcbank"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
            <a href="{{ route('employees.show', $employee) }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold rounded-xl hover:bg-slate-200 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-[#0071e3] hover:bg-[#0062c4] text-white font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                Save Changes
            </button>
        </div>

    </form>

</div>
@endsection
