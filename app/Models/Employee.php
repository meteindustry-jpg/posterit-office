<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_code',
        'user_id',
        'name',
        'photo',
        'mobile_number',
        'email',
        'designation',
        'department_id',
        'joining_date',
        'employment_status',
        'salary',
        'notes',
        'leave_quota',
        'emergency_contact_name',
        'emergency_contact_phone',
        'bank_name',
        'bank_account_no',
        'bank_ifsc',
        'upi_id',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'salary' => 'decimal:2',
            'leave_quota' => 'integer',
        ];
    }

    public static function generateUniqueCode(): string
    {
        $maxId = (int) (static::max('id') ?? 0);
        $next = $maxId + 1;
        $code = 'EMP-' . str_pad($next, 3, '0', STR_PAD_LEFT);
        while (static::where('employee_code', $code)->exists()) {
            $next++;
            $code = 'EMP-' . str_pad($next, 3, '0', STR_PAD_LEFT);
        }
        return $code;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances()
    {
        return $this->hasMany(DailyAttendance::class);
    }

    public function workEntries()
    {
        return $this->hasMany(DailyWorkEntry::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function getUsedLeavesAttribute(): float
    {
        return (float) $this->leaveRequests()
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('total_days');
    }

    public function getRemainingLeavesAttribute(): float
    {
        return max(0, $this->leave_quota - $this->used_leaves);
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && file_exists(public_path('storage/' . $this->photo))) {
            return asset('storage/' . $this->photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }
}
