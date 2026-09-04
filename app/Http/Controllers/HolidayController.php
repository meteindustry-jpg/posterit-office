<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Services\AuditService;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->get('year', now()->year);

        $allHolidays = Holiday::orderBy('date', 'asc')->get();
        $holidays = $allHolidays->filter(fn ($h) => (int) $h->date->format('Y') === $year)->values();

        $todayStr = now()->format('Y-m-d');
        $upcomingCount = Holiday::whereDate('date', '>=', $todayStr)->whereYear('date', $year)->count();
        $nextHoliday = Holiday::whereDate('date', '>=', $todayStr)->orderBy('date', 'asc')->first();
        $nationalCount = $holidays->where('type', 'national')->count();
        $companyCount = $holidays->where('type', 'company')->count();

        $holidaysJson = $allHolidays->map(fn ($h) => [
            'id' => $h->id,
            'name' => $h->name,
            'date' => $h->date->format('Y-m-d'),
            'type' => $h->type,
            'description' => $h->description,
        ]);

        return view('holidays.index', compact('holidays', 'year', 'upcomingCount', 'nextHoliday', 'nationalCount', 'companyCount', 'holidaysJson'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'type' => ['required', 'in:national,religious,company,optional'],
            'description' => ['nullable', 'string'],
        ]);

        $holiday = Holiday::create($validated);

        AuditService::log('create', 'Holiday', "Added holiday {$holiday->name} on {$holiday->date->format('Y-m-d')}", null, $holiday->toArray());

        return back()->with('success', 'Holiday added successfully.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'type' => ['required', 'in:national,religious,company,optional'],
            'description' => ['nullable', 'string'],
        ]);

        $old = $holiday->toArray();
        $holiday->update($validated);

        AuditService::log('update', 'Holiday', "Updated holiday {$holiday->name}", $old, $holiday->toArray());

        return back()->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $old = $holiday->toArray();
        $name = $holiday->name;
        $holiday->delete();

        AuditService::log('delete', 'Holiday', "Deleted holiday {$name}", $old);

        return back()->with('success', "Holiday {$name} deleted.");
    }
}
