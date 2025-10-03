<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        'check_in_time' => 'string',
        'check_out_time' => 'string',
        'total_work_hours' => 'string',
        'total_break_hours' => 'string',
        'net_work_hours' => 'string',
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
		// Prefer stored total if present and non-zero, otherwise compute from check-in/out minus completed breaks
		if ($this->total_work_hours) {
			$stored = $this->total_work_hours instanceof Carbon
				? $this->total_work_hours->copy()
				: Carbon::parse($this->total_work_hours);
			$storedMinutes = ((int) $stored->format('H')) * 60 + (int) $stored->format('i');
			if ($storedMinutes > 0) {
				return $stored->format('H:i');
			}
		}

		if (!$this->check_in_time || !$this->check_out_time) {
			return '00:00';
		}

		$attendanceDate = $this->attendance_date instanceof Carbon
			? $this->attendance_date->format('Y-m-d')
			: (string) $this->attendance_date;

		$inTime = $this->check_in_time instanceof Carbon
			? $this->check_in_time->format('H:i:s')
			: (string) $this->check_in_time;

		$outTime = $this->check_out_time instanceof Carbon
			? $this->check_out_time->format('H:i:s')
			: (string) $this->check_out_time;

		$in = Carbon::createFromFormat('Y-m-d H:i:s', $attendanceDate.' '.$inTime);
		$out = Carbon::createFromFormat('Y-m-d H:i:s', $attendanceDate.' '.$outTime);
		if ($out->lt($in)) {
			$out->addDay(); // handle overnight shifts
		}

		$totalMinutes = max(0, $in->diffInMinutes($out));

		// Sum break durations (supports TIME columns)
		if ($this->relationLoaded('breaks')) {
			$breakSeconds = $this->breaks
				->where('break_status', 'completed')
				->sum(function ($break) {
					$duration = (string) ($break->break_duration ?? '00:00:00');
					return max(0, strtotime($duration) - strtotime('00:00:00'));
				});
		} else {
			$breakSeconds = (int) $this->breaks()
				->where('break_status', 'completed')
				->sum(DB::raw('TIME_TO_SEC(break_duration)'));
		}
		$breakMinutes = (int) round($breakSeconds / 60);
		$netMinutes = max(0, $totalMinutes - $breakMinutes);

		$hours = intdiv($netMinutes, 60);
		$minutes = $netMinutes % 60;
		return sprintf('%02d:%02d', $hours, $minutes);
    }

	public function getComputedWorkHoursFormattedAttribute()
	{
		if (!$this->check_in_time || !$this->check_out_time) {
			return '00:00';
		}

		$attendanceDate = $this->attendance_date instanceof Carbon
			? $this->attendance_date->format('Y-m-d')
			: (string) $this->attendance_date;

		$inStr = $this->check_in_time instanceof Carbon
			? $this->check_in_time->format('H:i:s')
			: (string) $this->check_in_time;

		$outStr = $this->check_out_time instanceof Carbon
			? $this->check_out_time->format('H:i:s')
			: (string) $this->check_out_time;

		$in = Carbon::createFromFormat('Y-m-d H:i:s', $attendanceDate.' '.$inStr);
		$out = Carbon::createFromFormat('Y-m-d H:i:s', $attendanceDate.' '.$outStr);
		if ($out->lt($in)) {
			$out->addDay();
		}

		$totalMinutes = max(0, $in->diffInMinutes($out));

		if ($this->relationLoaded('breaks')) {
			$breakSeconds = $this->breaks
				->where('break_status', 'completed')
				->sum(function ($break) {
					$duration = (string) ($break->break_duration ?? '00:00:00');
					return max(0, strtotime($duration) - strtotime('00:00:00'));
				});
		} else {
			$breakSeconds = (int) $this->breaks()
				->where('break_status', 'completed')
				->sum(DB::raw('TIME_TO_SEC(break_duration)'));
		}

		$netMinutes = max(0, $totalMinutes - (int) round($breakSeconds / 60));
		$hours = intdiv($netMinutes, 60);
		$minutes = $netMinutes % 60;
		return sprintf('%02d:%02d', $hours, $minutes);
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
		if (!$this->check_in_time) {
			return null;
		}

		$targetTimezone = config('app.timezone');
		$timeValue = $this->check_in_time;

		if ($timeValue instanceof Carbon) {
			$dt = $timeValue->copy()->timezone('UTC')->setTimezone($targetTimezone);
		} else {
			$timeString = (string) $timeValue;
			if (strpos($timeString, ' ') !== false) {
				$dt = Carbon::createFromFormat('Y-m-d H:i:s', $timeString, 'UTC')->setTimezone($targetTimezone);
			} elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $timeString)) {
				$dt = Carbon::createFromFormat('H:i:s', $timeString, 'UTC')->setTimezone($targetTimezone);
			} else {
				$dt = Carbon::parse($timeString, 'UTC')->setTimezone($targetTimezone);
			}
		}

		return $dt->format('H:i');
    }

    public function getCheckOutTimeFormattedAttribute()
    {
		if (!$this->check_out_time) {
			return null;
		}

		$targetTimezone = config('app.timezone');
		$timeValue = $this->check_out_time;

		if ($timeValue instanceof Carbon) {
			$dt = $timeValue->copy()->timezone('UTC')->setTimezone($targetTimezone);
		} else {
			$timeString = (string) $timeValue;
			if (strpos($timeString, ' ') !== false) {
				$dt = Carbon::createFromFormat('Y-m-d H:i:s', $timeString, 'UTC')->setTimezone($targetTimezone);
			} elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $timeString)) {
				$dt = Carbon::createFromFormat('H:i:s', $timeString, 'UTC')->setTimezone($targetTimezone);
			} else {
				$dt = Carbon::parse($timeString, 'UTC')->setTimezone($targetTimezone);
			}
		}

		return $dt->format('H:i');
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
