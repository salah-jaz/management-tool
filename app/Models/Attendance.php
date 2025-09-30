<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'total_work_hours',
        'total_break_hours',
        'net_work_hours',
        'status',
        'check_in_type',
        'check_out_type',
        'overtime_hours',
        'late_minutes',
        'early_departure_minutes',
        'notes',
        'check_in_location',
        'check_out_location',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'is_approved',
        'approved_by',
        'approved_at',
        'approval_notes'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime:H:i:s',
        'check_out_time' => 'datetime:H:i:s',
        'total_work_hours' => 'datetime:H:i:s',
        'total_break_hours' => 'datetime:H:i:s',
        'net_work_hours' => 'datetime:H:i:s',
        'overtime_hours' => 'decimal:2',
        'late_minutes' => 'decimal:2',
        'early_departure_minutes' => 'decimal:2',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime'
    ];

    // Relationships
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

    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('attendance_date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    // Accessors & Mutators
    public function getTotalWorkHoursFormattedAttribute()
    {
        return $this->total_work_hours ? Carbon::parse($this->total_work_hours)->format('H:i') : '00:00';
    }

    public function getTotalBreakHoursFormattedAttribute()
    {
        return $this->total_break_hours ? Carbon::parse($this->total_break_hours)->format('H:i') : '00:00';
    }

    public function getNetWorkHoursFormattedAttribute()
    {
        return $this->net_work_hours ? Carbon::parse($this->net_work_hours)->format('H:i') : '00:00';
    }

    public function getCheckInTimeFormattedAttribute()
    {
        return $this->check_in_time ? Carbon::parse($this->check_in_time)->format('H:i') : null;
    }

    public function getCheckOutTimeFormattedAttribute()
    {
        return $this->check_out_time ? Carbon::parse($this->check_out_time)->format('H:i') : null;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'half_day' => 'info',
            'leave' => 'secondary'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getIsLateAttribute()
    {
        if (!$this->check_in_time) return false;
        
        $settings = AttendanceSetting::where('workspace_id', $this->workspace_id)->first();
        if (!$settings) return false;

        $checkInTime = Carbon::parse($this->check_in_time);
        $expectedStartTime = Carbon::parse($settings->work_start_time);
        $tolerance = $settings->late_tolerance_minutes;

        return $checkInTime->gt($expectedStartTime->addMinutes($tolerance));
    }

    public function getIsEarlyDepartureAttribute()
    {
        if (!$this->check_out_time) return false;
        
        $settings = AttendanceSetting::where('workspace_id', $this->workspace_id)->first();
        if (!$settings) return false;

        $checkOutTime = Carbon::parse($this->check_out_time);
        $expectedEndTime = Carbon::parse($settings->work_end_time);
        $tolerance = $settings->early_departure_tolerance_minutes;

        return $checkOutTime->lt($expectedEndTime->subMinutes($tolerance));
    }

    // Methods
    public function calculateWorkHours()
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $checkOut = Carbon::parse($this->check_out_time);
        
        // Subtract break time
        $totalBreakMinutes = $this->breaks()->where('break_status', 'completed')->sum('break_duration');
        $totalBreakHours = $totalBreakMinutes / 60;
        
        $workHours = $checkOut->diffInHours($checkIn) - $totalBreakHours;
        
        return max(0, $workHours);
    }

    public function calculateOvertime()
    {
        $settings = AttendanceSetting::where('workspace_id', $this->workspace_id)->first();
        if (!$settings) return 0;

        $workHours = $this->calculateWorkHours();
        if (!$workHours) return 0;

        $threshold = $settings->overtime_threshold_hours;
        return max(0, $workHours - $threshold);
    }

    public function isWorkingDay()
    {
        $settings = AttendanceSetting::where('workspace_id', $this->workspace_id)->first();
        if (!$settings) return true;

        $dayName = strtolower($this->attendance_date->format('l'));
        $workingDays = json_decode($settings->working_days, true);
        
        return in_array($dayName, $workingDays);
    }

    public function isHoliday()
    {
        $settings = AttendanceSetting::where('workspace_id', $this->workspace_id)->first();
        if (!$settings || !$settings->holidays) return false;

        $holidays = json_decode($settings->holidays, true);
        $dateString = $this->attendance_date->format('Y-m-d');
        
        return in_array($dateString, $holidays);
    }
}