<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Print Report - Posterit System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', sans-serif; background: #fff; color: #0f172a; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-8 max-w-5xl mx-auto" onload="window.print()">

    <!-- Print / Close Toolbar -->
    <div class="no-print mb-6 p-4 bg-slate-100 rounded-2xl flex items-center justify-between border border-slate-200">
        <span class="text-xs font-semibold text-slate-600">Print Preview Mode</span>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-sm">Print Now</button>
            <button onclick="window.close()" class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold">Close</button>
        </div>
    </div>

    <!-- Letterhead -->
    <div class="border-b-2 border-indigo-600 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-indigo-600 tracking-tight">POSTERIT DIGITAL STUDIO</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Employee Work & Performance Management System</p>
        </div>
        <div class="text-right text-xs text-slate-500">
            <div><strong>Report:</strong> {{ strtoupper(str_replace('_', ' ', $reportType)) }}</div>
            <div><strong>Date Range:</strong> {{ $startDate }} to {{ $endDate }}</div>
            <div class="text-[10px] text-slate-400 mt-1">Generated: {{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>

    <!-- Content Table -->
    @if($reportType === 'daily_work')
    <table class="w-full text-left text-xs border-collapse">
        <thead>
            <tr class="bg-slate-100 border-b border-slate-300 text-slate-600 uppercase text-[10px]">
                <th class="py-2.5 px-3">Date</th>
                <th class="py-2.5 px-3">Employee</th>
                <th class="py-2.5 px-3">Department</th>
                <th class="py-2.5 px-3">Category</th>
                <th class="py-2.5 px-3 text-center">Quantity</th>
                <th class="py-2.5 px-3">Remarks</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($data['records'] as $r)
            <tr>
                <td class="py-2 px-3">{{ $r->date->format('Y-m-d') }}</td>
                <td class="py-2 px-3 font-semibold">{{ $r->employee->name }} ({{ $r->employee->employee_code }})</td>
                <td class="py-2 px-3 text-slate-500">{{ $r->employee->department->name ?? 'N/A' }}</td>
                <td class="py-2 px-3 font-medium">{{ $r->category->name }}</td>
                <td class="py-2 px-3 text-center font-bold text-sm">{{ $r->quantity }}</td>
                <td class="py-2 px-3 text-slate-500">{{ $r->remarks ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'attendance')
    <table class="w-full text-left text-xs border-collapse">
        <thead>
            <tr class="bg-slate-100 border-b border-slate-300 text-slate-600 uppercase text-[10px]">
                <th class="py-2.5 px-3">Date</th>
                <th class="py-2.5 px-3">Employee</th>
                <th class="py-2.5 px-3">Department</th>
                <th class="py-2.5 px-3">Status</th>
                <th class="py-2.5 px-3">Check In</th>
                <th class="py-2.5 px-3">Check Out</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($data['records'] as $r)
            <tr>
                <td class="py-2 px-3">{{ $r->date->format('Y-m-d') }}</td>
                <td class="py-2 px-3 font-semibold">{{ $r->employee->name }} ({{ $r->employee->employee_code }})</td>
                <td class="py-2 px-3 text-slate-500">{{ $r->employee->department->name ?? 'N/A' }}</td>
                <td class="py-2 px-3 font-bold uppercase">{{ $r->status }}</td>
                <td class="py-2 px-3">{{ $r->check_in ?? '-' }}</td>
                <td class="py-2 px-3">{{ $r->check_out ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'employee_summary')
    <table class="w-full text-left text-xs border-collapse">
        <thead>
            <tr class="bg-slate-100 border-b border-slate-300 text-slate-600 uppercase text-[10px]">
                <th class="py-2.5 px-3">Employee</th>
                <th class="py-2.5 px-3">Department</th>
                <th class="py-2.5 px-3 text-center">Total Works</th>
                <th class="py-2.5 px-3 text-center">Present</th>
                <th class="py-2.5 px-3 text-center">WFH</th>
                <th class="py-2.5 px-3 text-center">Leaves</th>
                <th class="py-2.5 px-3 text-center">Attendance %</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($data['summaries'] as $s)
            <tr>
                <td class="py-2 px-3 font-semibold">{{ $s['employee']->name }} ({{ $s['employee']->employee_code }})</td>
                <td class="py-2 px-3 text-slate-500">{{ $s['employee']->department->name ?? 'N/A' }}</td>
                <td class="py-2 px-3 text-center font-bold text-sm">{{ $s['total_works'] }}</td>
                <td class="py-2 px-3 text-center">{{ $s['present_days'] }}</td>
                <td class="py-2 px-3 text-center">{{ $s['wfh_days'] }}</td>
                <td class="py-2 px-3 text-center">{{ $s['leave_days'] }}</td>
                <td class="py-2 px-3 text-center font-extrabold">{{ $s['attendance_rate'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'category_summary')
    <table class="w-full text-left text-xs border-collapse">
        <thead>
            <tr class="bg-slate-100 border-b border-slate-300 text-slate-600 uppercase text-[10px]">
                <th class="py-2.5 px-3">Work Category</th>
                <th class="py-2.5 px-3 text-center">Total Task Entries</th>
                <th class="py-2.5 px-3 text-center">Total Quantity Completed</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($data['records'] as $c)
            <tr>
                <td class="py-2 px-3 font-bold">{{ $c->category->name ?? 'N/A' }}</td>
                <td class="py-2 px-3 text-center">{{ $c->total_tasks }} logs</td>
                <td class="py-2 px-3 text-center font-bold text-sm">{{ $c->total_qty }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="mt-8 pt-4 border-t border-slate-200 text-center text-[10px] text-slate-400">
        Posterit Employee Management System • Confidential Company Report
    </div>

</body>
</html>
