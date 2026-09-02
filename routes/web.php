<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyWorkEntryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkCategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Posterit Employee Work Management System
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Common Profile & Security
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Dashboard (All Roles with role-specific views)
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search AJAX API
    Route::get('/api/search', [GlobalSearchController::class, 'search'])->name('api.search');

    // Leave System (Common view & apply)
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/create', [LeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');

    // Work History (All Roles - Employees view their own history)
    Route::get('/work-entries', [DailyWorkEntryController::class, 'index'])->name('work-entries.index');

    // Attendance Views & Exports (All authenticated roles)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/batch', [AttendanceController::class, 'storeBatch'])->name('attendance.storeBatch');
    Route::get('/attendance/monthly-grid', [AttendanceController::class, 'monthlyGrid'])->name('attendance.monthlyGrid');
    Route::get('/attendance/export-monthly', [AttendanceController::class, 'exportMonthly'])->name('attendance.exportMonthly');

    // Employee Self Attendance Clock-In / Clock-Out (All Roles)
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');

    // Holidays (All can view calendar)
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');

    // Todo / Task Management (All roles)
    Route::get('/todos', [\App\Http\Controllers\TodoController::class, 'index'])->name('todos.index');
    Route::post('/todos', [\App\Http\Controllers\TodoController::class, 'store'])->name('todos.store');
    Route::put('/todos/{todo}', [\App\Http\Controllers\TodoController::class, 'update'])->name('todos.update');
    Route::patch('/todos/{todo}/toggle', [\App\Http\Controllers\TodoController::class, 'toggle'])->name('todos.toggle');
    Route::patch('/todos/{todo}/status', [\App\Http\Controllers\TodoController::class, 'updateStatus'])->name('todos.updateStatus');
    Route::patch('/todos/{todo}/subtasks/toggle', [\App\Http\Controllers\TodoController::class, 'toggleSubtask'])->name('todos.toggleSubtask');
    Route::post('/todos/{todo}/convert-work-entry', [\App\Http\Controllers\TodoController::class, 'convertToWorkEntry'])->name('todos.convertToWorkEntry');
    Route::delete('/todos/{todo}', [\App\Http\Controllers\TodoController::class, 'destroy'])->name('todos.destroy');

    // ----------------------------------------------------
    // Manager, Admin, Super Admin Level
    // ----------------------------------------------------
    Route::middleware('role:super_admin,admin,manager')->group(function () {
        // Daily Work Entries Batch & CRUD (Manager & Admin Oversight)
        Route::get('/work-entries/batch', [DailyWorkEntryController::class, 'batchCreate'])->name('work-entries.batch');
        Route::post('/work-entries/batch', [DailyWorkEntryController::class, 'batchStore'])->name('work-entries.batchStore');
        Route::get('/work-entries/{workEntry}/edit', [DailyWorkEntryController::class, 'edit'])->name('work-entries.edit');
        Route::put('/work-entries/{workEntry}', [DailyWorkEntryController::class, 'update'])->name('work-entries.update');
        Route::delete('/work-entries/{workEntry}', [DailyWorkEntryController::class, 'destroy'])->name('work-entries.destroy');

        // Leave Approval
        Route::patch('/leaves/{leave}/status', [LeaveController::class, 'updateStatus'])->name('leaves.updateStatus');

        // Performance Scorecard
        Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');

        // Reports & Exports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/{format?}', [ReportController::class, 'export'])->name('reports.export');

        // Payroll Management Actions (Manager & Admin Only)
        Route::post('/payroll/generate', [\App\Http\Controllers\PayrollController::class, 'generate'])->name('payroll.generate');
        Route::put('/payroll/payslips/{payslip}', [\App\Http\Controllers\PayrollController::class, 'updatePayslip'])->name('payroll.updatePayslip');
        Route::post('/payroll/runs/{payrollRun}/mark-paid', [\App\Http\Controllers\PayrollController::class, 'bulkMarkPaid'])->name('payroll.bulkMarkPaid');
        Route::get('/payroll/runs/{payrollRun}/export-bank-csv', [\App\Http\Controllers\PayrollController::class, 'exportBankCsv'])->name('payroll.exportBankCsv');
    });

    // Payroll Index & Employee Payslip Routes
    Route::get('/payroll', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/my-payslips', [\App\Http\Controllers\PayrollController::class, 'myPayslips'])->name('payroll.myPayslips');
    Route::get('/payroll/payslips/{payslip}', [\App\Http\Controllers\PayrollController::class, 'showPayslip'])->name('payroll.showPayslip');

    // ----------------------------------------------------
    // Admin & Super Admin Level
    // ----------------------------------------------------
    Route::middleware('role:super_admin,admin')->group(function () {
        // Employee Management
        Route::resource('employees', EmployeeController::class);

        // Work Categories
        Route::resource('categories', WorkCategoryController::class)->except(['create', 'show', 'edit']);

        // Departments
        Route::resource('departments', DepartmentController::class)->except(['create', 'show', 'edit']);

        // Holidays Management
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])->name('holidays.update');
        Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
    });

    // ----------------------------------------------------
    // Super Admin Level
    // ----------------------------------------------------
    Route::middleware('role:super_admin')->group(function () {
        // Users & Roles
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);

        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // Company Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
