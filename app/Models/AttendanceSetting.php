<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'work_start_time',
        'work_end_time',
        'break_start_time',
        'break_end_time',
        'break_duration_minutes',
        'max_break_duration_minutes',
        'late_tolerance_minutes',
        'early_departure_tolerance_minutes',
        'require_location',
        'require_photo',
        'auto_checkout',
        'auto_checkout_hours',
        'weekend_tracking',
        'holiday_tracking',
        'working_days',
        'holidays',
        'overtime_threshold_hours',
        'overtime_approval_required',
        'break_approval_required',
        'attendance_approval_required',
        'location_radius_meters',
        'timezone',
        'is_active'
    ];

    protected $casts = [
        'work_start_time' => 'datetime:H:i:s',
        'work_end_time' => 'datetime:H:i:s',
        'break_start_time' => 'datetime:H:i:s',
        'break_end_time' => 'datetime:H:i:s',
        'break_duration_minutes' => 'integer',
        'max_break_duration_minutes' => 'integer',
        'late_tolerance_minutes' => 'integer',
        'early_departure_tolerance_minutes' => 'integer',
        'require_location' => 'boolean',
        'require_photo' => 'boolean',
        'auto_checkout' => 'boolean',
        'auto_checkout_hours' => 'integer',
        'weekend_tracking' => 'boolean',
        'holiday_tracking' => 'boolean',
        'working_days' => 'array',
        'holidays' => 'array',
        'overtime_threshold_hours' => 'decimal:2',
        'overtime_approval_required' => 'boolean',
        'break_approval_required' => 'boolean',
        'attendance_approval_required' => 'boolean',
        'location_radius_meters' => 'integer',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    // Methods
    public function getWorkingDaysArray()
    {
        return $this->working_days ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    }

    public function getHolidaysArray()
    {
        return $this->holidays ?? [];
    }

    public function isWorkingDay($dayName)
    {
        $workingDays = $this->getWorkingDaysArray();
        return in_array(strtolower($dayName), $workingDays);
    }

    public function isHoliday($date)
    {
        $holidays = $this->getHolidaysArray();
        $dateString = is_string($date) ? $date : $date->format('Y-m-d');
        return in_array($dateString, $holidays);
    }

    public function getWorkStartTimeFormatted()
    {
        return $this->work_start_time ? $this->work_start_time->format('H:i') : '09:00';
    }

    public function getWorkEndTimeFormatted()
    {
        return $this->work_end_time ? $this->work_end_time->format('H:i') : '17:00';
    }

    public function getBreakStartTimeFormatted()
    {
        return $this->break_start_time ? $this->break_start_time->format('H:i') : '12:00';
    }

    public function getBreakEndTimeFormatted()
    {
        return $this->break_end_time ? $this->break_end_time->format('H:i') : '13:00';
    }

    public function getTotalWorkHours()
    {
        if (!$this->work_start_time || !$this->work_end_time) {
            return 8; // Default 8 hours
        }

        $start = $this->work_start_time;
        $end = $this->work_end_time;
        
        return $end->diffInHours($start);
    }

    public function getBreakDurationHours()
    {
        return $this->break_duration_minutes / 60;
    }

    public function getNetWorkHours()
    {
        return $this->getTotalWorkHours() - $this->getBreakDurationHours();
    }

    public static function getDefaultSettings($workspaceId)
    {
        return [
            'workspace_id' => $workspaceId,
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
            'break_duration_minutes' => 60,
            'max_break_duration_minutes' => 120,
            'late_tolerance_minutes' => 15,
            'early_departure_tolerance_minutes' => 15,
            'require_location' => false,
            'require_photo' => false,
            'auto_checkout' => false,
            'auto_checkout_hours' => 8,
            'weekend_tracking' => false,
            'holiday_tracking' => false,
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'holidays' => [],
            'overtime_threshold_hours' => 8.00,
            'overtime_approval_required' => true,
            'break_approval_required' => false,
            'attendance_approval_required' => false,
            'location_radius_meters' => 100,
            'timezone' => 'UTC',
            'is_active' => true
        ];
    }
}