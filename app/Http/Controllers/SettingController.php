<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Services\AuditService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'company_name' => CompanySetting::get('company_name', 'Posterit Digital Studio'),
            'company_tagline' => CompanySetting::get('company_tagline', 'High-Performance Creative Graphic & Media Production Studio'),
            'company_email' => CompanySetting::get('company_email', 'contact@posterit.com'),
            'company_phone' => CompanySetting::get('company_phone', '+91 98765 43210'),
            'company_website' => CompanySetting::get('company_website', 'https://posterit.com'),
            'company_address' => CompanySetting::get('company_address', '402, Creative Hub, Tech Park Road, Mumbai, Maharashtra 400001'),
            'company_tax_id' => CompanySetting::get('company_tax_id', 'GSTIN27ABCDE1234F1Z5'),
            'company_logo' => CompanySetting::get('company_logo'),
            'working_days' => json_decode(CompanySetting::get('working_days', '["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]'), true) ?? [],
            'timezone' => CompanySetting::get('timezone', config('app.timezone', 'Asia/Kolkata')),
            'office_timing_start' => CompanySetting::get('office_timing_start', '09:30'),
            'office_timing_end' => CompanySetting::get('office_timing_end', '18:30'),
            'late_grace_minutes' => (int) CompanySetting::get('late_grace_minutes', 15),
            'half_day_hours' => (float) CompanySetting::get('half_day_hours', 4.5),
            'default_leave_count' => CompanySetting::get('default_leave_count', '18'),
            'currency_symbol' => CompanySetting::get('currency_symbol', '₹'),
            'theme_mode' => CompanySetting::get('theme_mode', 'light'),
            'payslip_footer_note' => CompanySetting::get('payslip_footer_note', 'This is a system-generated salary slip and does not require a physical signature.'),
            'attendance_reminder_enabled' => (bool) CompanySetting::get('attendance_reminder_enabled', true),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_tax_id' => ['nullable', 'string', 'max:100'],
            'company_logo' => ['nullable', 'image', 'max:2048'],
            'working_days' => ['required', 'array'],
            'timezone' => ['nullable', 'string'],
            'office_timing_start' => ['required', 'string'],
            'office_timing_end' => ['required', 'string'],
            'late_grace_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'half_day_hours' => ['required', 'numeric', 'min:1', 'max:12'],
            'default_leave_count' => ['required', 'integer', 'min:0'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'theme_mode' => ['required', 'in:light,dark,system'],
            'payslip_footer_note' => ['nullable', 'string', 'max:500'],
            'attendance_reminder_enabled' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('settings', 'public');
            CompanySetting::set('company_logo', $path);
        }

        if (! empty($validated['timezone'])) {
            CompanySetting::set('timezone', $validated['timezone']);
            date_default_timezone_set($validated['timezone']);
            config(['app.timezone' => $validated['timezone']]);
        }

        CompanySetting::set('company_name', $validated['company_name']);
        CompanySetting::set('company_tagline', $validated['company_tagline'] ?? '');
        CompanySetting::set('company_email', $validated['company_email'] ?? '');
        CompanySetting::set('company_phone', $validated['company_phone'] ?? '');
        CompanySetting::set('company_website', $validated['company_website'] ?? '');
        CompanySetting::set('company_address', $validated['company_address'] ?? '');
        CompanySetting::set('company_tax_id', $validated['company_tax_id'] ?? '');
        CompanySetting::set('working_days', json_encode($validated['working_days']));
        CompanySetting::set('office_timing_start', $validated['office_timing_start']);
        CompanySetting::set('office_timing_end', $validated['office_timing_end']);
        CompanySetting::set('late_grace_minutes', (string) $validated['late_grace_minutes']);
        CompanySetting::set('half_day_hours', (string) $validated['half_day_hours']);
        CompanySetting::set('default_leave_count', (string) $validated['default_leave_count']);
        CompanySetting::set('currency_symbol', $validated['currency_symbol']);
        CompanySetting::set('theme_mode', $validated['theme_mode']);
        CompanySetting::set('payslip_footer_note', $validated['payslip_footer_note'] ?? '');
        CompanySetting::set('attendance_reminder_enabled', $request->has('attendance_reminder_enabled') ? '1' : '0');

        AuditService::log('update', 'Settings', 'Updated company system settings.');

        return back()->with('success', 'System settings updated successfully.');
    }
}
