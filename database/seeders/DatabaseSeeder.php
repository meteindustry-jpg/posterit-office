<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\CompanySetting;
use App\Models\DailyAttendance;
use App\Models\DailyWorkEntry;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\WorkCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Company Settings
        $settings = [
            'company_name' => 'Posterit Office',
            'company_tagline' => 'High-Performance Creative Graphic & Media Production Studio',
            'company_email' => 'contact@posterit.com',
            'company_phone' => '+91 98765 43210',
            'company_address' => '402, Creative Hub, Tech Park Road, Mumbai, Maharashtra 400001',
            'working_days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']),
            'office_timing_start' => '09:30',
            'office_timing_end' => '18:30',
            'default_leave_count' => '18',
            'currency_symbol' => '₹',
            'theme_mode' => 'light',
        ];

        foreach ($settings as $k => $v) {
            CompanySetting::set($k, $v);
        }

        // 2. Departments
        $departments = [
            ['name' => 'Graphics & Design', 'description' => 'Creative poster, banner, and UI visual assets'],
            ['name' => 'Video Production', 'description' => 'Video editing, reels, motion graphics, and rendering'],
            ['name' => 'Digital Marketing', 'description' => 'SEO, social media campaigns, content strategy'],
            ['name' => 'Web Development', 'description' => 'Website maintenance, landing pages, and frontend updates'],
            ['name' => 'Quality Assurance', 'description' => 'Design audit and quality check of client deliverables'],
        ];

        $deptModels = [];
        foreach ($departments as $d) {
            $deptModels[$d['name']] = Department::create($d);
        }

        // 3. Work Categories
        $categories = [
            ['name' => 'PSD Design', 'color' => '#3B82F6', 'description' => 'Photoshop graphic designs and templates'],
            ['name' => 'Social Media Design', 'color' => '#EC4899', 'description' => 'Instagram, Facebook, LinkedIn post artwork'],
            ['name' => 'Banner Design', 'color' => '#8B5CF6', 'description' => 'Website & marketing display banners'],
            ['name' => 'Thumbnail', 'color' => '#F59E0B', 'description' => 'YouTube & video promotional thumbnails'],
            ['name' => 'Logo', 'color' => '#10B981', 'description' => 'Brand logos & vector marks'],
            ['name' => 'Flyer', 'color' => '#6366F1', 'description' => 'Event and business promotional flyers'],
            ['name' => 'Brochure', 'color' => '#14B8A6', 'description' => 'Multi-page bifold/trifold brochures'],
            ['name' => 'Vector', 'color' => '#06B6D4', 'description' => 'Vector trace, icons, and illustrations'],
            ['name' => 'Illustration', 'color' => '#F43F5E', 'description' => 'Custom creative digital artwork'],
            ['name' => 'Photo Editing', 'color' => '#84CC16', 'description' => 'Color grading, retouching, background removal'],
            ['name' => 'Image Upload', 'color' => '#64748B', 'description' => 'Cataloging and server uploads'],
            ['name' => 'SEO', 'color' => '#D97706', 'description' => 'Keyword research, on-page optimization, backlink tasks'],
            ['name' => 'Website Update', 'color' => '#0284C7', 'description' => 'Content and asset updates on web pages'],
            ['name' => 'Video Editing', 'color' => '#EF4444', 'description' => 'Video cutting, color grading, audio sync'],
            ['name' => 'Motion Graphics', 'color' => '#A855F7', 'description' => 'After Effects animations, intro/outro stings'],
        ];

        $catModels = [];
        foreach ($categories as $c) {
            $catModels[] = WorkCategory::create($c);
        }

        // 4. Leave Types
        $leaveTypes = [
            ['name' => 'Casual Leave', 'default_days_per_year' => 6, 'is_paid' => true],
            ['name' => 'Sick Leave', 'default_days_per_year' => 6, 'is_paid' => true],
            ['name' => 'Paid Leave', 'default_days_per_year' => 6, 'is_paid' => true],
            ['name' => 'Unpaid Leave', 'default_days_per_year' => 10, 'is_paid' => false],
        ];

        $leaveTypeModels = [];
        foreach ($leaveTypes as $lt) {
            $leaveTypeModels[] = LeaveType::create($lt);
        }

        // 5. Holidays (Current Year)
        $currentYear = now()->year;
        $holidays = [
            ['name' => 'New Year Day', 'date' => "{$currentYear}-01-01", 'type' => 'national', 'description' => 'Global holiday'],
            ['name' => 'Republic Day', 'date' => "{$currentYear}-01-26", 'type' => 'national', 'description' => 'National holiday'],
            ['name' => 'Holi Festival', 'date' => "{$currentYear}-03-25", 'type' => 'religious', 'description' => 'Festival of colors'],
            ['name' => 'Labor Day / Maharashtra Day', 'date' => "{$currentYear}-05-01", 'type' => 'national', 'description' => 'Workers day'],
            ['name' => 'Independence Day', 'date' => "{$currentYear}-08-15", 'type' => 'national', 'description' => 'National independence celebration'],
            ['name' => 'Ganesh Chaturthi', 'date' => "{$currentYear}-09-07", 'type' => 'religious', 'description' => 'State festival'],
            ['name' => 'Gandhi Jayanti', 'date' => "{$currentYear}-10-02", 'type' => 'national', 'description' => 'Mahatma Gandhi birthday'],
            ['name' => 'Diwali Laxmi Pujan', 'date' => "{$currentYear}-11-01", 'type' => 'religious', 'description' => 'Festival of lights'],
            ['name' => 'Christmas', 'date' => "{$currentYear}-12-25", 'type' => 'national', 'description' => 'Christmas Day'],
        ];

        foreach ($holidays as $h) {
            Holiday::create($h);
        }

        // 6. Users & Employees
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@posterit.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $admin = User::create([
            'name' => 'Admin Posterit',
            'email' => 'admin@posterit.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $manager = User::create([
            'name' => 'Vikas Deshmukh',
            'email' => 'manager@posterit.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'is_active' => true,
        ]);

        $employeesData = [
            [
                'code' => 'EMP-001',
                'name' => 'Rahul Sharma',
                'email' => 'rahul@posterit.com',
                'mobile' => '+91 98234 11223',
                'designation' => 'Senior Graphic Designer',
                'dept' => 'Graphics & Design',
                'salary' => 45000.00,
                'joining' => '2023-03-15',
                'quota' => 18,
            ],
            [
                'code' => 'EMP-002',
                'name' => 'Priya Patel',
                'email' => 'priya@posterit.com',
                'mobile' => '+91 98234 22334',
                'designation' => 'Motion Graphic Artist',
                'dept' => 'Video Production',
                'salary' => 48000.00,
                'joining' => '2023-06-01',
                'quota' => 18,
            ],
            [
                'code' => 'EMP-003',
                'name' => 'Amit Verma',
                'email' => 'amit@posterit.com',
                'mobile' => '+91 98234 33445',
                'designation' => 'Social Media Designer',
                'dept' => 'Graphics & Design',
                'salary' => 35000.00,
                'joining' => '2024-01-10',
                'quota' => 18,
            ],
            [
                'code' => 'EMP-004',
                'name' => 'Sneha Kulkarni',
                'email' => 'sneha@posterit.com',
                'mobile' => '+91 98234 44556',
                'designation' => 'SEO & Content Specialist',
                'dept' => 'Digital Marketing',
                'salary' => 38000.00,
                'joining' => '2023-11-20',
                'quota' => 18,
            ],
            [
                'code' => 'EMP-005',
                'name' => 'Vikram Singh',
                'email' => 'vikram@posterit.com',
                'mobile' => '+91 98234 55667',
                'designation' => 'Video Editor & Colorist',
                'dept' => 'Video Production',
                'salary' => 42000.00,
                'joining' => '2024-02-01',
                'quota' => 18,
            ],
            [
                'code' => 'EMP-006',
                'name' => 'Neha Joshi',
                'email' => 'neha@posterit.com',
                'mobile' => '+91 98234 66778',
                'designation' => 'UI/Web Graphic Designer',
                'dept' => 'Web Development',
                'salary' => 40000.00,
                'joining' => '2024-04-15',
                'quota' => 18,
            ],
        ];

        $employeeModels = [];

        foreach ($employeesData as $empData) {
            $user = User::create([
                'name' => $empData['name'],
                'email' => $empData['email'],
                'password' => Hash::make('password'),
                'role' => 'employee',
                'is_active' => true,
            ]);

            $emp = Employee::create([
                'employee_code' => $empData['code'],
                'user_id' => $user->id,
                'name' => $empData['name'],
                'mobile_number' => $empData['mobile'],
                'email' => $empData['email'],
                'designation' => $empData['designation'],
                'department_id' => $deptModels[$empData['dept']]->id,
                'joining_date' => $empData['joining'],
                'employment_status' => 'active',
                'salary' => $empData['salary'],
                'notes' => 'Dedicated and skilled professional in ' . $empData['dept'],
                'leave_quota' => $empData['quota'],
            ]);

            $user->update(['employee_id' => $emp->id]);
            $employeeModels[] = $emp;
        }

        // 7. Seed Past 25 Days Attendance & Work Entries
        $today = now();
        $statuses = ['present', 'present', 'present', 'present', 'wfh', 'present', 'half_day'];

        for ($i = 25; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);

            // Skip Sundays
            if ($date->isSunday()) {
                continue;
            }

            $dateStr = $date->format('Y-m-d');

            foreach ($employeeModels as $idx => $emp) {
                // Random attendance status
                $randStatus = $statuses[($idx + $i) % count($statuses)];
                if ($i === 3 && $idx === 1) {
                    $randStatus = 'leave';
                }

                DailyAttendance::create([
                    'employee_id' => $emp->id,
                    'date' => $dateStr,
                    'status' => $randStatus,
                    'check_in' => $randStatus === 'absent' || $randStatus === 'leave' ? null : '09:28:00',
                    'check_out' => $randStatus === 'absent' || $randStatus === 'leave' ? null : '18:35:00',
                    'remarks' => $randStatus === 'wfh' ? 'Approved remote work' : null,
                    'recorded_by_user_id' => $manager->id,
                ]);

                // Create work entries if present or wfh or half_day
                if (in_array($randStatus, ['present', 'wfh', 'half_day'])) {
                    // Pick 1-3 categories per employee
                    $empCats = match ($emp->department->name) {
                        'Graphics & Design' => [$catModels[0], $catModels[1], $catModels[2], $catModels[3]],
                        'Video Production' => [$catModels[13], $catModels[14], $catModels[3]],
                        'Digital Marketing' => [$catModels[11], $catModels[1], $catModels[12]],
                        'Web Development' => [$catModels[12], $catModels[0], $catModels[2]],
                        default => [$catModels[0]],
                    };

                    $chosenCat = $empCats[($i + $idx) % count($empCats)];
                    $quantity = match ($chosenCat->name) {
                        'Social Media Design', 'Thumbnail', 'Banner Design' => rand(4, 9),
                        'PSD Design', 'Photo Editing' => rand(2, 5),
                        'Video Editing', 'Motion Graphics' => rand(1, 3),
                        'SEO', 'Website Update' => rand(3, 6),
                        default => rand(2, 4),
                    };

                    DailyWorkEntry::create([
                        'date' => $dateStr,
                        'employee_id' => $emp->id,
                        'work_category_id' => $chosenCat->id,
                        'quantity' => $quantity,
                        'remarks' => 'Completed task batch #' . rand(100, 999),
                        'created_by_user_id' => $manager->id,
                    ]);

                    // Add a second entry sometimes
                    if (($i + $idx) % 2 === 0) {
                        $secondCat = $empCats[(($i + $idx) + 1) % count($empCats)];
                        DailyWorkEntry::create([
                            'date' => $dateStr,
                            'employee_id' => $emp->id,
                            'work_category_id' => $secondCat->id,
                            'quantity' => rand(1, 4),
                            'remarks' => 'Supplementary client delivery',
                            'created_by_user_id' => $manager->id,
                        ]);
                    }
                }
            }
        }

        // 8. Leave Requests
        LeaveRequest::create([
            'employee_id' => $employeeModels[1]->id,
            'leave_type_id' => $leaveTypeModels[0]->id,
            'start_date' => $today->copy()->subDays(3)->format('Y-m-d'),
            'end_date' => $today->copy()->subDays(3)->format('Y-m-d'),
            'total_days' => 1,
            'reason' => 'Family function in hometown',
            'status' => 'approved',
            'action_by_user_id' => $manager->id,
            'action_remarks' => 'Approved, project handover done.',
        ]);

        LeaveRequest::create([
            'employee_id' => $employeeModels[2]->id,
            'leave_type_id' => $leaveTypeModels[1]->id,
            'start_date' => $today->copy()->addDays(2)->format('Y-m-d'),
            'end_date' => $today->copy()->addDays(3)->format('Y-m-d'),
            'total_days' => 2,
            'reason' => 'Medical checkup and recovery',
            'status' => 'pending',
        ]);

        // 9. Sample Studio Todo Tasks
        \App\Models\Todo::create([
            'user_id' => $manager->id,
            'assigned_to_user_id' => $superAdmin->id,
            'title' => 'Export 10 YouTube Thumbnails for Campaign',
            'description' => 'Final color grading and PSD export for client review by 6 PM.',
            'priority' => 'high',
            'category' => 'Design',
            'due_date' => $today->format('Y-m-d'),
            'due_time' => '18:00:00',
            'is_completed' => false,
        ]);

        \App\Models\Todo::create([
            'user_id' => $manager->id,
            'assigned_to_user_id' => $superAdmin->id,
            'title' => 'Client Revision: Instagram Carousel #4',
            'description' => 'Update brand color scheme to pastel purple as requested.',
            'priority' => 'medium',
            'category' => 'Revision',
            'due_date' => $today->format('Y-m-d'),
            'due_time' => '16:30:00',
            'is_completed' => false,
        ]);

        \App\Models\Todo::create([
            'user_id' => $superAdmin->id,
            'assigned_to_user_id' => null,
            'title' => 'Review Monthly Output & Performance Scorecards',
            'description' => 'Audit team deliverables before sending monthly reports.',
            'priority' => 'low',
            'category' => 'Review',
            'due_date' => $today->copy()->addDays(2)->format('Y-m-d'),
            'due_time' => '11:00:00',
            'is_completed' => false,
        ]);

        \App\Models\Todo::create([
            'user_id' => $manager->id,
            'assigned_to_user_id' => $superAdmin->id,
            'title' => 'Weekly Team Sync & Work Allocation',
            'description' => 'Discuss upcoming client sprint deadlines.',
            'priority' => 'medium',
            'category' => 'Meeting',
            'due_date' => $today->copy()->subDays(1)->format('Y-m-d'),
            'due_time' => '10:00:00',
            'is_completed' => true,
            'completed_at' => now()->subDay(),
        ]);

        // 10. Initial Audit Logs
        AuditLog::create([
            'user_id' => $superAdmin->id,
            'action' => 'setup',
            'module' => 'System',
            'description' => 'System initialized with demo settings, employees, and work categories for Posterit.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Initializer',
        ]);
    }
}
