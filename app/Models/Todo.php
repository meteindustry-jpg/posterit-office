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

    protected $appends = [
        'reference_preview',
        'is_reference_image',
    ];

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

    public function isReferenceImage(): bool
    {
        if (!$this->reference_url) {
            return false;
        }

        $url = trim($this->reference_url);

        // Local stored file
        if (str_starts_with($url, 'todos/') || str_starts_with($url, '/storage/todos/')) {
            return true;
        }

        // Standard image file extensions
        $cleanUrl = strtok($url, '?#');
        $ext = strtolower(pathinfo($cleanUrl, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif'])) {
            return true;
        }

        // Google Drive image share link
        if (preg_match('/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)/', $url)) {
            return true;
        }

        // Common image CDNs / photo sharing sites
        if (preg_match('/(imgur\.com|unsplash\.com|cloudinary\.com|imagekit\.io|postimg\.cc|prnt\.sc)/i', $url)) {
            return true;
        }

        return false;
    }

    public function referenceImagePreviewUrl(): ?string
    {
        if (!$this->reference_url) {
            return null;
        }

        $url = trim($this->reference_url);

        // Local stored file
        if (str_starts_with($url, 'todos/')) {
            return asset('storage/' . $url);
        }
        if (str_starts_with($url, '/storage/')) {
            return asset(ltrim($url, '/'));
        }

        // Google Drive file ID conversion to thumbnail
        if (preg_match('/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)/', $url, $m)) {
            return "https://drive.google.com/thumbnail?id={$m[1]}&sz=w1000";
        }

        // Dropbox link conversion to direct raw image
        if (str_contains($url, 'dropbox.com')) {
            return str_replace(['?dl=0', '&dl=0'], '', $url) . (str_contains($url, '?') ? '&raw=1' : '?raw=1');
        }

        return $url;
    }

    public function getReferencePreviewAttribute(): ?string
    {
        return $this->referenceImagePreviewUrl();
    }

    public function getIsReferenceImageAttribute(): bool
    {
        return $this->isReferenceImage();
    }
}
