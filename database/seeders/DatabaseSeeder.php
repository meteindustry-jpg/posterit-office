<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Todo;
use App\Models\User;
use App\Models\WorkCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Company Information (Real Production Defaults)
        $settings = [
            'company_name' => 'Posterit Office',
            'company_tagline' => 'Creative Work & Studio Management',
            'company_email' => 'samir@posterit.com',
            'company_phone' => '+91 98765 43210',
            'company_address' => 'Posterit Studio, Mumbai, Maharashtra, India',
            'work_hours_per_day' => '8',
            'monthly_working_days' => '26',
            'fiscal_year_start' => '04-01',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'currency_symbol' => '₹',
        ];

        foreach ($settings as $key => $val) {
            CompanySetting::set($key, $val);
        }

        // 2. Standard Industry Departments
        $departments = [
            ['name' => 'Graphics & Design', 'description' => 'Social media creatives, banners, thumbnails, branding'],
            ['name' => 'Video Production', 'description' => 'Reels, video editing, motion graphics, YouTube videos'],
            ['name' => 'Digital Marketing', 'description' => 'SEO, social media campaigns, content strategy'],
            ['name' => 'Web Development', 'description' => 'Front-end, UI/UX, website maintenance, landing pages'],
            ['name' => 'Accounts & Operations', 'description' => 'Billing, client invoicing, payroll, studio management'],
        ];

        foreach ($departments as $d) {
            Department::firstOrCreate(['name' => $d['name']], $d);
        }

        // 3. Standard Work Categories
        $categories = [
            ['name' => 'Social Media Design', 'color' => '#0071E3', 'description' => 'Instagram, Facebook, LinkedIn single creatives'],
            ['name' => 'Carousel Design', 'color' => '#5856D6', 'description' => 'Multi-slide educational or promotional carousels'],
            ['name' => 'Thumbnail', 'color' => '#FF9500', 'description' => 'High-CTR YouTube and video thumbnails'],
            ['name' => 'Banner Design', 'color' => '#34C759', 'description' => 'Web banners, display ads, hero banners'],
            ['name' => 'Print / Poster', 'color' => '#AF52DE', 'description' => 'Print media, hoardings, standees, flyers'],
            ['name' => 'Branding / Identity', 'color' => '#FF2D55', 'description' => 'Logos, brand guidelines, stationery sets'],
            ['name' => 'Illustration', 'color' => '#F43F5E', 'description' => 'Custom creative digital artwork'],
            ['name' => 'Photo Editing', 'color' => '#84CC16', 'description' => 'Color grading, retouching, background removal'],
            ['name' => 'Video Editing', 'color' => '#EF4444', 'description' => 'Video cutting, color grading, audio sync'],
            ['name' => 'Motion Graphics', 'color' => '#A855F7', 'description' => 'After Effects animations, intro/outro stings'],
            ['name' => 'SEO & Optimization', 'color' => '#D97706', 'description' => 'Keyword research, on-page optimization, backlink tasks'],
            ['name' => 'Website Update', 'color' => '#0284C7', 'description' => 'Content and asset updates on web pages'],
        ];

        foreach ($categories as $c) {
            WorkCategory::firstOrCreate(['name' => $c['name']], $c);
        }

        // 4. Standard Leave Types
        $leaveTypes = [
            ['name' => 'Casual Leave', 'default_days_per_year' => 6, 'is_paid' => true],
            ['name' => 'Sick Leave', 'default_days_per_year' => 6, 'is_paid' => true],
            ['name' => 'Paid Leave', 'default_days_per_year' => 6, 'is_paid' => true],
            ['name' => 'Unpaid Leave', 'default_days_per_year' => 10, 'is_paid' => false],
        ];

        foreach ($leaveTypes as $lt) {
            LeaveType::firstOrCreate(['name' => $lt['name']], $lt);
        }

        // 5. Official Holidays
        $currentYear = now()->year;
        $holidays = [
            ['name' => 'New Year Day', 'date' => "{$currentYear}-01-01", 'type' => 'national', 'description' => 'Global holiday'],
            ['name' => 'Republic Day', 'date' => "{$currentYear}-01-26", 'type' => 'national', 'description' => 'National holiday'],
            ['name' => 'Labor Day', 'date' => "{$currentYear}-05-01", 'type' => 'national', 'description' => 'International Workers Day'],
            ['name' => 'Independence Day', 'date' => "{$currentYear}-08-15", 'type' => 'national', 'description' => 'National Independence celebration'],
            ['name' => 'Gandhi Jayanti', 'date' => "{$currentYear}-10-02", 'type' => 'national', 'description' => 'Mahatma Gandhi Birthday'],
            ['name' => 'Diwali', 'date' => "{$currentYear}-11-01", 'type' => 'religious', 'description' => 'Festival of Lights'],
            ['name' => 'Christmas', 'date' => "{$currentYear}-12-25", 'type' => 'national', 'description' => 'Christmas Day'],
        ];

        foreach ($holidays as $h) {
            Holiday::firstOrCreate(['name' => $h['name'], 'date' => $h['date']], $h);
        }

        // 6. Super Admin Account (Samir Mete)
        $samirMete = User::firstOrCreate(
            ['email' => 'samir@posterit.com'],
            [
                'name' => 'Samir Mete',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'sam@posterit.com'],
            [
                'name' => 'Sam Mete',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // 7. Testing Environment Fixtures (ONLY seeded during PHPUnit test suite)
        if (app()->environment('testing')) {
            $dept = Department::first();
            $manager = User::firstOrCreate(
                ['email' => 'manager@posterit.com'],
                ['name' => 'Vikas Deshmukh', 'password' => Hash::make('password'), 'role' => 'manager', 'is_active' => true]
            );

            $admin = User::firstOrCreate(
                ['email' => 'admin@posterit.com'],
                ['name' => 'Admin Posterit', 'password' => Hash::make('password'), 'role' => 'admin', 'is_active' => true]
            );

            $employeesData = [
                ['code' => 'EMP-001', 'name' => 'Rahul Sharma', 'email' => 'rahul@posterit.com', 'mobile' => '+91 98234 11223', 'designation' => 'Senior Graphic Designer'],
                ['code' => 'EMP-002', 'name' => 'Priya Patel', 'email' => 'priya@posterit.com', 'mobile' => '+91 98234 22334', 'designation' => 'Motion Graphic Artist'],
            ];

            foreach ($employeesData as $ed) {
                $u = User::firstOrCreate(
                    ['email' => $ed['email']],
                    ['name' => $ed['name'], 'password' => Hash::make('password'), 'role' => 'employee', 'is_active' => true]
                );

                $e = Employee::firstOrCreate(
                    ['email' => $ed['email']],
                    [
                        'employee_code' => $ed['code'],
                        'user_id' => $u->id,
                        'name' => $ed['name'],
                        'mobile_number' => $ed['mobile'],
                        'designation' => $ed['designation'],
                        'department_id' => $dept ? $dept->id : 1,
                        'joining_date' => '2024-01-01',
                        'employment_status' => 'active',
                        'salary' => 40000,
                        'leave_quota' => 18,
                    ]
                );
                $u->update(['employee_id' => $e->id]);
            }

            $firstEmp = Employee::first();
            $leaveType = LeaveType::first();
            if ($firstEmp && $leaveType) {
                LeaveRequest::create([
                    'employee_id' => $firstEmp->id,
                    'leave_type_id' => $leaveType->id,
                    'start_date' => now()->addDays(5)->format('Y-m-d'),
                    'end_date' => now()->addDays(6)->format('Y-m-d'),
                    'total_days' => 2,
                    'reason' => 'Test leave request',
                    'status' => 'pending',
                ]);
            }

            Todo::create([
                'user_id' => $manager->id,
                'title' => 'Test Initial Task',
                'description' => 'Test task description',
                'priority' => 'medium',
                'status' => 'todo',
                'category' => 'General',
                'is_completed' => false,
            ]);
        }

        // 8. Initial Production Audit Log
        AuditLog::create([
            'user_id' => $samirMete->id,
            'action' => 'launch',
            'module' => 'System',
            'description' => 'System initialized for live production. Ready for real studio usage.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Production System Initializer',
        ]);
    }
}
