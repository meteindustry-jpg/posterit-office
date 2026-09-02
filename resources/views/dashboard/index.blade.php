@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    
    <!-- Header & Quick Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                Good day, {{ auth()->user()->name }}
            </h1>
            <p class="text-xs text-slate-500 mt-0.5 font-normal">
                Posterit Operations & Productivity Activity • {{ now()->format('F Y') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('attendance.index') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 shadow-2xs transition">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Daily Attendance</span>
            </a>

            @if(auth()->user()->isManager())
            <a href="{{ route('work-entries.batch') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0071e3] hover:bg-[#0062c4] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Log Works</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Vibrant Dribbble / Apple Arcade Style KPI Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Active Team (Cyan / Sky Blue Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#0284c7] via-[#0ea5e9] to-[#38bdf8] text-white shadow-[0_8px_22px_rgba(14,165,233,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Total Team</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $totalEmployees }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">Active team members</div>
            </div>
        </div>

        <!-- Attendance Today (Emerald / Mint Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#059669] via-[#10b981] to-[#34d399] text-white shadow-[0_8px_22px_rgba(16,185,129,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Present Today</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="flex items-baseline gap-1.5">
                    <div class="text-3xl font-black text-white tracking-tight">{{ $presentToday + $wfhToday }}</div>
                    <span class="text-xs text-white/85 font-medium">/ {{ $totalEmployees }}</span>
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] font-medium text-white/90">
                    <span>{{ $presentToday }} Office</span> •
                    <span>{{ $wfhToday }} WFH</span> •
                    <span>{{ $leaveToday }} Leave</span>
                </div>
            </div>
        </div>

        <!-- Works Done Today (Orange / Amber Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#f59e0b] via-[#f97316] to-[#fb923c] text-white shadow-[0_8px_22px_rgba(249,115,22,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M7 2v11h3v9l7-12h-4l4-8z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Works Today</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $totalWorksToday }}</div>
                <div class="text-[11px] font-medium mt-1">
                    @if($pendingWorkEntries > 0)
                        <span class="text-white/95">{{ $pendingWorkEntries }} pending review</span>
                    @else
                        <span class="text-white/90">✓ All logged today</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Monthly Volume (Violet / Purple Gradient) -->
        <div class="relative overflow-hidden p-5 rounded-2xl bg-gradient-to-br from-[#6366f1] via-[#7c3aed] to-[#8b5cf6] text-white shadow-[0_8px_22px_rgba(99,102,241,0.25)] hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between group">
            <!-- Large Watermark Illustration -->
            <svg class="absolute -right-3 -bottom-3 w-28 h-28 text-white/15 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
            </svg>

            <div class="relative z-10">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-white/90">Monthly Volume</span>
                <div class="w-6 h-0.5 bg-white/30 rounded-full mt-1"></div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="text-3xl font-black text-white tracking-tight">{{ $monthlyWorkCount }}</div>
                <div class="text-[11px] text-white/85 font-medium mt-1">{{ $attendancePercentage }}% Team Attendance</div>
            </div>
        </div>

    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Output Trend Chart -->
        <div class="lg:col-span-2 p-5 ui-panel">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Daily Output Trend</h3>
                    <p class="text-xs text-slate-500 font-normal">Design deliverables in the last 14 days</p>
                </div>
                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[11px] font-medium">14 Days</span>
            </div>

            <div id="workTrendChart" class="h-64 w-full"></div>
        </div>

        <!-- Work Categories Distribution -->
        <div class="p-5 ui-panel flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-sm text-slate-900">Category Share</h3>
                <p class="text-xs text-slate-500 font-normal mb-3">Work breakdown by design type</p>
                <div id="categoryChart" class="h-52 flex items-center justify-center"></div>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap gap-1.5 max-h-24 overflow-y-auto">
                @foreach($categoryBreakdown->take(6) as $cat)
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-50 border border-slate-200 text-slate-700">
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $cat->category->color ?? '#0071e3' }}"></span>
                    <span>{{ $cat->category->name ?? 'Cat' }}: <strong>{{ $cat->total_qty }}</strong></span>
                </span>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Mid Section: Top Performers, Pending Tasks, Leave Requests -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Top Performer Spotlight -->
        <div class="p-5 ui-panel">
            <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Top Performers</h3>
                    <p class="text-[11px] text-slate-500">{{ now()->format('F Y') }} Output Leaderboard</p>
                </div>
                <a href="{{ route('performance.index') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">All →</a>
            </div>

            <div class="space-y-2">
                @forelse($topPerformers as $idx => $tp)
                <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-200/60 flex items-center gap-3">
                    <div class="w-6 h-6 rounded-lg {{ $idx === 0 ? 'bg-amber-100 text-amber-800 font-bold' : ($idx === 1 ? 'bg-slate-200 text-slate-700' : 'bg-slate-100 text-slate-600') }} flex items-center justify-center text-xs shrink-0">
                        {{ $idx + 1 }}
                    </div>
                    <img src="{{ $tp->employee->photo_url }}" class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-200">
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-xs text-slate-900 truncate">{{ $tp->employee->name }}</div>
                        <div class="text-[11px] text-slate-500 truncate">{{ $tp->employee->department->name ?? 'Design' }}</div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="font-bold text-xs text-[#0071e3]">{{ $tp->total_qty }}</div>
                        <div class="text-[9px] text-slate-400 font-medium uppercase">Tasks</div>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-xs text-slate-400">No output records yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Pending Tasks Widget -->
        <div class="p-5 ui-panel">
            <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Pending Tasks</h3>
                    <p class="text-[11px] text-slate-500">Quick action items</p>
                </div>
                <a href="{{ route('todos.index') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">All →</a>
            </div>

            <div class="space-y-2">
                @forelse($myPendingTodos as $todo)
                <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-200/60 flex items-center justify-between gap-3 group">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <form method="POST" action="{{ route('todos.toggle', $todo) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="Mark done" class="w-4.5 h-4.5 rounded-md border border-slate-300 hover:border-[#0071e3] bg-white text-transparent hover:text-slate-300 flex items-center justify-center transition cursor-pointer">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            </button>
                        </form>
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-xs text-slate-900 truncate">{{ $todo->title }}</div>
                            <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5">
                                <span class="text-slate-500">{{ $todo->category }}</span>
                                @if($todo->due_date)
                                    <span>• Due {{ $todo->due_date->format('d M') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($todo->priority === 'high')
                        <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0" title="High Priority"></span>
                    @elseif($todo->priority === 'medium')
                        <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0" title="Medium Priority"></span>
                    @endif
                </div>
                @empty
                <div class="py-8 text-center text-xs text-slate-400">
                    No pending tasks.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pending Leave Requests -->
        <div class="p-5 ui-panel">
            <div class="flex items-center justify-between mb-3.5 pb-2 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Leave Requests</h3>
                    <p class="text-[11px] text-slate-500">Pending applications</p>
                </div>
                <a href="{{ route('leaves.index') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">All →</a>
            </div>

            <div class="space-y-2.5">
                @forelse($pendingLeaves as $leave)
                <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-200/60 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold text-slate-900">{{ $leave->employee->name }}</div>
                        <span class="px-1.5 py-0.5 text-[10px] font-medium bg-amber-50 text-amber-800 border border-amber-200 rounded">{{ $leave->leaveType->name }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500">
                        {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }} ({{ $leave->total_days }}d)
                    </div>
                    
                    <div class="pt-1 flex gap-2">
                        <form method="POST" action="{{ route('leaves.updateStatus', $leave) }}" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="w-full py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                Approve
                            </button>
                        </form>

                        <form method="POST" action="{{ route('leaves.updateStatus', $leave) }}" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="w-full py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                Reject
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-xs text-slate-400">
                    No pending leave requests.
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Recent Daily Work Activity Stream -->
    <div class="p-5 ui-panel">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-sm text-slate-900">Live Work Stream</h3>
                <p class="text-xs text-slate-500 font-normal">Real-time team deliverables & submissions</p>
            </div>
            <a href="{{ route('work-entries.index') }}" class="text-xs font-semibold text-[#0071e3] hover:underline">View All →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] bg-slate-50/50">
                        <th class="py-2.5 px-3 font-semibold">Date</th>
                        <th class="py-2.5 px-3 font-semibold">Employee</th>
                        <th class="py-2.5 px-3 font-semibold">Department</th>
                        <th class="py-2.5 px-3 font-semibold">Category</th>
                        <th class="py-2.5 px-3 font-semibold text-center">Quantity</th>
                        <th class="py-2.5 px-3 font-semibold">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentWorkEntries as $entry)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">{{ $entry->date->format('d M, Y') }}</td>
                        <td class="py-2.5 px-3">
                            <div class="flex items-center gap-2">
                                <img src="{{ $entry->employee->photo_url }}" class="w-6 h-6 rounded-full object-cover border border-slate-200">
                                <span class="font-medium text-slate-900">{{ $entry->employee->name }}</span>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-slate-500">{{ $entry->employee->department->name ?? 'N/A' }}</td>
                        <td class="py-2.5 px-3">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium" style="background-color: {{ $entry->category->color }}15; color: {{ $entry->category->color }};">
                                <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $entry->category->color }}"></span>
                                {{ $entry->category->name }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-center font-bold text-slate-900">{{ $entry->quantity }}</td>
                        <td class="py-2.5 px-3 text-slate-500 max-w-xs truncate">{{ $entry->remarks ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400">No work entries logged yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const textColor = '#64748b';
        const gridColor = '#f1f5f9';

        // Trend Area Chart
        const trendDates = @json($dates);
        const trendWorks = @json($workTrend);

        const trendOptions = {
            series: [{
                name: 'Deliverables',
                data: trendWorks
            }],
            chart: {
                type: 'area',
                height: 240,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#0071e3'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.03,
                    stops: [0, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2.5 },
            xaxis: {
                categories: trendDates,
                labels: { style: { colors: textColor, fontSize: '11px', fontWeight: 500 } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: textColor, fontSize: '11px', fontWeight: 500 } }
            },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 0
            },
            tooltip: { theme: 'light' }
        };

        const trendChart = new ApexCharts(document.querySelector("#workTrendChart"), trendOptions);
        trendChart.render();

        // Category Donut Chart
        const catLabels = @json($categoryBreakdown->pluck('category.name'));
        const catSeries = @json($categoryBreakdown->pluck('total_qty'));
        const catColors = @json($categoryBreakdown->pluck('category.color'));

        if (catSeries.length > 0) {
            const catOptions = {
                series: catSeries,
                labels: catLabels,
                colors: catColors,
                chart: {
                    type: 'donut',
                    height: 210,
                    fontFamily: 'Inter, sans-serif'
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    color: textColor,
                                    formatter: () => '{{ $monthlyWorkCount }}'
                                }
                            }
                        }
                    }
                },
                tooltip: { theme: 'light' }
            };

            const catChart = new ApexCharts(document.querySelector("#categoryChart"), catOptions);
            catChart.render();
        }
    });
</script>
@endpush
@endsection
