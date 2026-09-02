@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="holidayCalendarApp()">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold font-display text-slate-900 tracking-tight">
                Holiday Calendar
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Official company & national holiday schedule for Posterit.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if(auth()->user()->isAdmin())
            <button type="button" @click="addModalOpen = true" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#0071e3] hover:bg-[#0062c4] text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer active:scale-[0.98]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Holiday</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Calendar Controls & Navigation Bar -->
    <div class="p-2.5 bg-white rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        
        <!-- Left: Month Switcher & Today Button -->
        <div class="flex items-center gap-2">
            <div class="inline-flex items-center p-0.5 bg-slate-100 rounded-xl border border-slate-200/80">
                <button type="button" @click="prevMonth()" title="Previous Month" 
                        class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-white transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" @click="nextMonth()" title="Next Month" 
                        class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-white transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            <button type="button" @click="goToToday()" 
                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200/70 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                Today
            </button>

            <!-- Active Month Display -->
            <div class="px-2">
                <span class="text-base font-black text-slate-900 tracking-tight" x-text="currentMonthName + ' ' + currentYear"></span>
            </div>
        </div>

        <!-- Right: View Mode Toggle -->
        <div class="flex items-center gap-2">
            <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/80">
                <button type="button" @click="viewMode = 'month'" 
                        :class="viewMode === 'month' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-900'"
                        class="px-3 py-1 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Month Grid</span>
                </button>

                <button type="button" @click="viewMode = 'list'" 
                        :class="viewMode === 'list' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-900'"
                        class="px-3 py-1 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Annual List</span>
                </button>
            </div>
        </div>

    </div>

    <!-- VIEW 1: Interactive Monthly Calendar Grid (Days 1–31) -->
    <div x-show="viewMode === 'month'" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden">
        
        <!-- 7-Day Column Headers (Mon -> Sun) -->
        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50/75 text-center text-[11px] font-extrabold uppercase tracking-wider text-slate-500 py-3">
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div class="text-rose-500">Sat</div>
            <div class="text-rose-500">Sun</div>
        </div>

        <!-- Days Grid (35 or 42 cells) -->
        <div class="grid grid-cols-7 divide-x divide-y divide-slate-100 border-b border-slate-100">
            <template x-for="(cell, index) in calendarDays" :key="cell.dateStr + index">
                <div class="min-h-[115px] p-2 flex flex-col justify-between transition-colors relative"
                     :class="{
                         'bg-slate-50/40 text-slate-300': !cell.isCurrentMonth,
                         'bg-white text-slate-800 hover:bg-slate-50/60': cell.isCurrentMonth && !cell.isToday,
                         'bg-blue-50/30 ring-2 ring-[#0071e3] ring-inset z-10': cell.isToday
                     }">
                    
                    <!-- Top Day Number & Today Indicator -->
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center justify-center text-xs font-bold"
                              :class="{
                                  'w-6 h-6 rounded-full bg-[#0071e3] text-white shadow-xs font-black': cell.isToday,
                                  'text-slate-900 font-bold': cell.isCurrentMonth && !cell.isToday,
                                  'text-slate-300': !cell.isCurrentMonth
                              }"
                              x-text="cell.dayNum">
                        </span>

                        <span x-show="cell.isToday" class="text-[9px] font-extrabold text-[#0071e3] uppercase tracking-wider">
                            Today
                        </span>
                    </div>

                    <!-- Holiday Event Pills inside Day Cell -->
                    <div class="mt-1.5 space-y-1">
                        <template x-for="h in cell.holidays" :key="h.id">
                            <button type="button" 
                                    @click="openDetail(h)"
                                    :class="getHolidayTheme(h.name, h.type).bg"
                                    class="w-full text-left px-2 py-1 rounded-lg text-[11px] font-bold truncate flex items-center gap-1.5 transition-transform hover:scale-[1.03] cursor-pointer">
                                <span class="text-xs shrink-0" x-text="getHolidayTheme(h.name, h.type).icon"></span>
                                <span class="truncate" x-text="h.name"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Empty Space Filler -->
                    <div class="flex-1"></div>

                </div>
            </template>
        </div>

        <!-- Legend Footer -->
        <div class="p-3.5 bg-slate-50/60 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-3">
            <div class="flex items-center gap-4">
                <span class="font-bold text-slate-700">Legend:</span>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-blue-600"></span>
                    <span>National</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-pink-500"></span>
                    <span>Religious / Festive</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-teal-600"></span>
                    <span>Company Off</span>
                </div>
            </div>

            <div>
                Click on any colored holiday event pill to view details.
            </div>
        </div>

    </div>

    <!-- VIEW 2: Annual List View with Luminous Gradient Cards -->
    <div x-show="viewMode === 'list'" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($holidays as $index => $h)
            @php
                $isPast = $h->date->isPast() && !$h->date->isToday();
                $isToday = $h->date->isToday();
                $isUpcoming = $h->date->isFuture();
                $nameLower = strtolower($h->name);

                // 6 Curated Luminous Gradients & Shadows from Reference
                $styles = [
                    ['from-[#6049f5] via-[#8659f7] to-[#887bf9]', 'shadow-[0_12px_24px_-6px_rgba(134,89,247,0.35)]', '<path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 002 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10z"/>'],
                    ['from-[#f87817] via-[#fa9519] to-[#fdb51b]', 'shadow-[0_12px_24px_-6px_rgba(250,149,25,0.35)]', '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/>'],
                    ['from-[#f21b7f] via-[#f73898] to-[#f95cb0]', 'shadow-[0_12px_24px_-6px_rgba(247,56,152,0.35)]', '<path d="M12 3c-4.97 0-9 4.03-9 9 0 2.12.74 4.07 1.97 5.61L4.35 19.4c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0l1.9-1.9C9.22 19.64 10.56 20 12 20c4.97 0 9-4.03 9-9s-4.03-9-9-9zm0 15c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/>'],
                    ['from-[#0089f8] via-[#05abfb] to-[#36cafd]', 'shadow-[0_12px_24px_-6px_rgba(5,171,251,0.35)]', '<path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>'],
                    ['from-[#f7941d] via-[#faa822] to-[#fdc329]', 'shadow-[0_12px_24px_-6px_rgba(250,168,34,0.35)]', '<path d="M12 2c1.1 0 2 .9 2 2v6.2c3.4 1 6 4.2 6 7.8 0 4.4-3.6 8-8 8s-8-3.6-8-8c0-3.6 2.6-6.8 6-7.8V4c0-1.1.9-2 2-2zm0 12c-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4z"/>'],
                    ['from-[#00c068] via-[#20d47b] to-[#4ce393]', 'shadow-[0_12px_24px_-6px_rgba(32,212,123,0.35)]', '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>'],
                ];

                $style = $styles[$index % count($styles)];
                $gradient = $style[0];
                $shadow = $style[1];
                $svgIcon = $style[2];
            @endphp

            <!-- Clean Luminous Gradient Card -->
            <div class="relative overflow-hidden p-6 rounded-3xl bg-gradient-to-r {{ $gradient }} text-white {{ $shadow }} hover:scale-[1.02] hover:shadow-xl transition-all duration-300 flex flex-col justify-between group min-h-[150px]">
                
                <!-- Large Watermark Vector on Right -->
                <svg class="absolute -right-2 -bottom-2 w-32 h-32 text-white/20 pointer-events-none transition-transform group-hover:scale-110 duration-300" fill="currentColor" viewBox="0 0 24 24">
                    {!! $svgIcon !!}
                </svg>

                <!-- Card Content Left -->
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <h3 class="text-xl font-extrabold text-white tracking-tight leading-snug">
                            {{ $h->name }}
                        </h3>
                        <!-- Minimalist Dash from Reference -->
                        <div class="w-7 h-0.75 bg-white/40 rounded-full mt-2 mb-3"></div>
                    </div>

                    @if($isToday)
                        <span class="px-3 py-1 rounded-full text-[10px] font-black bg-white text-emerald-600 shadow-sm uppercase tracking-wider animate-pulse shrink-0">
                            Today
                        </span>
                    @elseif(auth()->user()->isAdmin())
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="openEdit(@js($h))" 
                                    title="Edit Holiday"
                                    class="p-1.5 rounded-lg bg-white/20 hover:bg-white/40 text-white cursor-pointer transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <form method="POST" action="{{ route('holidays.destroy', $h) }}" onsubmit="return confirm('Delete holiday {{ $h->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        title="Delete Holiday"
                                        class="p-1.5 rounded-lg bg-white/20 hover:bg-white/40 text-white cursor-pointer transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Card Bottom Meta -->
                <div class="relative z-10 flex items-baseline justify-between mt-auto">
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-white leading-none tracking-tight">{{ $h->date->format('d M') }}</span>
                        <span class="text-xs font-semibold text-white/90">{{ $h->date->format('l') }}</span>
                    </div>
                    <span class="text-xs font-semibold text-white/80 capitalize">{{ $h->type }}</span>
                </div>

            </div>
            @empty
            <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-slate-200/80 shadow-2xs">
                <div class="text-3xl mb-2">📅</div>
                <h4 class="font-bold text-slate-800 text-sm">No holidays registered for {{ $year }}</h4>
                <p class="text-xs text-slate-400 mt-1">Use the "+ Add Holiday" button to create one.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Holiday Details Modal -->
    <div x-show="detailModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs" style="display: none;">
        <div @click.outside="detailModalOpen = false" class="w-full max-w-sm bg-white rounded-3xl p-6 shadow-2xl border border-slate-200 space-y-4">
            <template x-if="selectedHoliday">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl" x-text="getHolidayTheme(selectedHoliday.name, selectedHoliday.type).icon"></span>
                            <div>
                                <h3 class="font-extrabold text-base text-slate-900" x-text="selectedHoliday.name"></h3>
                                <span class="text-xs font-bold text-slate-500 capitalize" x-text="selectedHoliday.type + ' Holiday'"></span>
                            </div>
                        </div>
                        <button type="button" @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-600 text-sm">✕</button>
                    </div>

                    <div class="mt-4 p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-bold uppercase text-[10px]">Date</span>
                            <span class="font-bold text-slate-800" x-text="selectedHoliday.date"></span>
                        </div>
                        <div x-show="selectedHoliday.description" class="pt-2 border-t border-slate-200/60">
                            <span class="text-slate-400 font-bold uppercase text-[10px] block mb-1">Details</span>
                            <p class="text-slate-600 italic" x-text="selectedHoliday.description"></p>
                        </div>
                    </div>

                    @if(auth()->user()->isAdmin())
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="openEdit(selectedHoliday)" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition">
                            Edit Holiday
                        </button>
                    </div>
                    @endif
                </div>
            </template>
        </div>
    </div>

    <!-- Add Modal -->
    <div x-show="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" style="display: none;">
        <div @click.outside="addModalOpen = false" class="w-full max-w-md bg-white rounded-3xl p-6 shadow-2xl border border-slate-200 space-y-4">
            <h3 class="font-extrabold text-base text-slate-900 font-display">Add New Holiday</h3>
            
            <form method="POST" action="{{ route('holidays.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Holiday Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Diwali Festival" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:bg-white focus:ring-2 focus:ring-[#0071e3]">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Holiday Date *</label>
                    <input type="date" name="date" required value="{{ now()->format('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:bg-white focus:ring-2 focus:ring-[#0071e3]">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Holiday Type *</label>
                    <select name="type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold focus:bg-white focus:ring-2 focus:ring-[#0071e3]">
                        <option value="national">National Holiday</option>
                        <option value="religious">Religious Holiday</option>
                        <option value="company" selected>Company Holiday</option>
                        <option value="optional">Optional Holiday</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Description (Optional)</label>
                    <textarea name="description" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#0071e3]"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#0071e3] hover:bg-[#0062c4] text-white font-bold rounded-xl shadow-xs cursor-pointer">Add Holiday</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" style="display: none;">
        <div @click.outside="editModalOpen = false" class="w-full max-w-md bg-white rounded-3xl p-6 shadow-2xl border border-slate-200 space-y-4">
            <h3 class="font-extrabold text-base text-slate-900 font-display">Edit Holiday</h3>
            
            <form :action="editData.url" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Holiday Name *</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:bg-white focus:ring-2 focus:ring-[#0071e3]">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Holiday Date *</label>
                    <input type="date" name="date" x-model="editData.date" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:bg-white focus:ring-2 focus:ring-[#0071e3]">
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Holiday Type *</label>
                    <select name="type" x-model="editData.type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold focus:bg-white focus:ring-2 focus:ring-[#0071e3]">
                        <option value="national">National Holiday</option>
                        <option value="religious">Religious Holiday</option>
                        <option value="company">Company Holiday</option>
                        <option value="optional">Optional Holiday</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-500 uppercase mb-1">Description (Optional)</label>
                    <textarea name="description" x-model="editData.description" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#0071e3]"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#0071e3] hover:bg-[#0062c4] text-white font-bold rounded-xl shadow-xs cursor-pointer">Update Holiday</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function holidayCalendarApp() {
    return {
        viewMode: 'month',
        currentYear: {{ $year }},
        currentMonth: {{ now()->month - 1 }},
        holidays: @json($holidaysJson),
        addModalOpen: false,
        editModalOpen: false,
        detailModalOpen: false,
        selectedHoliday: null,
        monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        
        editData: { id: null, name: '', date: '', type: 'company', description: '', url: '' },

        get currentMonthName() {
            return this.monthNames[this.currentMonth];
        },

        prevMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
        },

        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
        },

        goToToday() {
            const today = new Date();
            this.currentYear = today.getFullYear();
            this.currentMonth = today.getMonth();
        },

        get calendarDays() {
            const days = [];
            const firstDay = new Date(this.currentYear, this.currentMonth, 1);
            const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
            
            let startDayIndex = firstDay.getDay() - 1;
            if (startDayIndex === -1) startDayIndex = 6;
            
            const prevMonthLastDay = new Date(this.currentYear, this.currentMonth, 0).getDate();
            const totalDays = lastDay.getDate();
            
            // Prev month padding
            for (let i = startDayIndex - 1; i >= 0; i--) {
                const dayNum = prevMonthLastDay - i;
                const m = this.currentMonth === 0 ? 12 : this.currentMonth;
                const y = this.currentMonth === 0 ? this.currentYear - 1 : this.currentYear;
                const dateStr = `${y}-${String(m).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
                days.push({
                    dayNum: dayNum,
                    dateStr: dateStr,
                    isCurrentMonth: false,
                    isToday: false,
                    holidays: this.getHolidaysForDate(dateStr)
                });
            }
            
            const todayStr = new Date().toISOString().split('T')[0];
            
            // Current month
            for (let i = 1; i <= totalDays; i++) {
                const m = this.currentMonth + 1;
                const dateStr = `${this.currentYear}-${String(m).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                days.push({
                    dayNum: i,
                    dateStr: dateStr,
                    isCurrentMonth: true,
                    isToday: (dateStr === todayStr),
                    holidays: this.getHolidaysForDate(dateStr)
                });
            }
            
            // Next month padding
            const remaining = (days.length <= 35 ? 35 : 42) - days.length;
            for (let i = 1; i <= remaining; i++) {
                const m = this.currentMonth === 11 ? 1 : this.currentMonth + 2;
                const y = this.currentMonth === 11 ? this.currentYear + 1 : this.currentYear;
                const dateStr = `${y}-${String(m).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                days.push({
                    dayNum: i,
                    dateStr: dateStr,
                    isCurrentMonth: false,
                    isToday: false,
                    holidays: this.getHolidaysForDate(dateStr)
                });
            }
            
            return days;
        },

        getHolidaysForDate(dateStr) {
            return this.holidays.filter(h => h.date === dateStr);
        },

        getHolidayTheme(name, type) {
            const n = (name || '').toLowerCase();
            if (n.includes('diwali') || n.includes('laxmi') || n.includes('deepavali')) {
                return { bg: 'bg-amber-500 text-white shadow-xs', icon: '🪔' };
            }
            if (n.includes('holi')) {
                return { bg: 'bg-pink-500 text-white shadow-xs', icon: '🎨' };
            }
            if (n.includes('independence') || n.includes('republic')) {
                return { bg: 'bg-blue-600 text-white shadow-xs', icon: '🇮🇳' };
            }
            if (n.includes('christmas')) {
                return { bg: 'bg-emerald-600 text-white shadow-xs', icon: '🎄' };
            }
            if (n.includes('new year')) {
                return { bg: 'bg-purple-600 text-white shadow-xs', icon: '🎉' };
            }
            if (n.includes('ganesh')) {
                return { bg: 'bg-orange-500 text-white shadow-xs', icon: '🌺' };
            }
            if (n.includes('gandhi') || n.includes('labor') || n.includes('maharashtra')) {
                return { bg: 'bg-slate-700 text-white shadow-xs', icon: '🚩' };
            }
            return type === 'national' 
                ? { bg: 'bg-[#0071e3] text-white shadow-xs', icon: '🏛️' }
                : (type === 'religious' ? { bg: 'bg-rose-500 text-white shadow-xs', icon: '✨' } : { bg: 'bg-teal-600 text-white shadow-xs', icon: '🏢' });
        },

        openDetail(h) {
            this.selectedHoliday = h;
            this.detailModalOpen = true;
        },

        openEdit(h) {
            this.detailModalOpen = false;
            this.editData = {
                id: h.id,
                name: h.name,
                date: h.date.split('T')[0],
                type: h.type,
                description: h.description || '',
                url: `{{ url('holidays') }}/${h.id}`
            };
            this.editModalOpen = true;
        }
    };
}
</script>
@endsection
