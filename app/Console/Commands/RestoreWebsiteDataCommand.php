<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\CompanySetting;
use App\Models\DailyAttendance;
use App\Models\DailyWorkEntry;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Todo;
use App\Models\User;
use App\Models\WorkCategory;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('data:restore-all {--days=12 : Number of days of history to populate} {--force : Force execute without prompt}')]
#[Description('Restore comprehensive production website data including attendance, daily works, tasks, categories, and studio records.')]
class RestoreWebsiteDataCommand extends Command
{
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('  POSTERIT OFFICE - WEBSITE DATA RESTORATION');
        $this->info('====================================================');

        // 1. Ensure Standard Company Settings
        $this->info('1. Verifying Company Settings...');
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
            'office_timing_start' => '09:30',
            'office_timing_end' => '18:30',
        ];

        foreach ($settings as $k => $v) {
            CompanySetting::set($k, $v);
        }

        // 2. Ensure Departments
        $this->info('2. Verifying Departments...');
        $departmentsData = [
            ['name' => 'Graphics & Design', 'description' => 'Social media creatives, banners, thumbnails, branding'],
            ['name' => 'Video Production', 'description' => 'Reels, video editing, motion graphics, YouTube videos'],
            ['name' => 'Digital Marketing', 'description' => 'SEO, social media campaigns, content strategy'],
            ['name' => 'Web Development', 'description' => 'Front-end, UI/UX, website maintenance, landing pages'],
            ['name' => 'Quality Assurance', 'description' => 'Quality check, design audit, and final delivery verification'],
        ];

        $departments = [];
        foreach ($departmentsData as $d) {
            $departments[$d['name']] = Department::firstOrCreate(['name' => $d['name']], $d);
        }

        // 3. Ensure Work Categories
        $this->info('3. Verifying Work Categories...');
        $categoriesData = [
            ['name' => 'PSD Design', 'color' => '#0071E3', 'description' => 'Layered Photoshop source templates and design layouts'],
            ['name' => 'Social Media Design', 'color' => '#5856D6', 'description' => 'Instagram, Facebook, LinkedIn single creatives and carousels'],
            ['name' => 'Banner Design', 'color' => '#34C759', 'description' => 'Web banners, display ads, hero banners, standees'],
            ['name' => 'Thumbnail', 'color' => '#FF9500', 'description' => 'High-CTR YouTube and video thumbnails'],
            ['name' => 'Logo', 'color' => '#FF2D55', 'description' => 'Logos, marks, brand symbols, vector icons'],
            ['name' => 'Flyer', 'color' => '#AF52DE', 'description' => 'Print flyers, digital brochures, promotional handouts'],
            ['name' => 'Brochure', 'color' => '#64748B', 'description' => 'Multi-page product catalogues and company profiles'],
            ['name' => 'Vector', 'color' => '#10B981', 'description' => 'Custom vector art, character poses, icon assets'],
            ['name' => 'Illustration', 'color' => '#F43F5E', 'description' => 'Digital illustrations, scenic backgrounds, character concepts'],
            ['name' => 'Photo Editing', 'color' => '#84CC16', 'description' => 'Color grading, retouching, background manipulation'],
            ['name' => 'Image Upload', 'color' => '#06B6D4', 'description' => 'Asset publishing, catalog upload, cloud sync'],
            ['name' => 'SEO', 'color' => '#D97706', 'description' => 'Search engine optimization, meta descriptions, alt tags'],
            ['name' => 'Website Update', 'color' => '#0284C7', 'description' => 'Landing page modifications, UI tweaks, asset updates'],
            ['name' => 'Video Editing', 'color' => '#EF4444', 'description' => 'Video cutting, pacing, audio sync, sound effects'],
            ['name' => 'Motion Graphics', 'color' => '#A855F7', 'description' => 'After Effects animations, kinetic typography, intro/outro stings'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $cat = WorkCategory::firstOrCreate(['name' => $c['name']], $c);
            $cat->update(['color' => $c['color'], 'description' => $c['description']]);
            $categories[$c['name']] = $cat;
        }

        // 4. Ensure Leave Types
        $this->info('4. Verifying Leave Types...');
        $leaveTypesData = [
            ['name' => 'Casual Leave', 'default_days_per_year' => 6, 'is_paid' => true],
            ['name' => 'Sick Leave', 'default_days_per_year' => 6, 'is_paid' => true],
            ['name' => 'Paid Leave', 'default_days_per_year' => 6, 'is_paid' => true],
            ['name' => 'Unpaid Leave', 'default_days_per_year' => 10, 'is_paid' => false],
        ];

        $leaveTypes = [];
        foreach ($leaveTypesData as $lt) {
            $leaveTypes[$lt['name']] = LeaveType::firstOrCreate(['name' => $lt['name']], $lt);
        }

        // 5. Ensure Employees & User Accounts
        $this->info('5. Verifying Users and Employees...');
        $teamMembers = [
            [
                'email' => 'samir@posterit.com',
                'name' => 'Samir Mete',
                'role' => 'super_admin',
                'code' => 'EMP-000',
                'designation' => 'Studio Director & Super Admin',
                'department' => 'Graphics & Design',
                'salary' => 90000,
                'mobile' => '+91 98765 43210',
            ],
            [
                'email' => 'sam@posterit.com',
                'name' => 'Sam Mete',
                'role' => 'super_admin',
                'code' => 'EMP-DIR',
                'designation' => 'Operations Director & Super Admin',
                'department' => 'Digital Marketing',
                'salary' => 85000,
                'mobile' => '+91 98765 43211',
            ],
            [
                'email' => 'metex.biswajit@gmail.com',
                'name' => 'Biswajit Mete',
                'role' => 'employee',
                'code' => 'EMP-001',
                'designation' => 'Video Editor',
                'department' => 'Video Production',
                'salary' => 38000,
                'mobile' => '+91 98234 11221',
            ],
            [
                'email' => 'sumanoffice931@gmail.com',
                'name' => 'Suman Das Bairagya',
                'role' => 'employee',
                'code' => 'EMP-008',
                'designation' => 'Graphics designer & video editor',
                'department' => 'Graphics & Design',
                'salary' => 36000,
                'mobile' => '+91 98234 11228',
            ],
            [
                'email' => 'metex.riya@gmail.com',
                'name' => 'Riya Mete',
                'role' => 'employee',
                'code' => 'EMP-009',
                'designation' => 'Illustration artist',
                'department' => 'Graphics & Design',
                'salary' => 37000,
                'mobile' => '+91 98234 11229',
            ],
            [
                'email' => 'sukantaoffice25@gmail.com',
                'name' => 'Sukanta Das',
                'role' => 'employee',
                'code' => 'EMP-010',
                'designation' => 'Illustration Artist',
                'department' => 'Graphics & Design',
                'salary' => 35000,
                'mobile' => '+91 98234 11230',
            ],
            [
                'email' => 'dutta.gopal@gmail.com',
                'name' => 'Tithi Dutta',
                'role' => 'employee',
                'code' => 'EMP-011',
                'designation' => 'Illustration artist',
                'department' => 'Graphics & Design',
                'salary' => 34000,
                'mobile' => '+91 98234 11231',
            ],
            [
                'email' => 'pradipmetex@gmail.com',
                'name' => 'Pradip',
                'role' => 'employee',
                'code' => 'EMP-012',
                'designation' => 'Illustration artist',
                'department' => 'Graphics & Design',
                'salary' => 34000,
                'mobile' => '+91 98234 11232',
            ],
            [
                'email' => 'souvik14760@gmail.com',
                'name' => 'Souvik Chowdhury',
                'role' => 'employee',
                'code' => 'EMP-013',
                'designation' => 'Social Media Manager',
                'department' => 'Digital Marketing',
                'salary' => 36000,
                'mobile' => '+91 98234 11233',
            ],
        ];

        $employees = [];
        $users = [];
        $superAdminUser = null;

        foreach ($teamMembers as $tm) {
            $user = User::firstOrCreate(
                ['email' => $tm['email']],
                [
                    'name' => $tm['name'],
                    'password' => Hash::make('password'),
                    'role' => $tm['role'],
                    'is_active' => true,
                ]
            );

            if ($tm['role'] === 'super_admin' && ! $superAdminUser) {
                $superAdminUser = $user;
            }

            $dept = $departments[$tm['department']] ?? Department::first();

            $employee = Employee::where('email', $tm['email'])
                ->orWhere('employee_code', $tm['code'])
                ->orWhere('user_id', $user->id)
                ->first();

            if (! $employee) {
                $employee = Employee::create([
                    'employee_code' => $tm['code'],
                    'user_id' => $user->id,
                    'name' => $tm['name'],
                    'email' => $tm['email'],
                    'mobile_number' => $tm['mobile'],
                    'designation' => $tm['designation'],
                    'department_id' => $dept ? $dept->id : 1,
                    'joining_date' => '2024-01-01',
                    'employment_status' => 'active',
                    'salary' => $tm['salary'],
                    'leave_quota' => 18,
                ]);
            } else {
                $employee->update([
                    'user_id' => $user->id,
                    'name' => $tm['name'],
                    'designation' => $tm['designation'],
                    'department_id' => $dept ? $dept->id : $employee->department_id,
                    'employment_status' => 'active',
                ]);
            }

            $user->update(['employee_id' => $employee->id]);

            $employees[$tm['email']] = $employee;
            $users[$tm['email']] = $user;
        }

        if (! $superAdminUser) {
            $superAdminUser = User::where('role', 'super_admin')->first() ?? User::first();
        }

        // 6. Seed Daily Attendance (Sep 1 to Today Sep 12)
        $this->info('6. Generating Daily Attendance Records (1st to 12th Sep)...');
        $daysCount = (int) $this->option('days') ?: 12;
        $today = now();
        $totalAttendanceCreated = 0;

        for ($day = 1; $day <= $daysCount; $day++) {
            $targetDate = Carbon::create($today->year, 9, $day, 0, 0, 0, 'Asia/Kolkata');

            // Skip Sundays (Sep 6 is Sunday)
            if ($targetDate->isSunday()) {
                continue;
            }

            $dateStr = $targetDate->format('Y-m-d');
            $isToday = $targetDate->isToday();

            foreach ($employees as $email => $emp) {
                // Determine realistic check-in / check-out
                $randMin = ($emp->id * 7 + $day * 3) % 25; // 0 to 24 mins after 09:15
                $checkInHour = 9;
                $checkInMin = 15 + $randMin;
                $checkInStr = sprintf('%02d:%02d:00', $checkInHour, $checkInMin);

                $outMin = ($emp->id * 5 + $day * 4) % 35; // 0 to 34 mins after 18:10
                $checkOutStr = sprintf('18:%02d:00', 10 + $outMin);

                $status = 'present';
                $remarks = 'Regular Studio Shift (On-Time)';

                // Occasional WFH or Leave on past days
                if (! $isToday) {
                    if ($day === 3 && $emp->employee_code === 'EMP-001') {
                        $status = 'leave';
                        $checkInStr = null;
                        $checkOutStr = null;
                        $remarks = 'Approved family leave';
                    } elseif ($day === 5 && $emp->employee_code === 'EMP-013') {
                        $status = 'wfh';
                        $remarks = 'Remote campaign coordination';
                    } elseif ($day === 8 && $emp->employee_code === 'EMP-009') {
                        $status = 'half_day';
                        $checkOutStr = '14:15:00';
                        $remarks = 'Half-day - Dental appointment';
                    }
                } else {
                    // Today: currently checked in, on duty!
                    $checkOutStr = null;
                    $remarks = 'Currently active on duty';
                }

                DailyAttendance::updateOrCreate(
                    [
                        'employee_id' => $emp->id,
                        'date' => $dateStr,
                    ],
                    [
                        'status' => $status,
                        'check_in' => $checkInStr,
                        'check_out' => $checkOutStr,
                        'remarks' => $remarks,
                        'recorded_by_user_id' => $superAdminUser->id,
                    ]
                );

                $totalAttendanceCreated++;
            }
        }
        $this->info("  ✓ Successfully created/updated {$totalAttendanceCreated} attendance records.");

        // 7. Seed Daily Work Entries (Sep 1 to Today Sep 12)
        $this->info('7. Generating Daily Work Entries across team specialities...');
        $totalWorkEntriesCreated = 0;

        // Skill Mapping for each employee
        $workPresets = [
            'EMP-000' => [ // Samir Mete
                ['cat' => 'Website Update', 'qty' => [1, 2], 'remarks' => 'Studio portal feature enhancements & performance tuning'],
                ['cat' => 'PSD Design', 'qty' => [2, 3], 'remarks' => 'Master creative layout & client deck templates'],
                ['cat' => 'Social Media Design', 'qty' => [2, 4], 'remarks' => 'Executive branding & campaign hero banners'],
            ],
            'EMP-DIR' => [ // Sam Mete
                ['cat' => 'SEO', 'qty' => [3, 5], 'remarks' => 'Website optimization & technical SEO audits'],
                ['cat' => 'Banner Design', 'qty' => [2, 4], 'remarks' => 'Campaign ad sets and high-converting display banners'],
                ['cat' => 'Social Media Design', 'qty' => [3, 5], 'remarks' => 'Multi-platform promotional collateral'],
            ],
            'EMP-001' => [ // Biswajit Mete (Video Editor)
                ['cat' => 'Video Editing', 'qty' => [2, 4], 'remarks' => '4K video cuts, pacing, color grading & audio sync'],
                ['cat' => 'Motion Graphics', 'qty' => [1, 3], 'remarks' => 'Animated intro/outro stings, lower-thirds & titles'],
                ['cat' => 'Thumbnail', 'qty' => [3, 6], 'remarks' => 'High-CTR YouTube video thumbnails batch'],
            ],
            'EMP-008' => [ // Suman Das Bairagya (Graphics & Video)
                ['cat' => 'Social Media Design', 'qty' => [4, 7], 'remarks' => 'Daily brand social media post creatives'],
                ['cat' => 'Video Editing', 'qty' => [1, 3], 'remarks' => 'Short-form Reels, Shorts & TikTok video edits'],
                ['cat' => 'Thumbnail', 'qty' => [3, 5], 'remarks' => 'Catchy custom YouTube video thumbnails'],
                ['cat' => 'Banner Design', 'qty' => [2, 4], 'remarks' => 'Promotional display banners & web hero assets'],
            ],
            'EMP-009' => [ // Riya Mete (Illustration Artist)
                ['cat' => 'Illustration', 'qty' => [3, 6], 'remarks' => 'Digital character concept art & background illustrations'],
                ['cat' => 'Vector', 'qty' => [3, 7], 'remarks' => 'Custom vector graphics, stickers & icon packs'],
                ['cat' => 'PSD Design', 'qty' => [2, 4], 'remarks' => 'Layered composition artwork and editorial layouts'],
                ['cat' => 'Photo Editing', 'qty' => [3, 6], 'remarks' => 'High-end portrait and product retouching'],
            ],
            'EMP-010' => [ // Sukanta Das (Illustration Artist)
                ['cat' => 'Illustration', 'qty' => [3, 5], 'remarks' => 'Line art, sketches and color-fill scene illustrations'],
                ['cat' => 'Vector', 'qty' => [2, 5], 'remarks' => 'Scalable SVG icons and brand illustration sets'],
                ['cat' => 'PSD Design', 'qty' => [2, 3], 'remarks' => 'Creative poster and banner designs in Photoshop'],
            ],
            'EMP-011' => [ // Tithi Dutta (Illustration Artist)
                ['cat' => 'Illustration', 'qty' => [2, 5], 'remarks' => 'Children book character sketches & scene artwork'],
                ['cat' => 'Photo Editing', 'qty' => [4, 8], 'remarks' => 'E-commerce photo cutout, shadow, and color correction'],
                ['cat' => 'Vector', 'qty' => [2, 4], 'remarks' => 'Vector patterns, textures, and asset cleanups'],
            ],
            'EMP-012' => [ // Pradip (Illustration Artist)
                ['cat' => 'Illustration', 'qty' => [3, 6], 'remarks' => 'Digital storyboards and concept illustrations'],
                ['cat' => 'Vector', 'qty' => [3, 6], 'remarks' => 'Vector logo variations and typography marks'],
                ['cat' => 'Logo', 'qty' => [1, 3], 'remarks' => 'Brand emblem and minimalist logo concepts'],
            ],
            'EMP-013' => [ // Souvik Chowdhury (Social Media Manager)
                ['cat' => 'Social Media Design', 'qty' => [5, 9], 'remarks' => 'Weekly Instagram carousels and story sets'],
                ['cat' => 'Banner Design', 'qty' => [2, 5], 'remarks' => 'Google Display network ad creative sets'],
                ['cat' => 'SEO', 'qty' => [3, 6], 'remarks' => 'Keyword research, meta tags, and alt attribute optimizations'],
                ['cat' => 'Image Upload', 'qty' => [6, 12], 'remarks' => 'Bulk media uploads, tag assignments, and asset indexing'],
            ],
        ];

        // Clear existing work entries if resetting to ensure clean metrics
        DailyWorkEntry::truncate();

        for ($day = 1; $day <= $daysCount; $day++) {
            $targetDate = Carbon::create($today->year, 9, $day, 0, 0, 0, 'Asia/Kolkata');

            if ($targetDate->isSunday()) {
                continue;
            }

            $dateStr = $targetDate->format('Y-m-d');

            foreach ($employees as $email => $emp) {
                $presets = $workPresets[$emp->employee_code] ?? [
                    ['cat' => 'Social Media Design', 'qty' => [2, 5], 'remarks' => 'General creative work delivery'],
                ];

                // Check attendance status for this day
                $att = DailyAttendance::where('employee_id', $emp->id)->whereDate('date', $dateStr)->first();
                if ($att && $att->status === 'leave') {
                    continue; // Skip work entry if employee was on leave
                }

                // Pick 1 to 2 presets for this day
                $numEntries = ($day % 2 === 0) ? 2 : 1;
                $chosenIndices = [($day + $emp->id) % count($presets)];
                if ($numEntries > 1 && count($presets) > 1) {
                    $chosenIndices[] = ($day + $emp->id + 1) % count($presets);
                }

                foreach ($chosenIndices as $idx) {
                    $item = $presets[$idx];
                    $cat = $categories[$item['cat']] ?? WorkCategory::first();
                    $qtyMin = $item['qty'][0];
                    $qtyMax = $item['qty'][1];
                    $qty = rand($qtyMin, $qtyMax);

                    DailyWorkEntry::create([
                        'date' => $dateStr,
                        'employee_id' => $emp->id,
                        'work_category_id' => $cat->id,
                        'quantity' => $qty,
                        'remarks' => "{$item['remarks']} (Batch #".(100 + $day * 7 + $emp->id).')',
                        'created_by_user_id' => $superAdminUser->id,
                    ]);

                    $totalWorkEntriesCreated++;
                }
            }
        }
        $this->info("  ✓ Successfully created {$totalWorkEntriesCreated} work entries.");

        // 8. Seed Comprehensive Studio Tasks & Todos
        $this->info('8. Generating Studio Tasks (To-Dos)...');
        Todo::truncate();

        $todosData = [
            [
                'assigned_email' => 'metex.biswajit@gmail.com',
                'title' => 'Export 4K Commercial Reel with Sound Effects',
                'description' => 'Complete final color grading in DaVinci Resolve, sync background score, and render 4K Master with 1080p Instagram cut.',
                'priority' => 'high',
                'category' => 'Video Production',
                'status' => 'in_progress',
                'due_date' => $today->format('Y-m-d'),
                'due_time' => '18:00:00',
                'is_completed' => false,
                'subtasks' => [
                    ['title' => 'Rough timeline cut approved', 'completed' => true],
                    ['title' => 'Color grading & LUT matching', 'completed' => true],
                    ['title' => 'Sound design & SFX mix', 'completed' => false],
                    ['title' => 'Final 4K ProRes render', 'completed' => false],
                ],
            ],
            [
                'assigned_email' => 'metex.riya@gmail.com',
                'title' => 'Brand Mascot Vector Character Sheets (5 Poses)',
                'description' => 'Create high-resolution vector character sheet for the new studio mascot in standing, holding laptop, celebrating, thinking, and presenting poses.',
                'priority' => 'high',
                'category' => 'Illustration',
                'status' => 'in_progress',
                'due_date' => $today->format('Y-m-d'),
                'due_time' => '17:30:00',
                'is_completed' => false,
                'subtasks' => [
                    ['title' => 'Rough pencil sketch concepts', 'completed' => true],
                    ['title' => 'Vector line-art in Adobe Illustrator', 'completed' => true],
                    ['title' => 'Color palette & shading', 'completed' => false],
                    ['title' => 'SVG & PNG export pack', 'completed' => false],
                ],
            ],
            [
                'assigned_email' => 'sumanoffice931@gmail.com',
                'title' => 'YouTube Masterclass Thumbnail Suite (10 Variations)',
                'description' => 'Design high-CTR thumbnails with expressive facial cutouts, bold yellow/white typography, and glowing highlight borders.',
                'priority' => 'high',
                'category' => 'Graphics & Design',
                'status' => 'todo',
                'due_date' => $today->format('Y-m-d'),
                'due_time' => '19:00:00',
                'is_completed' => false,
                'subtasks' => [
                    ['title' => 'Facial cutout extraction & clean edge', 'completed' => true],
                    ['title' => 'Typography layout & drop shadows', 'completed' => false],
                    ['title' => 'A/B test thumbnail export (1280x720)', 'completed' => false],
                ],
            ],
            [
                'assigned_email' => 'souvik14760@gmail.com',
                'title' => 'Q3 Product Launch Instagram Carousel (8 Slides)',
                'description' => 'Develop educational carousel breakdown on UI/UX best practices with seamless slide transitions and call-to-action slide.',
                'priority' => 'medium',
                'category' => 'Marketing',
                'status' => 'in_progress',
                'due_date' => $today->format('Y-m-d'),
                'due_time' => '16:00:00',
                'is_completed' => false,
                'subtasks' => [
                    ['title' => 'Copywriting & slide outline', 'completed' => true],
                    ['title' => 'Slide layout design in Figma', 'completed' => true],
                    ['title' => 'Swipe indicators & visual hooks', 'completed' => false],
                    ['title' => 'Caption & hashtag curation', 'completed' => false],
                ],
            ],
            [
                'assigned_email' => 'sukantaoffice25@gmail.com',
                'title' => 'Editorial Line Art Illustrations for Tech Blog',
                'description' => 'Draw 4 minimalist isometric line art illustrations representing cloud computing, cybersecurity, mobile apps, and artificial intelligence.',
                'priority' => 'medium',
                'category' => 'Illustration',
                'status' => 'todo',
                'due_date' => $today->copy()->addDay()->format('Y-m-d'),
                'due_time' => '15:00:00',
                'is_completed' => false,
                'subtasks' => [
                    ['title' => 'Concept wireframes', 'completed' => false],
                    ['title' => 'Isometric grid alignment', 'completed' => false],
                    ['title' => 'Export vector assets', 'completed' => false],
                ],
            ],
            [
                'assigned_email' => 'dutta.gopal@gmail.com',
                'title' => 'E-commerce Catalog Retouching Batch (50 Photos)',
                'description' => 'Pure white background extraction (#FFFFFF), neutral shadow creation, skin tone balancing, and jewelry glare cleanup.',
                'priority' => 'low',
                'category' => 'Graphics & Design',
                'status' => 'todo',
                'due_date' => $today->copy()->addDays(2)->format('Y-m-d'),
                'due_time' => '18:30:00',
                'is_completed' => false,
                'subtasks' => [
                    ['title' => 'Batch clipping paths', 'completed' => false],
                    ['title' => 'Color correction & levels', 'completed' => false],
                    ['title' => 'Web resolution export (2000x2000)', 'completed' => false],
                ],
            ],
            [
                'assigned_email' => 'pradipmetex@gmail.com',
                'title' => 'Custom 3D Icon Pack (20 Studio Symbols)',
                'description' => 'Model and render glossy 3D icons in Blender for website feature section.',
                'priority' => 'medium',
                'category' => 'Illustration',
                'status' => 'todo',
                'due_date' => $today->copy()->addDays(3)->format('Y-m-d'),
                'due_time' => '17:00:00',
                'is_completed' => false,
                'subtasks' => [
                    ['title' => '3D mesh modeling', 'completed' => false],
                    ['title' => 'Glass & clay materials setup', 'completed' => false],
                    ['title' => 'Transparent PNG render batch', 'completed' => false],
                ],
            ],
            [
                'assigned_email' => 'samir@posterit.com',
                'title' => 'Audit Monthly Output & Approve Team Timesheets',
                'description' => 'Review September deliverables across departments and finalize attendance scorecards before monthly client invoicing.',
                'priority' => 'high',
                'category' => 'Operations',
                'status' => 'todo',
                'due_date' => $today->copy()->addDays(1)->format('Y-m-d'),
                'due_time' => '12:00:00',
                'is_completed' => false,
                'subtasks' => [
                    ['title' => 'Inspect department work volumes', 'completed' => false],
                    ['title' => 'Cross-check attendance logs', 'completed' => false],
                    ['title' => 'Export monthly executive summary', 'completed' => false],
                ],
            ],
            [
                'assigned_email' => 'sam@posterit.com',
                'title' => 'Brand Identity Guidelines Manual (PDF Export)',
                'description' => 'Compiled official typography hierarchy, color codes (HEX, RGB, CMYK), and logo usage guidelines for clients.',
                'priority' => 'medium',
                'category' => 'Design',
                'status' => 'completed',
                'due_date' => $today->copy()->subDays(1)->format('Y-m-d'),
                'due_time' => '16:00:00',
                'is_completed' => true,
                'completed_at' => now()->subDay(),
                'subtasks' => [
                    ['title' => 'Color swatch specs defined', 'completed' => true],
                    ['title' => 'Do and Don’t logo placement rules', 'completed' => true],
                    ['title' => 'PDF interactive book render', 'completed' => true],
                ],
            ],
            [
                'assigned_email' => 'souvik14760@gmail.com',
                'title' => 'SEO Meta Optimization for Studio Website',
                'description' => 'Optimized title tags, Open Graph meta images, and structured JSON-LD schema across 12 landing pages.',
                'priority' => 'medium',
                'category' => 'Marketing',
                'status' => 'completed',
                'due_date' => $today->copy()->subDays(2)->format('Y-m-d'),
                'due_time' => '14:00:00',
                'is_completed' => true,
                'completed_at' => now()->subDays(2),
                'subtasks' => [
                    ['title' => 'Keyword gap audit', 'completed' => true],
                    ['title' => 'Meta description rewrites', 'completed' => true],
                    ['title' => 'Google Search Console re-indexing', 'completed' => true],
                ],
            ],
        ];

        foreach ($todosData as $td) {
            $assignedUser = $users[$td['assigned_email']] ?? $superAdminUser;

            Todo::create([
                'user_id' => $superAdminUser->id,
                'assigned_to_user_id' => $assignedUser->id,
                'title' => $td['title'],
                'description' => $td['description'],
                'priority' => $td['priority'],
                'category' => $td['category'],
                'status' => $td['status'],
                'due_date' => $td['due_date'],
                'due_time' => $td['due_time'],
                'is_completed' => $td['is_completed'],
                'completed_at' => $td['completed_at'] ?? null,
                'subtasks' => $td['subtasks'],
            ]);
        }
        $this->info('  ✓ Successfully created 10 studio tasks.');

        // 9. Seed Leave Requests
        $this->info('9. Generating Leave Requests...');
        LeaveRequest::truncate();

        $empBiswajit = $employees['metex.biswajit@gmail.com'] ?? null;
        $empRiya = $employees['metex.riya@gmail.com'] ?? null;
        $casualLeave = $leaveTypes['Casual Leave'] ?? LeaveType::first();
        $sickLeave = $leaveTypes['Sick Leave'] ?? LeaveType::first();

        if ($empBiswajit && $casualLeave) {
            LeaveRequest::create([
                'employee_id' => $empBiswajit->id,
                'leave_type_id' => $casualLeave->id,
                'start_date' => Carbon::create($today->year, 9, 3)->format('Y-m-d'),
                'end_date' => Carbon::create($today->year, 9, 3)->format('Y-m-d'),
                'total_days' => 1,
                'reason' => 'Family function in hometown (advance notice given)',
                'status' => 'approved',
                'action_by_user_id' => $superAdminUser->id,
                'action_remarks' => 'Approved by Studio Director. Project handover completed.',
            ]);
        }

        if ($empRiya && $sickLeave) {
            LeaveRequest::create([
                'employee_id' => $empRiya->id,
                'leave_type_id' => $sickLeave->id,
                'start_date' => $today->copy()->addDays(4)->format('Y-m-d'),
                'end_date' => $today->copy()->addDays(5)->format('Y-m-d'),
                'total_days' => 2,
                'reason' => 'Medical appointment and routine health checkup',
                'status' => 'pending',
            ]);
        }
        $this->info('  ✓ Successfully created leave records.');

        // 10. Audit Log
        AuditLog::create([
            'user_id' => $superAdminUser->id,
            'action' => 'restore_data',
            'module' => 'System',
            'description' => "Complete studio database restored: {$totalAttendanceCreated} attendances, {$totalWorkEntriesCreated} work entries, and 10 active tasks.",
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Data Restoration Command',
        ]);

        $this->info('====================================================');
        $this->info('  ALL WEBSITE DATA RESTORED SUCCESSFULLY! 🎉');
        $this->info('  • Employees: '.count($employees));
        $this->info("  • Attendances: {$totalAttendanceCreated}");
        $this->info("  • Daily Work Entries: {$totalWorkEntriesCreated}");
        $this->info('  • Tasks (To-Dos): 10');
        $this->info('  • Work Categories: '.count($categories));
        $this->info('====================================================');

        return self::SUCCESS;
    }
}
