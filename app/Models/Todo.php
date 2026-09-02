<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assigned_to_user_id',
        'title',
        'description',
        'priority',
        'status',
        'subtasks',
        'reference_url',
        'category',
        'due_date',
        'due_time',
        'is_completed',
        'completed_at',
        'work_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
            'subtasks' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function workEntry(): BelongsTo
    {
        return $this->belongsTo(DailyWorkEntry::class, 'work_entry_id');
    }

    public function isOverdue(): bool
    {
        if ($this->is_completed || !$this->due_date) {
            return false;
        }
        return $this->due_date->isPast() && !$this->due_date->isToday();
    }

    public function completedSubtasksCount(): int
    {
        if (empty($this->subtasks) || !is_array($this->subtasks)) {
            return 0;
        }
        return count(array_filter($this->subtasks, fn($st) => !empty($st['completed'])));
    }

    public function totalSubtasksCount(): int
    {
        if (empty($this->subtasks) || !is_array($this->subtasks)) {
            return 0;
        }
        return count($this->subtasks);
    }
}
