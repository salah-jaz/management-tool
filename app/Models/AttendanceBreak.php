<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AttendanceBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'workspace_id',
        'user_id',
        'break_type',
        'break_start_time',
        'break_end_time',
        'break_duration',
        'break_reason',
        'break_status',
        'is_approved',
        'approved_by',
        'approved_at',
        'approval_notes'
    ];

    protected $casts = [
        'break_start_time' => 'string',
        'break_end_time' => 'string',
        'break_duration' => 'string', // time format HH:MM:SS
        'is_approved' => 'boolean',
        'approved_at' => 'datetime'
    ];

    // Relationships
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('break_status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('break_status', 'completed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('break_type', $type);
    }

    // Accessors & Mutators
    public function getBreakStartTimeFormattedAttribute()
    {
        return $this->break_start_time ? Carbon::parse($this->break_start_time)->format('H:i') : null;
    }

    public function getBreakEndTimeFormattedAttribute()
    {
        return $this->break_end_time ? Carbon::parse($this->break_end_time)->format('H:i') : null;
    }

    public function getBreakDurationFormattedAttribute()
    {
        if (!$this->break_duration) {
            return '00:00';
        }
        
        // Handle negative or invalid time values
        $duration = (string) $this->break_duration;
        if (strpos($duration, '-') === 0 || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $duration)) {
            return '00:00';
        }
        
        // Parse time string (HH:MM:SS) and format as HH:MM
        $time = Carbon::createFromFormat('H:i:s', $duration);
        return $time->format('H:i');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger'
        ];

        return $badges[$this->break_status] ?? 'secondary';
    }

    public function getTypeBadgeAttribute()
    {
        $badges = [
            'lunch' => 'primary',
            'coffee' => 'info',
            'personal' => 'warning',
            'meeting' => 'success',
            'other' => 'secondary'
        ];

        return $badges[$this->break_type] ?? 'secondary';
    }

    // Methods
    public function calculateDuration()
    {
        if (!$this->break_start_time || !$this->break_end_time) {
            return '00:00:00';
        }

        $startTime = Carbon::parse($this->break_start_time);
        $endTime = Carbon::parse($this->break_end_time);
        
        // If end time is earlier than start time, it's the next day
        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }
        
        $minutes = (int) round($startTime->diffInMinutes($endTime));
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        
        return sprintf('%02d:%02d:00', $hours, $mins);
    }

    public function endBreak()
    {
        $this->break_end_time = now(config('app.timezone'))->format('H:i:s');
        $this->break_status = 'completed';
        $this->break_duration = $this->calculateDuration();
        $this->save();
    }

    public function cancelBreak()
    {
        $this->break_status = 'cancelled';
        $this->save();
    }

    public function isActive()
    {
        return $this->break_status === 'active';
    }

    public function isCompleted()
    {
        return $this->break_status === 'completed';
    }

    public function isCancelled()
    {
        return $this->break_status === 'cancelled';
    }
}
