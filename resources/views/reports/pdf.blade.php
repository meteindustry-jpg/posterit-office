<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Posterit Work & Attendance Report</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #0f172a; line-height: 1.4; }
        .header { border-bottom: 2px solid #0071e3; padding-bottom: 12px; margin-bottom: 15px; }
        .title { font-size: 18px; font-weight: 900; color: #0071e3; }
        .subtitle { font-size: 11px; color: #334155; margin-top: 3px; font-weight: 600; }
        .meta-box { background: #f8fafc; border: 1px solid #cbd5e1; padding: 8px 12px; margin-bottom: 15px; border-radius: 4px; color: #1e293b; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f1f5f9; color: #0f172a; text-transform: uppercase; font-size: 10px; font-weight: 800; padding: 7px 8px; text-align: left; border-bottom: 2px solid #cbd5e1; letter-spacing: 0.5px; }
        td { padding: 7px 8px; border-bottom: 1px solid #e2e8f0; font-size: 10px; color: #1e293b; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-weight: 800; font-size: 9px; }
        .badge-present { background: #dcfce7; color: #14532d; }
        .badge-wfh { background: #e0e7ff; color: #312e81; }
        .badge-leave { background: #ffe4e6; color: #881337; }
        .badge-absent { background: #f1f5f9; color: #334155; }
        .badge-half_day { background: #fef3c7; color: #78350f; }
        .footer { margin-top: 25px; text-align: center; font-size: 10px; color: #64748b; border-top: 1px solid #cbd5e1; padding-top: 8px; font-weight: 600; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">POSTERIT DIGITAL STUDIO</div>
        <div class="subtitle">Employee Work & Performance Management System</div>
    </div>

    <div class="meta-box">
        <strong>Report Type:</strong> {{ strtoupper(str_replace('_', ' ', $reportType)) }} &nbsp;|&nbsp;
        <strong>Date Range:</strong> {{ $startDate }} to {{ $endDate }} &nbsp;|&nbsp;
        <strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}
    </div>

    @if($reportType === 'daily_work')
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Employee Code</th>
                <th>Employee Name</th>
                <th>Category</th>
                <th class="text-center">Quantity</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['records'] as $r)
            <tr>
                <td>{{ $r->date->format('Y-m-d') }}</td>
                <td>{{ $r->employee->employee_code }}</td>
                <td>{{ $r->employee->name }}</td>
                <td>{{ $r->category->name }}</td>
                <td class="text-center"><strong>{{ $r->quantity }}</strong></td>
                <td>{{ $r->remarks ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'attendance')
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Code</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Status</th>
                <th>Check In</th>
                <th>Check Out</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['records'] as $r)
            <tr>
                <td>{{ $r->date->format('Y-m-d') }}</td>
                <td>{{ $r->employee->employee_code }}</td>
                <td>{{ $r->employee->name }}</td>
                <td>{{ $r->employee->department->name ?? 'N/A' }}</td>
                <td><span class="badge badge-{{ $r->status }}">{{ strtoupper($r->status) }}</span></td>
                <td>{{ $r->check_in ? \Carbon\Carbon::parse($r->check_in)->format('h:i A') : '-' }}</td>
                <td>{{ $r->check_out ? \Carbon\Carbon::parse($r->check_out)->format('h:i A') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'employee_summary')
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Department</th>
                <th class="text-center">Total Works</th>
                <th class="text-center">Present</th>
                <th class="text-center">WFH</th>
                <th class="text-center">Leaves</th>
                <th class="text-center">Attendance %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['summaries'] as $s)
            <tr>
                <td>{{ $s['employee']->employee_code }}</td>
                <td><strong>{{ $s['employee']->name }}</strong></td>
                <td>{{ $s['employee']->department->name ?? 'N/A' }}</td>
                <td class="text-center"><strong>{{ $s['total_works'] }}</strong></td>
                <td class="text-center">{{ $s['present_days'] }}</td>
                <td class="text-center">{{ $s['wfh_days'] }}</td>
                <td class="text-center">{{ $s['leave_days'] }}</td>
                <td class="text-center"><strong>{{ $s['attendance_rate'] }}%</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($reportType === 'category_summary')
    <table>
        <thead>
            <tr>
                <th>Work Category</th>
                <th class="text-center">Total Task Entries</th>
                <th class="text-center">Total Quantity Completed</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['records'] as $c)
            <tr>
                <td><strong>{{ $c->category->name ?? 'N/A' }}</strong></td>
                <td class="text-center">{{ $c->total_tasks }} entries</td>
                <td class="text-center"><strong>{{ $c->total_qty }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        Posterit Employee Management System • Confidential & Proprietary Report • Page 1
    </div>

</body>
</html>
